<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateWorkerDocument;
use App\Models\Project;
use App\Models\Worker;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\TemplateProcessor;
use setasign\Fpdi\Fpdi;

class WorkerDocumentController extends Controller
{
    private const DEFAULT_WORKER_TEMPLATE = 'worker-timesheet.docx';

    private const DRIVER_WORKER_TEMPLATE = 'driver-worker-timesheet.docx';

    private const EQUIPMENT_INSPECTION_TEMPLATE = 'grar-daily-inpect.docx';

    // Convert month name to Arabic
    private const MONTH_NAMES = [
        'January' => 'يناير',
        'February' => 'فبراير',
        'March' => 'مارس',
        'April' => 'أبريل',
        'May' => 'مايو',
        'June' => 'يونيو',
        'July' => 'يوليو',
        'August' => 'أغسطس',
        'September' => 'سبتمبر',
        'October' => 'أكتوبر',
        'November' => 'نوفمبر',
        'December' => 'ديسمبر',
    ];

    private function resolveSelectedMonthStart(Request $request): Carbon
    {
        $selectedMonth = trim((string) $request->query('month', ''));

        if (preg_match('/^\d{4}-\d{2}$/', $selectedMonth) === 1) {
            try {
                return Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
            } catch (\Throwable $e) {
                // Fallback to the current month.
            }
        }

        return now()->startOfMonth();
    }

    public function downloadGeneratedDocument(string $token)
    {
        $cacheKey = $this->generatedDocumentCacheKey($token);
        $document = Cache::get($cacheKey);

        if (! is_array($document)) {
            abort(404, 'Document not found.');
        }

        $filePath = $this->resolveGeneratedDocumentPath($document['path'] ?? null);
        $fileName = $document['name'] ?? 'document';
        $mimeType = $document['mime'] ?? 'application/octet-stream';

        if (! $filePath || ! file_exists($filePath)) {
            \Log::warning('Generated document file is unavailable', [
                'token' => $token,
                'cached_path' => $document['path'] ?? null,
                'resolved_path' => $filePath,
                'file_exists' => $filePath ? file_exists($filePath) : false,
                'is_readable' => $filePath ? is_readable($filePath) : false,
                'private_storage' => storage_path('app/private'),
                'storage' => storage_path('app'),
            ]);

            abort(404, 'Document file not found.');
        }

        Cache::forget($cacheKey);

        return response()->download(
            $filePath,
            $fileName,
            ['Content-Type' => $mimeType]
        )->deleteFileAfterSend(true);
    }

    public function documentStatus(string $token)
    {
        $status = Cache::get('workers.generated-document.status.' . $token);

        if (! is_array($status)) {
            abort(404, 'Document job not found.');
        }

        return response()->json($status);
    }

    public function exportPdf(Request $request, Worker $worker)
    {
        if ($queued = $this->queueDocumentIfRequested($request, __FUNCTION__, $worker)) {
            return $queued;
        }

        $worker->load(['company', 'jobType']);
        $project = Project::latest('id')->with('company')->first();

        $pdf = Pdf::loadView('back.workers.export', [
            'worker' => $worker,
            'project' => $project,
        ])->setPaper('a4', 'portrait')
          ->setOptions([
              'defaultFont' => 'DejaVu Sans',
              'isHtml5ParserEnabled' => true,
              'isRemoteEnabled' => true,
              'isFontSubsettingEnabled' => true,
              'chroot' => public_path(),
          ]);

        $fileName = 'worker-' . $worker->name . '.pdf';
                Storage::makeDirectory('temp');
        $tempPath = Storage::path('temp/' . $fileName);
        file_put_contents($tempPath, $pdf->output());

        return $this->respondWithGeneratedDocument(
            $request,
            $tempPath,
            $fileName,
            'application/pdf'
        );
    }

    public function exportPdfMerged(Request $request)
    {
        if ($queued = $this->queueDocumentIfRequested($request, __FUNCTION__)) {
            return $queued;
        }

        if (! class_exists(Fpdi::class)) {
            abort(500, 'PDF merge requires setasign/fpdi. Install with: composer require setasign/fpdi');
        }

        $ids = collect(explode(',', (string) $request->query('ids')))
            ->filter(fn ($v) => trim($v) !== '')
            ->map(fn ($v) => (int) $v)
            ->values();

        $jobTypeId = $request->filled('job_type_id') ? (int) $request->query('job_type_id') : null;

        $project = Project::latest('id')->with('company')->first();

        $workers = Worker::with(['company', 'jobType'])
            ->where('is_on_company_payroll', 1)
            ->when($jobTypeId, function ($query) use ($jobTypeId) {
                $query->where('job_type_id', $jobTypeId);
            })
            ->when($ids->isNotEmpty(), function ($query) use ($ids) {
                $query->whereIn('id', $ids);
                $query->orderByRaw('FIELD(id,' . $ids->implode(',') . ')');
            }, function ($query) {
                $query->orderBy('id');
            })
            ->get();

        if ($workers->isEmpty()) {
            abort(404, 'No workers to export.');
        }

        $timestamp = now()->format('Ymd_His');
        $tempFolder = 'temp/workers-merged-' . $timestamp;
        Storage::makeDirectory($tempFolder);
        $tempDir = Storage::path($tempFolder);

        $pdfPaths = [];

        foreach ($workers as $worker) {
            $pdf = Pdf::loadView('back.workers.export', [
                'worker' => $worker,
                'project' => $project,
            ])->setPaper('a4', 'portrait')
              ->setOptions([
                  'defaultFont' => 'DejaVu Sans',
                  'isHtml5ParserEnabled' => true,
                  'isRemoteEnabled' => true,
                  'isFontSubsettingEnabled' => true,
                  'chroot' => public_path(),
              ]);

            $path = $tempDir . DIRECTORY_SEPARATOR . 'worker-' . $worker->id . '.pdf';
            file_put_contents($path, $pdf->output());
            $pdfPaths[] = $path;
        }

        $fpdi = new Fpdi();

        foreach ($pdfPaths as $path) {
            $pageCount = $fpdi->setSourceFile($path);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $tplId = $fpdi->importPage($pageNo);
                $size = $fpdi->getTemplateSize($tplId);
                $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
                $fpdi->AddPage($orientation, [$size['width'], $size['height']]);
                $fpdi->useTemplate($tplId);
            }
        }

