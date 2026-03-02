<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Worker;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\TemplateProcessor;
use setasign\Fpdi\Fpdi;

class WorkerDocumentController extends Controller
{
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

    public function exportPdf(Worker $worker)
    {
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

        return $pdf->download('worker-' . $worker->name . '.pdf');
    }

    public function exportPdfMerged(Request $request)
    {
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

        return response()->download(
            $mergedPath,
            'workers-merged-' . $timestamp . '.pdf',
            ['Content-Type' => 'application/pdf']
        )->deleteFileAfterSend(true);
    }

    private function rtl(string $text): string
    {
        return "\u{200F}" . trim($text) . "\u{200F}";
    }

    private function ltr(string $text): string
    {
        return "\u{200E}" . trim($text) . "\u{200E}";
    }

    public function exportWord(Worker $worker)
    {
        $worker->load(['company', 'jobType']);
        $project = Project::latest('id')->with('company')->first();

        $templatePath = storage_path('app/templates/worker-timesheet.docx');

        if (! file_exists($templatePath)) {
            abort(404, 'Word template not found. Add a template at storage/app/templates/worker-timesheet.docx with the expected placeholders.');
        }

        $monthStart = now()->startOfMonth();
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

        $companyAr = $worker->company->name_ar ?? $worker->company->name ?? '';
        $companyEn = $worker->company->name_en ?? $worker->company->short_name ?? '';
        $consortiumFixed =
             $this->rtl(' للمقاولات ')
            . $this->ltr(' FM+ ')
            . $this->rtl(' تحالف الشيماء الزراعية للمقاولات والتوريدات ');
        $processor = new TemplateProcessor($templatePath);

        $processor->setValues([
            'project_name_en' => optional($project)->name ?? 'محطة كهرباء أبيدوس2 للطاقة الشمسية بقدرة 1000 ميجاوات 
PV Power Plant Abydos 2 Solar (MW1000)',
            'company_name' => $consortiumFixed,
            'consortium_name' => optional(optional($project)->company)->name
                ?? (optional($worker->company)->name ?: (optional($worker->company)->short_name ?? '-')),
            'worker_name' => $worker->name ?? '-',
            'worker_job' => optional($worker->jobType)->name ?? '-',
            'worker_id' => $worker->national_id ?? '-',
            'worker_phone' => $worker->phone_number ?? '-',
            'access_code' => $worker->entity ?? " ",
            'report_month' => $monthStart->format('F Y'),
        ]);

        $processor->cloneRowAndSetValues('row_serial', $weekdayRows);

        // $fileName = $worker->name . ' - سركي.docx';

        $monthAr = self::MONTH_NAMES[$monthStart->format('F')] ?? $monthStart->format('F');
        $fileName = $worker->name . '  - سركي - ' . $monthAr . '.docx';
        $tempPath = 'temp/' . $fileName;

        Storage::makeDirectory('temp');
        $fullPath = Storage::path($tempPath);
        $processor->saveAs($fullPath);

        $this->addRedShadingToFridayCells($fullPath);

        return response()->download(
            $fullPath,
            $fileName,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
        )->deleteFileAfterSend(true);
    }

    public function exportWordAll(Request $request)
    {
        $templatePath = storage_path('app/templates/worker-timesheet.docx');

        if (! file_exists($templatePath)) {
            abort(404, 'Word template not found. Add a template at storage/app/templates/worker-timesheet.docx with the expected placeholders.');
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

        Storage::makeDirectory('temp');
        $tempDir = Storage::path('temp');
        $docxPaths = [];

        foreach ($workers as $worker) {
            $monthStart = now()->startOfMonth();
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
                    ?? (optional($worker->company)->name ?: (optional($worker->company)->short_name ?? '-')),
                'worker_name' => $worker->name ?? '-',
                'worker_job' => optional($worker->jobType)->name ?? '-',
                'worker_id' => $worker->national_id ?? '-',
                'worker_phone' => $worker->phone_number ?? '-',
                'access_code' => $worker->entity ?? "",
                'report_month' => $monthStart->format('F Y'),
            ]);

            $processor->cloneRowAndSetValues('row_serial', $weekdayRows);

            $fileName = 'worker-' . $worker->id . '-' . preg_replace('/[^a-zA-Z0-9]/', '', $worker->name) . '.docx';
            $docxPath = $tempDir . DIRECTORY_SEPARATOR . $fileName;

            $processor->saveAs($docxPath);
            $this->addRedShadingToFridayCells($docxPath);

            $docxPaths[] = $docxPath;
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

        return response()->download(
            $zipPath,
            'workers-timesheets.zip',
            ['Content-Type' => 'application/zip']
        )->deleteFileAfterSend(true);
    }

    public function exportWordMerged(Request $request)
    {
        $templatePath = storage_path('app/templates/worker-timesheet.docx');

        if (! file_exists($templatePath)) {
            abort(404, 'Word template not found. Add a template at storage/app/templates/worker-timesheet.docx with the expected placeholders.');
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

        $timestamp = now()->format('Y-m-d_His');
        $exportFolder = "workers-export/{$timestamp}";
        Storage::makeDirectory($exportFolder);
        $exportPath = Storage::path($exportFolder);

        $combinedDocxPath = $exportPath . DIRECTORY_SEPARATOR . 'workers-merged-' . $timestamp . '.docx';

        $docxPaths = [];
        foreach ($workers as $worker) {
            $workerDocxPath = $exportPath . DIRECTORY_SEPARATOR . 'worker-' . $worker->id . '.docx';
            $this->generateWorkerDocxFromTemplate($worker, $project, $templatePath, $workerDocxPath);
            $docxPaths[] = $workerDocxPath;
        }

        $this->mergeDocxFiles($docxPaths, $combinedDocxPath);

        foreach ($docxPaths as $docxPath) {
            @unlink($docxPath);
        }

        $monthStart = now()->startOfMonth();
        $monthAr = self::MONTH_NAMES[$monthStart->format('F')] ?? $monthStart->format('F');

        return response()->download(
            $combinedDocxPath,
            'سركي مجمع شهر ' . $monthAr . ' ' . $timestamp . '.docx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
        )->deleteFileAfterSend(true);
    }

    // public function exportWordPdf(Worker $worker)
    // {
    //     if (! class_exists(\Dompdf\Dompdf::class)) {
    //         abort(500, 'PDF rendering requires dompdf. Install with: composer require dompdf/dompdf');
    //     }

    //     $worker->load(['company', 'jobType']);
    //     $project = Project::latest('id')->with('company')->first();

    //     $templatePath = storage_path('app/templates/worker-timesheet.docx');

    //     if (! file_exists($templatePath)) {
    //         abort(404, 'Word template not found. Add a template at storage/app/templates/worker-timesheet.docx with the expected placeholders.');
    //     }

    //     $monthStart = now()->startOfMonth();
    //     $daysInMonth = $monthStart->daysInMonth;

    //     $weekdayRows = [];

    //     for ($i = 0; $i < $daysInMonth; $i++) {
    //         $day = $monthStart->copy()->addDays($i);
    //         $base = [
    //             'serial' => $i + 1,
    //             'date' => $day->format('j/n/Y'),
    //             'start' => '',
    //             'end' => '',
    //             'break' => '',
    //             'hours' => '',
    //             'location' => '',
    //             'note' => '',
    //             'supervisor' => '',
    //             'engineer' => '',
    //         ];

    //         $weekdayRows[] = [
    //             'row_serial' => $base['serial'],
    //             'row_date' => $base['date'],
    //             'row_start' => $base['start'],
    //             'row_end' => $base['end'],
    //             'row_break' => $base['break'],
    //             'row_hours' => $base['hours'],
    //             'row_location' => $base['location'],
    //             'row_note' => $base['note'],
    //             'row_supervisor' => $base['supervisor'],
    //             'row_engineer' => $base['engineer'],
    //         ];
    //     }

    //     $processor = new TemplateProcessor($templatePath);

    //     $processor->setValues([
    //         'project_name_en' => optional($project)->name ?? '-',
    //         'company_name' => optional($worker->company)->name ?: (optional($worker->company)->short_name ?? '-'),
    //         'consortium_name' => optional(optional($project)->company)->name
    //             ?? (optional($worker->company)->name ?: (optional($worker->company)->short_name ?? '-')),
    //         'worker_name' => $worker->name ?? '-',
    //         'worker_job' => optional($worker->jobType)->name ?? '-',
    //         'worker_id' => $worker->national_id ?? '-',
    //         'worker_phone' => $worker->phone_number ?? '-',
    //         'access_code' => $worker->entity ?? $worker->id,
    //         'report_month' => $monthStart->format('F Y'),
    //     ]);

    //     $processor->cloneRowAndSetValues('row_serial', $weekdayRows);

    //     Storage::makeDirectory('temp');
    //     $docxPath = Storage::path('temp/worker-' . $worker->id . '.docx');
    //     $pdfPath = Storage::path('temp/worker-' . $worker->id . '.pdf');

    //     $processor->saveAs($docxPath);
    //     $this->addRedShadingToFridayCells($docxPath);

    //     Settings::setPdfRenderer(Settings::PDF_RENDERER_DOMPDF, base_path('vendor/dompdf/dompdf'));

    //     $phpWord = IOFactory::load($docxPath);
    //     $pdfWriter = IOFactory::createWriter($phpWord, 'PDF');
    //     $pdfWriter->save($pdfPath);

    //     @unlink($docxPath);

    //     return response()->download(
    //         $pdfPath,
    //         'worker-' . $worker->id . '.pdf',
    //         ['Content-Type' => 'application/pdf']
    //     )->deleteFileAfterSend(true);
    // }

    public function exportWordPdfAll(Request $request)
    {
        $templatePath = storage_path('app/templates/worker-timesheet.docx');

        if (! file_exists($templatePath)) {
            abort(404, 'Word template not found. Add a template at storage/app/templates/worker-timesheet.docx with the expected placeholders.');
        }

        $ids = collect(explode(',', (string) $request->query('ids')))
            ->filter(fn ($v) => trim($v) !== '')
            ->map(fn ($v) => (int) $v)
            ->values();

        $jobTypeId = $request->filled('job_type_id') ? (int) $request->query('job_type_id') : null;
        $payrollOnly = $request->boolean('payroll_only', true);

        $project = Project::latest('id')->with('company')->first();

        $workers = Worker::with(['company', 'jobType'])
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

        $combinedDocxPath = $exportPath . DIRECTORY_SEPARATOR . 'workers-merged-' . $timestamp . '.docx';
        $combinedPdfPath = $exportPath . DIRECTORY_SEPARATOR . 'workers-merged-' . $timestamp . '.pdf';

        $docxPaths = [];
        foreach ($workers as $worker) {
            $workerDocxPath = $exportPath . DIRECTORY_SEPARATOR . 'worker-' . $worker->id . '.docx';
            $this->generateWorkerDocxFromTemplate($worker, $project, $templatePath, $workerDocxPath);
            $docxPaths[] = $workerDocxPath;
        }

        $this->mergeDocxFiles($docxPaths, $combinedDocxPath);

        $monthStart = now()->startOfMonth();
        $monthAr = self::MONTH_NAMES[$monthStart->format('F')] ?? $monthStart->format('F');

        $libreOfficePath = $this->findLibreOffice();
        if (! $libreOfficePath) {
            foreach ($docxPaths as $docxPath) {
                @unlink($docxPath);
            }

            return response()->download(
                $combinedDocxPath,
                'سركي مجمع شهر ' . $monthAr . ' ' . $timestamp . '.docx',
                ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
            )->deleteFileAfterSend(true);
        }

        $combinedPdfGenerated = $this->convertDocxToPdf($libreOfficePath, $combinedDocxPath, $exportPath);

        if (! $combinedPdfGenerated) {
            $pdfPaths = [];
            foreach ($docxPaths as $docxPath) {
                $pdfPath = $this->convertDocxToPdf($libreOfficePath, $docxPath, $exportPath);
                if ($pdfPath) {
                    $pdfPaths[] = $pdfPath;
                }
            }

            if (empty($pdfPaths)) {
                foreach ($docxPaths as $docxPath) {
                    @unlink($docxPath);
                }

                return response()->download(
                    $combinedDocxPath,
                    'سركي مجمع شهر ' . $monthAr . ' ' . $timestamp . '.docx',
                    ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
                )->deleteFileAfterSend(true);
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
        }

        foreach ($docxPaths as $docxPath) {
            @unlink($docxPath);
        }

        if (! file_exists($combinedPdfPath) || filesize($combinedPdfPath) <= 100) {
            return response()->download(
                $combinedDocxPath,
                'سركي مجمع شهر ' . $monthAr . ' ' . $timestamp . '.docx',
                ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
            )->deleteFileAfterSend(true);
        }

        return response()->download(
            $combinedPdfPath,
            'سركي مجمع شهر ' . $monthAr . ' ' . $timestamp . '.pdf',
            ['Content-Type' => 'application/pdf']
        )->deleteFileAfterSend(true);
    }

    private function generateWorkerDocxFromTemplate(Worker $worker, $project, string $templatePath, string $outputPath): void
    {
        $monthStart = now()->startOfMonth();
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
                ?? (optional($worker->company)->name ?: (optional($worker->company)->short_name ?? '-')),
            'worker_name' => $worker->name ?? '-',
            'worker_job' => optional($worker->jobType)->name ?? '-',
            'worker_id' => $worker->national_id ?? '-',
            'worker_phone' => $worker->phone_number ?? '-',
            'access_code' => $worker->entity ?? "",
            'report_month' => $monthStart->format('F Y'),
        ]);

        $processor->cloneRowAndSetValues('row_serial', $weekdayRows);
        $processor->saveAs($outputPath);
        $this->addRedShadingToFridayCells($outputPath);
    }