        $mergedPath = $tempDir . DIRECTORY_SEPARATOR . 'workers-merged.pdf';
        $fpdi->Output($mergedPath, 'F');

        foreach ($pdfPaths as $path) {
            @unlink($path);
        }

        return $this->respondWithGeneratedDocument(
            $request,
            $mergedPath,
            'workers-merged-' . $timestamp . '.pdf',
            'application/pdf'
        );
    }

    private function rtl(string $text): string
    {
        return "\u{200F}" . trim($text) . "\u{200F}";
    }

    private function ltr(string $text): string
    {
        return "\u{200E}" . trim($text) . "\u{200E}";
    }

    public function exportWord(Request $request, Worker $worker)
    {
        if ($queued = $this->queueDocumentIfRequested($request, __FUNCTION__, $worker)) {
            return $queued;
        }

        $worker->load([
            'company',
            'jobType',
            'equipmentAsDriver' => function ($query) {
                $query->latest('id');
            },
        ]);
        $project = Project::latest('id')->with('company')->first();

        $monthStart = $this->resolveSelectedMonthStart($request);
        $monthAr = self::MONTH_NAMES[$monthStart->format('F')] ?? $monthStart->format('F');
        Storage::makeDirectory('temp');
        $shifts = $this->workerDocumentShifts($worker);

        if (count($shifts) === 1) {
            $fileName = $worker->name . '  - سركي - ' . $monthAr . '.docx';
            $fullPath = Storage::path('temp/' . $fileName);
            $this->generateWorkerDocxFromTemplate($worker, $project, $fullPath, $monthStart, $shifts[0]);

            return $this->respondWithGeneratedDocument(
                $request,
                $fullPath,
                $fileName,
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            );
        }

        $zipPath = Storage::path('temp/' . $worker->name . '  - سركي - ' . $monthAr . '.zip');
        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach ($shifts as $shift) {
            $fileName = $worker->name . '  - سركي - ' . $shift . ' - ' . $monthAr . '.docx';
            $fullPath = Storage::path('temp/' . $fileName);
            $this->generateWorkerDocxFromTemplate($worker, $project, $fullPath, $monthStart, $shift);
            $zip->addFile($fullPath, $fileName);
        }

        $zip->close();

        foreach ($shifts as $shift) {
            @unlink(Storage::path('temp/' . $worker->name . '  - سركي - ' . $shift . ' - ' . $monthAr . '.docx'));
        }

        return $this->respondWithGeneratedDocument(
            $request,
            $zipPath,
            $worker->name . '  - سركي - ' . $monthAr . '.zip',
            'application/zip'
        );
    }

    public function exportWordAll(Request $request)
    {
        if ($queued = $this->queueDocumentIfRequested($request, __FUNCTION__)) {
            return $queued;
        }

        $ids = collect(explode(',', (string) $request->query('ids')))
            ->filter(fn ($v) => trim($v) !== '')
            ->map(fn ($v) => (int) $v)
            ->values();

        $jobTypeId = $request->filled('job_type_id') ? (int) $request->query('job_type_id') : null;

        $project = Project::latest('id')->with('company')->first();

        $workers = Worker::with([
            'company',
            'jobType',
            'equipmentAsDriver' => function ($query) {
                $query->latest('id');
            },
        ])
            ->where('is_on_company_payroll', 1)
            ->when($jobTypeId, function ($query) use ($jobTypeId) {
                $query->where('job_type_id', $jobTypeId);
            })
            ->when($ids->isNotEmpty(), function ($query) use ($ids) {
                $query->whereIn('id', $ids);
                $query->orderByRaw('FIELD(id,' . $ids->implode(',') . ')');
            }, function ($query) {
                $query->orderBy('id');
            })
            ->get();

        if ($workers->isEmpty()) {
            abort(404, 'No workers to export.');
        }

        Storage::makeDirectory('temp');
        $tempDir = Storage::path('temp');
        $docxPaths = [];
        $monthStart = $this->resolveSelectedMonthStart($request);

        foreach ($workers as $worker) {
            foreach ($this->workerDocumentShifts($worker) as $shift) {
                $suffix = $shift ? '-' . $shift : '';
                $fileName = 'worker-' . $worker->id . $suffix . '-' . preg_replace('/[^a-zA-Z0-9]/', '', $worker->name) . '.docx';
                $docxPath = $tempDir . DIRECTORY_SEPARATOR . $fileName;
                $this->generateWorkerDocxFromTemplate($worker, $project, $docxPath, $monthStart, $shift);
                $docxPaths[] = $docxPath;
            }
        }

        $zipPath = $tempDir . DIRECTORY_SEPARATOR . 'workers-timesheets.zip';
        $zip = new \ZipArchive();

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Could not create ZIP archive.');
        }

        foreach ($docxPaths as $docxPath) {
            $zip->addFile($docxPath, basename($docxPath));
        }

        $zip->close();

        foreach ($docxPaths as $docxPath) {
            @unlink($docxPath);
        }

        return $this->respondWithGeneratedDocument(
            $request,
            $zipPath,
            'workers-timesheets.zip',
            'application/zip'
        );
    }

    public function exportWordMerged(Request $request)
    {
        if ($queued = $this->queueDocumentIfRequested($request, __FUNCTION__)) {
            return $queued;
        }

        $ids = collect(explode(',', (string) $request->query('ids')))
            ->filter(fn ($v) => trim($v) !== '')
            ->map(fn ($v) => (int) $v)
            ->values();

        $jobTypeId = $request->filled('job_type_id') ? (int) $request->query('job_type_id') : null;

        $project = Project::latest('id')->with('company')->first();

        $workers = Worker::with([
            'company',
            'jobType',
            'equipmentAsDriver' => function ($query) {
                $query->latest('id');
            },
        ])
            ->where('is_on_company_payroll', 1)
            ->when($jobTypeId, function ($query) use ($jobTypeId) {
                $query->where('job_type_id', $jobTypeId);
            })
            ->when($ids->isNotEmpty(), function ($query) use ($ids) {
                $query->whereIn('id', $ids);
                $query->orderByRaw('FIELD(id,' . $ids->implode(',') . ')');
            }, function ($query) {
                $query->orderBy('id');
            })
            ->get();

        if ($workers->isEmpty()) {
            abort(404, 'No workers to export.');
        }

        $timestamp = now()->format('Y-m-d_His');
        $exportFolder = "workers-export/{$timestamp}";
        Storage::makeDirectory($exportFolder);
        $exportPath = Storage::path($exportFolder);
        $monthStart = $this->resolveSelectedMonthStart($request);

        $combinedDocxPath = $exportPath . DIRECTORY_SEPARATOR . 'workers-merged-' . $timestamp . '.docx';

        $docxPaths = [];
        foreach ($workers as $worker) {
            foreach ($this->workerDocumentShifts($worker) as $shift) {
                $suffix = $shift ? '-' . $shift : '';
                $workerDocxPath = $exportPath . DIRECTORY_SEPARATOR . 'worker-' . $worker->id . $suffix . '.docx';
                $this->generateWorkerDocxFromTemplate($worker, $project, $workerDocxPath, $monthStart, $shift);
                $docxPaths[] = $workerDocxPath;
            }
        }

        $this->mergeDocxFiles($docxPaths, $combinedDocxPath);

        foreach ($docxPaths as $docxPath) {
            @unlink($docxPath);
        }

        $monthAr = self::MONTH_NAMES[$monthStart->format('F')] ?? $monthStart->format('F');

        return $this->respondWithGeneratedDocument(
            $request,
            $combinedDocxPath,
            'سركي مجمع شهر ' . $monthAr . ' ' . $timestamp . '.docx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        );
    }

    public function exportWordPdf(Request $request, Worker $worker)
    {
        if ($queued = $this->queueDocumentIfRequested($request, __FUNCTION__, $worker)) {
            return $queued;
        }

        $worker->load([
            'company',
            'jobType',
            'equipmentAsDriver' => function ($query) {
                $query->latest('id');
            },
        ]);
        $project = Project::latest('id')->with('company')->first();

        $monthStart = $this->resolveSelectedMonthStart($request);
        $monthAr = self::MONTH_NAMES[$monthStart->format('F')] ?? $monthStart->format('F');

        $timestamp = now()->format('Y-m-d_His');
        $exportFolder = "workers-export/{$timestamp}";
        Storage::makeDirectory($exportFolder);
        $exportPath = Storage::path($exportFolder);

        $fileNameBase = $worker->name . ' - سركي - ' . $monthAr;
        $pdfFileName = $fileNameBase . '.pdf';

        $libreOfficePath = $this->findLibreOffice();
        if (! $libreOfficePath) {
            throw new \RuntimeException('LibreOffice is required to generate PDF files.');
        }

        $pdfPaths = [];
        foreach ($this->workerDocumentShifts($worker) as $shift) {
            $suffix = $shift ? '-' . $shift : '';
            $docxPath = $exportPath . DIRECTORY_SEPARATOR . 'worker-' . $worker->id . $suffix . '.docx';
            $this->generateWorkerDocxFromTemplate($worker, $project, $docxPath, $monthStart, $shift);
            $pdfPath = $this->convertDocxToPdf($libreOfficePath, $docxPath, $exportPath);

            if (! $pdfPath || ! file_exists($pdfPath) || filesize($pdfPath) <= 100) {
                throw new \RuntimeException('LibreOffice could not convert the document to PDF.');
            }

            $pdfPaths[] = [$pdfPath, $shift];
            @unlink($docxPath);
        }

        if (count($pdfPaths) === 1) {
            return $this->respondWithGeneratedDocument(
                $request,
                $pdfPaths[0][0],
                $pdfFileName,
                'application/pdf'
            );
        }

        $zipPath = $exportPath . DIRECTORY_SEPARATOR . $fileNameBase . '.zip';
        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        foreach ($pdfPaths as [$pdfPath, $shift]) {
            $zip->addFile($pdfPath, $fileNameBase . ' - ' . $shift . '.pdf');
        }
        $zip->close();

        foreach ($pdfPaths as [$pdfPath]) {
            @unlink($pdfPath);
        }

        return $this->respondWithGeneratedDocument(
            $request,
            $zipPath,
            $fileNameBase . '.zip',
            'application/zip'
        );
    }

    public function exportWordPdfAll(Request $request)
    {
        if ($queued = $this->queueDocumentIfRequested($request, __FUNCTION__)) {
            return $queued;
        }

        $ids = collect(explode(',', (string) $request->query('ids')))
            ->filter(fn ($v) => trim($v) !== '')
            ->map(fn ($v) => (int) $v)
            ->values();

        $jobTypeId = $request->filled('job_type_id') ? (int) $request->query('job_type_id') : null;
        $payrollOnly = $request->boolean('payroll_only', true);

        $project = Project::latest('id')->with('company')->first();

        $workers = Worker::with([
            'company',
            'jobType',
            'equipmentAsDriver' => function ($query) {
                $query->latest('id');
            },
        ])
            ->when($payrollOnly, function ($query) {
                $query->where('is_on_company_payroll', 1);
            })
            ->when($jobTypeId, function ($query) use ($jobTypeId) {
                $query->where('job_type_id', $jobTypeId);
            })
            ->when($ids->isNotEmpty(), function ($query) use ($ids) {
                $query->whereIn('id', $ids);
                $query->orderByRaw('FIELD(id,' . $ids->implode(',') . ')');
            }, function ($query) {
                $query->orderBy('id');
            })
            ->get();

        if ($workers->isEmpty()) {
            abort(404, 'No workers to export.');
        }

        $timestamp = now()->format('Y-m-d_His');
        $exportFolder = "workers-export/{$timestamp}";
        Storage::makeDirectory($exportFolder);
        $exportPath = Storage::path($exportFolder);
        $monthStart = $this->resolveSelectedMonthStart($request);

        $combinedDocxPath = $exportPath . DIRECTORY_SEPARATOR . 'workers-merged-' . $timestamp . '.docx';
        $combinedPdfPath = $exportPath . DIRECTORY_SEPARATOR . 'workers-merged-' . $timestamp . '.pdf';

        $groupDocxPaths = [];
        foreach ($workers->groupBy(fn (Worker $worker) => $this->isDriverJob($worker) ? 'driver' : 'default') as $type => $typeWorkers) {
            $typeDocxPaths = [];

            foreach ($typeWorkers as $worker) {
                foreach ($this->workerDocumentShifts($worker) as $shift) {
                    $suffix = $shift ? '-' . $shift : '';
                    $workerDocxPath = $exportPath . DIRECTORY_SEPARATOR . 'worker-' . $worker->id . $suffix . '.docx';
                    $this->generateWorkerDocxFromTemplate($worker, $project, $workerDocxPath, $monthStart, $shift);
                    $typeDocxPaths[] = $workerDocxPath;
                }
            }

            $typeDocxPath = $exportPath . DIRECTORY_SEPARATOR . 'workers-' . $type . '-' . $timestamp . '.docx';
            $this->mergeDocxFiles($typeDocxPaths, $typeDocxPath);
            $groupDocxPaths[] = $typeDocxPath;

            foreach ($typeDocxPaths as $typeDocxPath) {
                @unlink($typeDocxPath);
            }
        }

        $this->mergeDocxFiles($groupDocxPaths, $combinedDocxPath);

        $monthAr = self::MONTH_NAMES[$monthStart->format('F')] ?? $monthStart->format('F');

        $libreOfficePath = $this->findLibreOffice();
        if (! $libreOfficePath) {
            throw new \RuntimeException('LibreOffice is required to generate PDF files.');
        }

        $pdfPaths = [];
        foreach ($groupDocxPaths as $groupDocxPath) {
            $pdfPath = $this->convertDocxToPdf($libreOfficePath, $groupDocxPath, $exportPath);
            if (! $pdfPath) {
                throw new \RuntimeException('LibreOffice could not convert the document to PDF.');
            }

            $pdfPaths[] = $pdfPath;
        }

        $fpdi = new Fpdi();
        foreach ($pdfPaths as $path) {
            $pageCount = $fpdi->setSourceFile($path);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $tplId = $fpdi->importPage($pageNo);
                $size = $fpdi->getTemplateSize($tplId);
                $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
                $fpdi->AddPage($orientation, [$size['width'], $size['height']]);
                $fpdi->useTemplate($tplId);
            }
        }
        $fpdi->Output($combinedPdfPath, 'F');

        foreach ($groupDocxPaths as $groupDocxPath) {
            @unlink($groupDocxPath);
        }

        foreach ($pdfPaths as $pdfPath) {
            @unlink($pdfPath);
        }

        if (! file_exists($combinedPdfPath) || filesize($combinedPdfPath) <= 100) {
            throw new \RuntimeException('The merged PDF file was not created.');
        }

        return $this->respondWithGeneratedDocument(
            $request,
            $combinedPdfPath,
            'سركي مجمع شهر ' . $monthAr . ' ' . $timestamp . '.pdf',
            'application/pdf'
        );
    }

    private function generateWorkerDocxFromTemplate(Worker $worker, $project, string $outputPath, Carbon $monthStart, ?string $shift = null): void
    {
        $templatePath = $this->resolveWorkerTemplatePath($worker);
        $daysInMonth = $monthStart->daysInMonth;

        $weekdayRows = [];

        for ($i = 0; $i < $daysInMonth; $i++) {
            $day = $monthStart->copy()->addDays($i);
            $base = [
                'serial' => $i + 1,
                'date' => $day->format('j/n/Y'),
                'start' => '',
                'end' => '',
                'break' => '',
                'hours' => '',
                'location' => '',
                'note' => '',
                'supervisor' => '',
                'engineer' => '',
            ];

            $weekdayRows[] = [
                'row_serial' => $base['serial'],
                'row_date' => $base['date'],
                'row_start' => $base['start'],
                'row_end' => $base['end'],
                'row_break' => $base['break'],
                'row_hours' => $base['hours'],
                'row_location' => $base['location'],
                'row_note' => $base['note'],
                'row_supervisor' => $base['supervisor'],
                'row_engineer' => $base['engineer'],
            ];
        }

        $processor = new TemplateProcessor($templatePath);
        $consortiumFixed =
             $this->rtl(' للمقاولات ')
            . $this->ltr(' FM+ ')
            . $this->rtl(' تحالف الشيماء الزراعية للمقاولات والتوريدات ');

        $processor->setValues([
            'project_name_en' => optional($project)->name ?? 'محطة كهرباء أبيدوس2 للطاقة الشمسية بقدرة 1000 ميجاوات 
PV Power Plant Abydos 2 Solar (MW1000)',
            'company_name' => $consortiumFixed,
            'consortium_name' => optional(optional($project)->company)->name
                ?? (optional($worker->company)->name ?: (optional($worker->company)->short_name ?? '')),
            'worker_name' => $worker->name ?? '',
            'worker_job' => optional($worker->jobType)->name ?? '',
            'worker_id' => $worker->national_id ?? '',
            'worker_phone' => $worker->phone_number ?? '',
            'access_code' => $worker->entity ?? "",
            'shift' => $shift ?? '',
            'report_month' => $monthStart->format('F Y'),
            'equipment' => $this->workerEquipmentValue($worker, 'equipment'),
            'equipment_type' => $this->workerEquipmentValue($worker, 'equipment_type'),
            'equipment_code' => $this->workerEquipmentValue($worker, 'equipment_code'),
            'equipment_number' => $this->workerEquipmentValue($worker, 'equipment_number'),
            'equipment_model' => $this->workerEquipmentValue($worker, 'equipment_model'),
        ]);

        $this->fillAllTimesheetTables($processor, $weekdayRows);
        $processor->saveAs($outputPath);
        $this->addRedShadingToFridayCells($outputPath);
    }

    private function workerDocumentShifts(Worker $worker): array
    {
        return $this->isDriverJob($worker) ? ['صباحي', 'مسائي'] : [null];
    }

    private function fillAllTimesheetTables(TemplateProcessor $processor, array $weekdayRows): void
    {
        try {
            $processor->cloneRowAndSetValues('row_serial', $weekdayRows);
        } catch (\Throwable $e) {
        }
    }

    private function resolveWorkerTemplatePath(?Worker $worker = null): string
    {
        $defaultTemplatePath = storage_path('app/templates/' . self::DEFAULT_WORKER_TEMPLATE);
        $driverTemplatePath = storage_path('app/templates/' . self::DRIVER_WORKER_TEMPLATE);

        if ($worker && $this->isDriverJob($worker) && file_exists($driverTemplatePath)) {
            return $driverTemplatePath;
        }

        if (file_exists($defaultTemplatePath)) {
            return $defaultTemplatePath;
        }

        abort(404, 'Word template not found. Add templates at storage/app/templates/' . self::DEFAULT_WORKER_TEMPLATE . ' and (for driver jobs) storage/app/templates/' . self::DRIVER_WORKER_TEMPLATE . '.');
    }

    private function isDriverJob(Worker $worker): bool
    {
        $jobName = (string) optional($worker->jobType)->name;

        return Str::contains($jobName, 'سائق');
    }

    private function workerEquipmentValue(Worker $worker, string $field): string
    {
        $equipment = $this->primaryWorkerEquipment($worker);

        if (! $equipment) {
            return '';
        }

        if ($field === 'equipment_type') {
            return (string) ($equipment->equipment_type ?? '');
        }

        if ($field === 'equipment_code') {
            return (string) ($equipment->equipment_code ?? '');
        }

        if ($field === 'equipment_number') {
            return (string) ($equipment->equipment_number ?? '');
        }

        if ($field === 'equipment_model') {
            $model = trim(implode(' ', array_filter([
                $equipment->manufacture ?? null,
                $equipment->model_year ?? null,
            ])));

            return $model !== '' ? $model : '';
        }

        if ($field === 'equipment') {
            $label = trim(implode(' - ', array_filter([
                $equipment->equipment_type ?? null,
                $equipment->equipment_number ?? null,
            ])));

            return $label !== '' ? $label : '';
        }

        return '';
    }

    private function primaryWorkerEquipment(Worker $worker)
    {
        if ($worker->relationLoaded('equipmentAsDriver')) {
            return $worker->equipmentAsDriver->first();
        }

        return $worker->equipmentAsDriver()->latest('id')->first();
    }

    private function convertDocxToPdf(string $libreOfficePath, string $docxPath, string $outputDir): ?string
    {
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $profileDir = $outputDir . DIRECTORY_SEPARATOR . '.lo-profile-' . bin2hex(random_bytes(6));
        $profileUri = 'file:///' . ltrim(str_replace(DIRECTORY_SEPARATOR, '/', $profileDir), '/');

        @mkdir($profileDir, 0775, true);

        if ($isWindows) {
            $command = sprintf(
                '%s --headless --norestore --nofirststartwizard --nodefault --nolockcheck -env:UserInstallation=%s --convert-to pdf --outdir %s %s 2>&1',
                escapeshellarg($libreOfficePath),
                escapeshellarg($profileUri),
                escapeshellarg($outputDir),
                escapeshellarg($docxPath)
            );
        } else {
            $homeDir = $outputDir . DIRECTORY_SEPARATOR . '.home';
            $cacheDir = $outputDir . DIRECTORY_SEPARATOR . '.cache';
            $configDir = $outputDir . DIRECTORY_SEPARATOR . '.config';

            @mkdir($homeDir, 0775, true);
            @mkdir($cacheDir, 0775, true);
            @mkdir($configDir, 0775, true);

            $command = sprintf(
                'HOME=%s XDG_CACHE_HOME=%s XDG_CONFIG_HOME=%s %s --headless --norestore --nofirststartwizard --nodefault --nolockcheck -env:UserInstallation=%s --convert-to pdf --outdir %s %s 2>&1',
                escapeshellarg($homeDir),
                escapeshellarg($cacheDir),
                escapeshellarg($configDir),
                escapeshellarg($libreOfficePath),
                escapeshellarg($profileUri),
                escapeshellarg($outputDir),
                escapeshellarg($docxPath)
            );
        }

        $process = proc_open($command, [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ], $pipes);

        if (! is_resource($process)) {
            return null;
        }

        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);
        $output = '';
        $startedAt = microtime(true);
        $timedOut = false;

        do {
            $output .= stream_get_contents($pipes[1]);
            $output .= stream_get_contents($pipes[2]);
            $status = proc_get_status($process);

            if (! $status['running']) {
                break;
            }

            if (microtime(true) - $startedAt >= 90) {
                $timedOut = true;
                proc_terminate($process);
                break;
            }

            usleep(100000);
        } while (true);

        $output .= stream_get_contents($pipes[1]);
        $output .= stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $returnCode = proc_close($process);

        $pdfPath = $outputDir . DIRECTORY_SEPARATOR . pathinfo($docxPath, PATHINFO_FILENAME) . '.pdf';

        if (! $timedOut && $returnCode === 0 && file_exists($pdfPath) && filesize($pdfPath) > 100) {
            @rmdir($profileDir);

            return $pdfPath;
        }

        \Log::warning('LibreOffice conversion failed or timed out', [
            'command' => $command,
            'return_code' => $returnCode,
            'timed_out' => $timedOut,
            'output' => $output,
            'docx' => $docxPath,
            'expected_pdf' => $pdfPath,
        ]);

        @rmdir($profileDir);

        return null;
    }

    private function mergeDocxFiles(array $docxPaths, string $outputPath): void
    {
        if (empty($docxPaths)) {
            throw new \RuntimeException('No DOCX files to merge.');
        }

        copy($docxPaths[0], $outputPath);

        $baseZip = new \ZipArchive();
        if ($baseZip->open($outputPath) !== true) {
            throw new \RuntimeException('Unable to open base DOCX for merge.');
        }

        $baseXml = $baseZip->getFromName('word/document.xml');
        if ($baseXml === false) {
            $baseZip->close();
            throw new \RuntimeException('Base DOCX document.xml not found.');
        }

        $baseDom = new \DOMDocument();
        $baseDom->loadXML($baseXml);
        $baseXpath = new \DOMXPath($baseDom);
        $baseXpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $baseRelationshipsXml = $baseZip->getFromName('word/_rels/document.xml.rels');
        if ($baseRelationshipsXml === false) {
            $baseZip->close();
            throw new \RuntimeException('Base DOCX relationships not found.');
        }

        $baseRelationshipsDom = new \DOMDocument();
        $baseRelationshipsDom->loadXML($baseRelationshipsXml);
        $baseRelationships = $baseRelationshipsDom->documentElement;
        $relationshipNamespace = 'http://schemas.openxmlformats.org/package/2006/relationships';
        $nextRelationshipId = 1;

        foreach ($baseRelationships->childNodes as $relationship) {
            if ($relationship->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            if (preg_match('/^rId(\d+)$/', $relationship->getAttribute('Id'), $matches)) {
                $nextRelationshipId = max($nextRelationshipId, (int) $matches[1] + 1);
            }
        }

        $baseBody = $baseXpath->query('//w:body')->item(0);
        if (! $baseBody) {
            $baseZip->close();
            throw new \RuntimeException('Base DOCX body not found.');
        }

        $baseSectPr = $baseXpath->query('./w:sectPr', $baseBody)->item(0);

        foreach (array_slice($docxPaths, 1) as $path) {
            $zip = new \ZipArchive();
            if ($zip->open($path) !== true) {
                continue;
            }

            $xml = $zip->getFromName('word/document.xml');
            $relationshipsXml = $zip->getFromName('word/_rels/document.xml.rels');

            $sourceImages = [];
            if ($relationshipsXml !== false) {
                $relationshipsDom = new \DOMDocument();
                $relationshipsDom->loadXML($relationshipsXml);

                foreach ($relationshipsDom->documentElement->childNodes as $relationship) {
                    if ($relationship->nodeType !== XML_ELEMENT_NODE
                        || $relationship->getAttribute('Type')
                            !== 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/image') {
                        continue;
                    }

                    $target = ltrim($relationship->getAttribute('Target'), '/');
                    $sourceEntry = str_starts_with($target, 'word/') ? $target : 'word/' . $target;
                    $imageContents = $zip->getFromName($sourceEntry);

                    if ($imageContents !== false) {
                        $sourceImages[$relationship->getAttribute('Id')] = [
                            'contents' => $imageContents,
                            'extension' => pathinfo($target, PATHINFO_EXTENSION) ?: 'png',
                        ];
                    }
                }
            }

            $zip->close();

            if ($xml === false) {
                continue;
            }

            $dom = new \DOMDocument();
            $dom->loadXML($xml);
            $xpath = new \DOMXPath($dom);
            $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

            $relationshipMap = [];
            foreach ($sourceImages as $sourceRelationshipId => $image) {
                $newRelationshipId = 'rId' . $nextRelationshipId++;
                $imageName = 'merged-image-' . ($nextRelationshipId - 1) . '.' . $image['extension'];

                $baseZip->addFromString('word/media/' . $imageName, $image['contents']);

                $relationship = $baseRelationshipsDom->createElementNS(
                    $relationshipNamespace,
                    'Relationship'
                );
                $relationship->setAttribute('Id', $newRelationshipId);
                $relationship->setAttribute(
                    'Type',
                    'http://schemas.openxmlformats.org/officeDocument/2006/relationships/image'
                );
                $relationship->setAttribute('Target', 'media/' . $imageName);
                $baseRelationships->appendChild($relationship);

                $relationshipMap[$sourceRelationshipId] = $newRelationshipId;
            }

            foreach ($relationshipMap as $sourceRelationshipId => $newRelationshipId) {
                $imageReferences = $xpath->query(
                    '//*[local-name()="blip"]/@*[local-name()="embed" and .="' . $sourceRelationshipId . '"]'
                );

                foreach ($imageReferences as $imageReference) {
                    $imageReference->nodeValue = $newRelationshipId;
                }
            }

            $body = $xpath->query('//w:body')->item(0);
            if (! $body) {
                continue;
            }

            foreach ($body->childNodes as $node) {
                if ($node->nodeType === XML_ELEMENT_NODE && $node->localName === 'sectPr') {
                    continue;
                }

                $imported = $baseDom->importNode($node, true);
                if ($baseSectPr) {
                    $baseBody->insertBefore($imported, $baseSectPr);
                } else {
                    $baseBody->appendChild($imported);
                }
            }
        }

        $baseZip->deleteName('word/document.xml');
        $baseZip->addFromString('word/document.xml', $baseDom->saveXML());
        $baseZip->deleteName('word/_rels/document.xml.rels');
        $baseZip->addFromString('word/_rels/document.xml.rels', $baseRelationshipsDom->saveXML());
        $baseZip->close();
    }

    private function addRedShadingToFridayCells($filePath)
    {
        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            return;
        }

        $xml = $zip->getFromName('word/document.xml');
        if ($xml === false) {
            $zip->close();
            return;
        }

        $dom = new \DOMDocument();
        $dom->loadXML($xml);

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $rows = $xpath->query('//w:tr');

        foreach ($rows as $row) {
            $rowXml = $dom->saveXML($row);
            $isPaddingRow = str_contains($rowXml, '__padding_row__');

            if ($isPaddingRow) {
                foreach ($xpath->query('.//w:t', $row) as $textNode) {
                    if (trim($textNode->textContent) === '__padding_row__') {
                        $textNode->nodeValue = '';
                    }
                }
            }

            $rowProperties = $xpath->query('./w:trPr', $row)->item(0);
            if (! $rowProperties) {
                $rowProperties = $dom->createElementNS(
                    'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
                    'w:trPr'
                );
                $row->insertBefore($rowProperties, $row->firstChild);
            }

            if ($xpath->query('./w:cantSplit', $rowProperties)->length === 0) {
                $rowProperties->appendChild($dom->createElementNS(
                    'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
                    'w:cantSplit'
                ));
            }

            if ($isPaddingRow) {
                foreach ($xpath->query('.//w:tc', $row) as $cell) {
                    $tcProperties = $xpath->query('./w:tcPr', $cell)->item(0);
                    if (! $tcProperties) {
                        $tcProperties = $dom->createElementNS(
                            'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
                            'w:tcPr'
                        );
                        $cell->insertBefore($tcProperties, $cell->firstChild);
                    }

                    foreach ($xpath->query('./w:tcBorders', $tcProperties) as $borders) {
                        $tcProperties->removeChild($borders);
                    }

                    $borders = $dom->createElementNS(
                        'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
                        'w:tcBorders'
                    );
                    foreach (['top', 'left', 'bottom', 'right', 'insideH', 'insideV'] as $side) {
                        $border = $dom->createElementNS(
                            'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
                            'w:' . $side
                        );
                        $border->setAttribute('w:val', 'nil');
                        $borders->appendChild($border);
                    }
                    $tcProperties->appendChild($borders);
                }
            }

            if ($this->isFridayRow($rowXml)) {
                $cells = $xpath->query('.//w:tc', $row);

                foreach ($cells as $cell) {
                    $tcPrList = $xpath->query('.//w:tcPr', $cell);

                    if ($tcPrList->length > 0) {
                        $tcPr = $tcPrList->item(0);
                    } else {
                        $tcPr = $dom->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:tcPr');
                        $cell->insertBefore($tcPr, $cell->firstChild);
                    }

                    $existingShd = $xpath->query('.//w:shd', $tcPr);
                    foreach ($existingShd as $shd) {
                        $tcPr->removeChild($shd);
                    }

                    $shd = $dom->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:shd');
                    $shd->setAttribute('w:val', 'clear');
                    $shd->setAttribute('w:color', 'auto');
                    // Light blue fill for Friday rows (was red 'FF0000')
                    $shd->setAttribute('w:fill', 'D9EDF7');
                    $tcPr->appendChild($shd);
                }
            }
        }

        $zip->deleteName('word/document.xml');
        $zip->addFromString('word/document.xml', $dom->saveXML());
        $zip->close();
    }

    private function isFridayRow($rowXml)
    {
        if (preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $rowXml, $matches)) {
            try {
                $date = Carbon::createFromFormat('j/n/Y', $matches[0]);
                return $date->isFriday();
            } catch (\Exception $e) {
                return false;
            }
        }

        return false;
    }

    private function findLibreOffice()
    {
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        if ($isWindows) {
            exec('where soffice 2>nul', $output, $returnCode);
        } else {
            exec('which soffice 2>/dev/null', $output, $returnCode);
            if ($returnCode !== 0 || empty($output[0])) {
                exec('which libreoffice 2>/dev/null', $output, $returnCode);
            }
        }

        if ($returnCode === 0 && ! empty($output[0])) {
            return trim($output[0]);
        }

        if ($isWindows) {
            $paths = [
                'C:\\Program Files\\LibreOffice\\program\\soffice.exe',
                'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe',
                'C:\\LibreOffice\\program\\soffice.exe',
            ];
        } else {
            $paths = [
                '/usr/bin/soffice',
                '/usr/bin/libreoffice',
                '/usr/local/bin/soffice',
                '/usr/local/bin/libreoffice',
                '/snap/bin/libreoffice',
                '/opt/libreoffice/program/soffice',
                '/Applications/LibreOffice.app/Contents/MacOS/soffice',
            ];
        }

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    public function exportDailyEquipmentInspection(Request $request, Worker $worker)
    {
        if ($queued = $this->queueDocumentIfRequested($request, __FUNCTION__, $worker)) {
            return $queued;
        }

        $worker->load(['equipmentAsDriver' => function ($query) {
            $query->latest('id');
        }]);

        $equipment = $worker->equipmentAsDriver->first();
        if (!$equipment) {
            abort(404, 'لا توجد معدة مخصصة لهذا العامل');
        }

        $templatePath = storage_path('app/templates/' . self::EQUIPMENT_INSPECTION_TEMPLATE);
        if (!file_exists($templatePath)) {
            abort(404, 'فحص يومي للمعدة قالب غير موجود. أضف القالب في ' . $templatePath);
        }

        $processor = new TemplateProcessor($templatePath);

        $processor->setValues([
            'worker_name' => $worker->name ?? '',
            'worker_id' => $worker->national_id ?? '',
            'worker_phone' => $worker->phone_number ?? '',
            'equipment_code' => $equipment->equipment_code ?? '',
            'equipment_type' => $equipment->equipment_type ?? '',
            'equipment_number' => $equipment->equipment_number ?? '',
            'equipment_model' => trim(implode(' ', array_filter([
                $equipment->manufacture ?? null,
                $equipment->model_year ?? null,
            ]))) ?: '',
            'inspection_date' => now()->format('d/m/Y'),
            'inspection_time' => now()->format('H:i'),
        ]);

        Storage::makeDirectory('temp');
        $fileName = 'فحص-يومي-جرار-' . $worker->name . '-' . now()->format('Y-m-d_His') . '.docx';
        $tempPath = Storage::path('temp/' . $fileName);
        $processor->saveAs($tempPath);

        return $this->respondWithGeneratedDocument(
            $request,
            $tempPath,
            $fileName,
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        );
    }

    public function preview(Worker $worker)
    {
        $worker->load(['company', 'jobType']);
        $project = Project::latest('id')->with('company')->first();

        return view('back.workers.export', [
            'worker' => $worker,
            'project' => $project,
        ]);
    }

    private function respondWithGeneratedDocument(Request $request, string $filePath, string $fileName, string $mimeType)
    {
        if ($request->headers->has('X-Worker-Document-Job')) {
            return response()->json([
                'document' => [
                    'path' => $filePath,
                    'name' => $fileName,
                    'mime' => $mimeType,
                ],
            ]);
        }

        if ($request->ajax() || $request->expectsJson()) {
            $token = (string) Str::uuid();

            Cache::put($this->generatedDocumentCacheKey($token), [
                'path' => $filePath,
                'name' => $fileName,
                'mime' => $mimeType,
            ], now()->addMinutes(15));

            return response()->json([
                'download_url' => route('workers.documents.download', ['token' => $token], false),
                'filename' => $fileName,
            ]);
        }

        return response()->download(
            $filePath,
            $fileName,
            ['Content-Type' => $mimeType]
        )->deleteFileAfterSend(true);
    }

    private function queueDocumentIfRequested(Request $request, string $action, ?Worker $worker = null)
    {
        if (! ($request->ajax() || $request->expectsJson())
            || $request->headers->has('X-Worker-Document-Job')) {
            return null;
        }

        $token = (string) Str::uuid();

        Cache::put('workers.generated-document.status.' . $token, [
            'status' => 'pending',
        ], now()->addMinutes(15));

        GenerateWorkerDocument::dispatch(
            $action,
            $token,
            $request->query(),
            $worker?->getKey(),
        );

        return response()->json([
            'status_url' => route('workers.documents.status', ['token' => $token], false),
        ], 202);
    }

    private function generatedDocumentCacheKey(string $token): string
    {
        return 'workers.generated-document.' . $token;
    }

    private function relativeGeneratedDocumentPath(string $filePath): string
    {
        $storageRoot = rtrim(str_replace('\\', '/', storage_path('app/private')), '/') . '/';
        $normalizedPath = str_replace('\\', '/', $filePath);

        if (str_starts_with($normalizedPath, $storageRoot)) {
            return substr($normalizedPath, strlen($storageRoot));
        }

        return $filePath;
    }

    private function resolveGeneratedDocumentPath(mixed $filePath): ?string
    {
        if (! is_string($filePath) || trim($filePath) === '') {
            return null;
        }

        $candidates = [];

        if (file_exists($filePath)) {
            $candidates[] = $filePath;
        }

        try {
            $candidates[] = Storage::disk('local')->path($filePath);
        } catch (\Throwable) {
        }

        $normalizedPath = str_replace('\\', '/', $filePath);
        foreach (['/storage/app/private/', '/storage/app/'] as $storageMarker) {
            $storagePosition = strripos($normalizedPath, $storageMarker);

            if ($storagePosition !== false) {
                $relativePath = str_replace('/', DIRECTORY_SEPARATOR, substr($normalizedPath, $storagePosition + strlen($storageMarker)));
                $candidates[] = storage_path('app/private/' . $relativePath);
                $candidates[] = storage_path('app/' . $relativePath);
                break;
            }
        }

        $relativePath = ltrim(str_replace('/', DIRECTORY_SEPARATOR, $normalizedPath), DIRECTORY_SEPARATOR);
        $candidates[] = storage_path('app/private/' . $relativePath);
        $candidates[] = storage_path('app/' . $relativePath);
        $candidates[] = $filePath;

        foreach (array_unique($candidates) as $candidate) {
            if (file_exists($candidate) && is_readable($candidate)) {
                return $candidate;
            }
        }

        // Secondary check: file exists even if not readable, or first candidate
        foreach (array_unique($candidates) as $candidate) {
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        return $candidates[0] ?? null;
    }
}