    private function convertDocxToPdf(string $libreOfficePath, string $docxPath, string $outputDir): ?string
    {
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        if ($isWindows) {
            $command = sprintf(
                '%s --headless --convert-to pdf --outdir %s %s 2>&1',
                escapeshellarg($libreOfficePath),
                escapeshellarg($outputDir),
                escapeshellarg($docxPath)
            );
        } else {
            $profileDir = $outputDir . DIRECTORY_SEPARATOR . '.lo-profile';
            $homeDir = $outputDir . DIRECTORY_SEPARATOR . '.home';
            $cacheDir = $outputDir . DIRECTORY_SEPARATOR . '.cache';
            $configDir = $outputDir . DIRECTORY_SEPARATOR . '.config';

            @mkdir($profileDir, 0775, true);
            @mkdir($homeDir, 0775, true);
            @mkdir($cacheDir, 0775, true);
            @mkdir($configDir, 0775, true);

            $profileUri = 'file://' . str_replace(DIRECTORY_SEPARATOR, '/', $profileDir);

            $command = sprintf(
                'HOME=%s XDG_CACHE_HOME=%s XDG_CONFIG_HOME=%s SAL_USE_VCLPLUGIN=gen %s --headless -env:UserInstallation=%s --convert-to pdf --outdir %s %s 2>&1',
                escapeshellarg($homeDir),
                escapeshellarg($cacheDir),
                escapeshellarg($configDir),
                escapeshellarg($libreOfficePath),
                escapeshellarg($profileUri),
                escapeshellarg($outputDir),
                escapeshellarg($docxPath)
            );
        }

        exec($command, $output, $returnCode);

        $pdfPath = $outputDir . DIRECTORY_SEPARATOR . pathinfo($docxPath, PATHINFO_FILENAME) . '.pdf';

        if ($returnCode === 0 && file_exists($pdfPath) && filesize($pdfPath) > 100) {
            return $pdfPath;
        }

        \Log::warning('LibreOffice conversion failed', [
            'command' => $command,
            'return_code' => $returnCode,
            'output' => implode("\n", $output),
            'docx' => $docxPath,
            'expected_pdf' => $pdfPath,
        ]);

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
            $zip->close();

            if ($xml === false) {
                continue;
            }

            $dom = new \DOMDocument();
            $dom->loadXML($xml);
            $xpath = new \DOMXPath($dom);
            $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

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

            if ($this->isFridayRow($rowXml)) {
                $cells = $xpath->query('.//w:tc', $row);
                $cellCount = 0;

                foreach ($cells as $cell) {
                    if ($cellCount >= 2) {
                        break;
                    }

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
                    $shd->setAttribute('w:fill', 'FF0000');
                    $tcPr->appendChild($shd);

                    $cellCount++;
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

    public function preview(Worker $worker)
    {
        $worker->load(['company', 'jobType']);
        $project = Project::latest('id')->with('company')->first();

        return view('back.workers.export', [
            'worker' => $worker,
            'project' => $project,
        ]);
    }
}
