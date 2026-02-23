<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\JobType;
use App\Models\Worker;
use App\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\TemplateProcessor;

class WorkerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('themes.blk.back.workers.index')->with('workers', Worker::orderBy('created_at', 'desc')->paginate(10));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('themes.blk.back.workers.create', [
            'companies' => Company::orderBy('name')->get(),
            'jobtypes' => JobType::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'entity' => 'nullable|string|max:255',
            'company_id' => 'required|exists:companies,id',
            'job_type_id' => 'nullable|exists:job_types,id',
            'national_id' => 'required|string|max:255|unique:workers,national_id',
            'phone_number' => 'required|string|max:20',
            'address' => 'nullable|string|max:255',
            'join_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:join_date',
            'salary' => 'nullable|numeric|min:0',
            'has_housing' => 'nullable|boolean',
            'is_local_community' => 'nullable|boolean',
            'is_on_company_payroll' => 'nullable|boolean',
        ]); 

        $worker = new Worker();
        $worker->name = $request->name;
        $worker->entity = $request->entity;
        $worker->company_id = $request->company_id;
        $worker->job_type_id = $request->job_type_id;
        $worker->national_id = $request->national_id;
        $worker->phone_number = $request->phone_number;
        $worker->address = $request->address;
        $worker->join_date = $request->join_date;
        $worker->end_date = $request->end_date;
        $worker->salary = $request->salary;
        $worker->has_housing = $request->boolean('has_housing');
        $worker->is_local_community = $request->boolean('is_local_community');
        $worker->is_on_company_payroll = $request->boolean('is_on_company_payroll', true);
        $worker->save();

        return redirect()->route('workers.index')->with('success', 'Worker created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Worker $worker)
    {
        return view('themes.blk.back.workers.show', compact('worker'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Worker $worker)
    {
        return view('themes.blk.back.workers.edit', [
            'worker' => $worker,
            'companies' => Company::orderBy('name')->get(),
            'jobtypes' => JobType::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Worker $worker)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company_id' => 'required|exists:companies,id',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'join_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:join_date',
            'salary' => 'nullable|numeric|min:0',
            'has_housing' => 'nullable|boolean',
            'is_local_community' => 'nullable|boolean',
            'is_on_company_payroll' => 'nullable|boolean',
        ]); 
        $worker->name = $request->name;
        $worker->entity = $request->entity;
        $worker->company_id = $request->company_id;
        $worker->job_type_id = $request->job_type_id;
        $worker->national_id = $request->national_id;
        $worker->phone_number = $request->phone_number;
        $worker->address = $request->address;
        $worker->join_date = $request->join_date;
        $worker->end_date = $request->end_date;
        $worker->salary = $request->salary;
        $worker->has_housing = $request->boolean('has_housing');
        $worker->is_local_community = $request->boolean('is_local_community');
        $worker->is_on_company_payroll = $request->boolean('is_on_company_payroll');
        $worker->save();
        return redirect()->route('workers.index')->with('success', 'Worker updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Worker $worker)
    {
        $worker->delete();
        return redirect()->route('workers.index')->with('success', 'Worker deleted successfully.'); 
    }

    public function exportPdf(Worker $worker)
    {
        $worker->load(['company', 'jobType']);
        $project = Project::latest('id')->with('company')->first();

        $pdf = Pdf::loadView('themes.blk.back.workers.export', [
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

        return $pdf->download('worker-' . $worker->id . '.pdf');
    }

    public function exportPdfMerged(Request $request)
    {
        if (! class_exists(Fpdi::class)) {
            abort(500, 'PDF merge requires setasign/fpdi. Install with: composer require setasign/fpdi');
        }

        $templatePath = storage_path('app/templates/worker-timesheet.docx');

        if (! file_exists($templatePath)) {
            abort(404, 'Word template not found. Add a template at storage/app/templates/worker-timesheet.docx with the expected placeholders.');
        }

        $templatePath = storage_path('app/templates/worker-timesheet.docx');

        if (! file_exists($templatePath)) {
            abort(404, 'Word template not found. Add a template at storage/app/templates/worker-timesheet.docx with the expected placeholders.');
        }

        $ids = collect(explode(',', (string) $request->query('ids')))
            ->filter(fn ($v) => trim($v) !== '')
            ->map(fn ($v) => (int) $v)
            ->values();

        $project = Project::latest('id')->with('company')->first();

        $workers = Worker::with(['company', 'jobType'])
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
        $pdfPaths = [];

        foreach ($workers as $worker) {
            $pdf = Pdf::loadView('themes.blk.back.workers.export', [
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
            'workers-merged.pdf',
            ['Content-Type' => 'application/pdf']
        )->deleteFileAfterSend(true);
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

        $processor = new TemplateProcessor($templatePath);

        $processor->setValues([
            'project_name_en' => optional($project)->name ?? '-',
            'company_name' => optional($worker->company)->name ?: (optional($worker->company)->short_name ?? '-'),
            'consortium_name' => optional(optional($project)->company)->name
                ?? (optional($worker->company)->name ?: (optional($worker->company)->short_name ?? '-')),
            'worker_name' => $worker->name ?? '-',
            'worker_job' => optional($worker->jobType)->name ?? '-',
            'worker_id' => $worker->national_id ?? '-',
            'worker_phone' => $worker->phone_number ?? '-',
            'access_code' => $worker->entity ?? $worker->id,
            'report_month' => $monthStart->format('F Y'),
        ]);

        $processor->cloneRowAndSetValues('row_serial', $weekdayRows);

        $fileName = 'worker-' . $worker->id . '.docx';
        $tempPath = 'temp/' . $fileName;

        Storage::makeDirectory('temp');
        $fullPath = Storage::path($tempPath);
        $processor->saveAs($fullPath);

        // Post-process to add red shading to Friday cells and remove markers
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

        $project = Project::latest('id')->with('company')->first();

        $workers = Worker::with(['company', 'jobType'])
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
            $weekendRows = [];

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

                if ($day->isFriday()) {
                    $weekendRows[] = [
                        'week_serial' => $base['serial'],
                        'week_date' => $base['date'],
                        'week_start' => $base['start'],
                        'week_end' => $base['end'],
                        'week_break' => $base['break'],
                        'week_hours' => $base['hours'],
                        'week_location' => $base['location'],
                        'week_note' => $base['note'],
                        'week_supervisor' => $base['supervisor'],
                        'week_engineer' => $base['engineer'],
                    ];
                } else {
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
            }

            $processor = new TemplateProcessor($templatePath);

            $processor->setValues([
                'project_name_en' => optional($project)->name ?? '-',
                'company_name' => optional($worker->company)->name ?: (optional($worker->company)->short_name ?? '-'),
                'consortium_name' => optional(optional($project)->company)->name
                    ?? (optional($worker->company)->name ?: (optional($worker->company)->short_name ?? '-')),
                'worker_name' => $worker->name ?? '-',
                'worker_job' => optional($worker->jobType)->name ?? '-',
                'worker_id' => $worker->national_id ?? '-',
                'worker_phone' => $worker->phone_number ?? '-',
                'access_code' => $worker->entity ?? $worker->id,
                'report_month' => $monthStart->format('F Y'),
            ]);

            $processor->cloneRowAndSetValues('row_serial', $weekdayRows);
            $processor->cloneRowAndSetValues('week_serial', $weekendRows);

            $fileName = 'worker-' . $worker->id . '-' . preg_replace('/[^a-zA-Z0-9]/', '', $worker->name) . '.docx';
            $docxPath = $tempDir . DIRECTORY_SEPARATOR . $fileName;

            $processor->saveAs($docxPath);
            
            // Add red shading to Friday cells
            $this->addRedShadingToFridayCells($docxPath);
            
            $docxPaths[] = $docxPath;
        }

        // Create ZIP archive
        $zipPath = $tempDir . DIRECTORY_SEPARATOR . 'workers-timesheets.zip';
        $zip = new \ZipArchive();

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Could not create ZIP archive.');
        }

        foreach ($docxPaths as $docxPath) {
            $zip->addFile($docxPath, basename($docxPath));
        }

        $zip->close();

        // Clean up individual files
        foreach ($docxPaths as $docxPath) {
            @unlink($docxPath);
        }

        return response()->download(
            $zipPath,
            'workers-timesheets.zip',
            ['Content-Type' => 'application/zip']
        )->deleteFileAfterSend(true);
    }

    public function exportWordPdf(Worker $worker)
    {
        if (! class_exists(\Dompdf\Dompdf::class)) {
            abort(500, 'PDF rendering requires dompdf. Install with: composer require dompdf/dompdf');
        }

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

        $processor = new TemplateProcessor($templatePath);

        $processor->setValues([
            'project_name_en' => optional($project)->name ?? '-',
            'company_name' => optional($worker->company)->name ?: (optional($worker->company)->short_name ?? '-'),
            'consortium_name' => optional(optional($project)->company)->name
                ?? (optional($worker->company)->name ?: (optional($worker->company)->short_name ?? '-')),
            'worker_name' => $worker->name ?? '-',
            'worker_job' => optional($worker->jobType)->name ?? '-',
            'worker_id' => $worker->national_id ?? '-',
            'worker_phone' => $worker->phone_number ?? '-',
            'access_code' => $worker->entity ?? $worker->id,
            'report_month' => $monthStart->format('F Y'),
        ]);

        $processor->cloneRowAndSetValues('row_serial', $weekdayRows);

        Storage::makeDirectory('temp');
        $docxPath = Storage::path('temp/worker-' . $worker->id . '.docx');
        $pdfPath = Storage::path('temp/worker-' . $worker->id . '.pdf');

        $processor->saveAs($docxPath);
        
        // Add red shading to Friday cells
        $this->addRedShadingToFridayCells($docxPath);

        Settings::setPdfRenderer(Settings::PDF_RENDERER_DOMPDF, base_path('vendor/dompdf/dompdf'));

        $phpWord = IOFactory::load($docxPath);
        $pdfWriter = IOFactory::createWriter($phpWord, 'PDF');
        $pdfWriter->save($pdfPath);

        @unlink($docxPath);

        return response()->download(
            $pdfPath,
            'worker-' . $worker->id . '.pdf',
            ['Content-Type' => 'application/pdf']
        )->deleteFileAfterSend(true);
    }

    public function exportWordPdfAll(Request $request)
    {
        $templatePath = storage_path('app/templates/worker-timesheet.docx');

        if (! file_exists($templatePath)) {
            abort(404, 'Word template not found. Add a template at storage/app/templates/worker-timesheet.docx with the expected placeholders.');
        }

        if (! class_exists(Fpdi::class)) {
            abort(500, 'PDF merge requires setasign/fpdi. Install with: composer require setasign/fpdi');
        }

        $ids = collect(explode(',', (string) $request->query('ids')))
            ->filter(fn ($v) => trim($v) !== '')
            ->map(fn ($v) => (int) $v)
            ->values();

        $project = Project::latest('id')->with('company')->first();

        $workers = Worker::with(['company', 'jobType'])
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

        // Create persistent export folder with timestamp
        $timestamp = now()->format('Y-m-d_His');
        $exportFolder = "workers-export/{$timestamp}";
        Storage::makeDirectory($exportFolder);
        $exportPath = Storage::path($exportFolder);

        // Generate DOCX files
        $docxPaths = [];
        foreach ($workers as $worker) {
            $monthStart = now()->startOfMonth();
            $daysInMonth = $monthStart->daysInMonth;

            $weekdayRows = [];
            $weekendRows = [];

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

                if ($day->isFriday()) {
                    $weekendRows[] = [
                        'week_serial' => $base['serial'],
                        'week_date' => $base['date'],
                        'week_start' => $base['start'],
                        'week_end' => $base['end'],
                        'week_break' => $base['break'],
                        'week_hours' => $base['hours'],
                        'week_location' => $base['location'],
                        'week_note' => $base['note'],
                        'week_supervisor' => $base['supervisor'],
                        'week_engineer' => $base['engineer'],
                    ];
                } else {
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
            }

            $processor = new TemplateProcessor($templatePath);

            $processor->setValues([
                'project_name_en' => optional($project)->name ?? '-',
                'company_name' => optional($worker->company)->name ?: (optional($worker->company)->short_name ?? '-'),
                'consortium_name' => optional(optional($project)->company)->name
                    ?? (optional($worker->company)->name ?: (optional($worker->company)->short_name ?? '-')),
                'worker_name' => $worker->name ?? '-',
                'worker_job' => optional($worker->jobType)->name ?? '-',
                'worker_id' => $worker->national_id ?? '-',
                'worker_phone' => $worker->phone_number ?? '-',
                'access_code' => $worker->entity ?? $worker->id,
                'report_month' => $monthStart->format('F Y'),
            ]);

            $processor->cloneRowAndSetValues('row_serial', $weekdayRows);
            $processor->cloneRowAndSetValues('week_serial', $weekendRows);

            $fileName = 'worker-' . $worker->id . '.docx';
            $docxPath = $exportPath . DIRECTORY_SEPARATOR . $fileName;
            $processor->saveAs($docxPath);
            
            // Add red shading to Friday cells
            $this->addRedShadingToFridayCells($docxPath);
            
            $docxPaths[] = $docxPath;
        }

        // Try to convert DOCX to PDF using LibreOffice
        $pdfPaths = [];
        $libreOfficePath = $this->findLibreOffice();

        if ($libreOfficePath) {
            foreach ($docxPaths as $docxPath) {
                // Use escapeshellarg for better cross-platform compatibility
                $command = sprintf(
                    '%s --headless --convert-to pdf --outdir %s %s 2>&1',
                    escapeshellarg($libreOfficePath),
                    escapeshellarg($exportPath),
                    escapeshellarg($docxPath)
                );

                exec($command, $output, $returnCode);

                $pdfPath = str_replace('.docx', '.pdf', $docxPath);
                if (file_exists($pdfPath) && filesize($pdfPath) > 100) {
                    $pdfPaths[] = $pdfPath;
                    \Log::info("LibreOffice conversion successful for: {$docxPath}");
                } else {
                    \Log::warning("LibreOffice conversion failed for: {$docxPath}. Return code: {$returnCode}. Output: " . implode("\n", $output));
                }
                
                // Clear output array for next iteration
                $output = [];
            }
        }

        if (empty($pdfPaths)) {
            // LibreOffice not available or conversion failed
            $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
            
            $message = "DOCX files saved to: storage/app/{$exportFolder}\n\n";
            $message .= "LibreOffice not found or conversion failed.\n\n";
            $message .= "To enable automatic PDF conversion:\n\n";
            
            if ($isWindows) {
                $message .= "Windows Installation:\n";
                $message .= "1. Install LibreOffice from https://www.libreoffice.org/\n";
                $message .= "2. Add it to your PATH, or ensure soffice.exe is in one of these locations:\n";
                $message .= "   - C:\\Program Files\\LibreOffice\\program\\soffice.exe\n";
                $message .= "   - C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe\n";
            } else {
                $message .= "Ubuntu/Linux Installation:\n";
                $message .= "Run the following commands:\n";
                $message .= "   sudo apt update\n";
                $message .= "   sudo apt install libreoffice --no-install-recommends\n";
                $message .= "   # Or via snap: sudo snap install libreoffice\n\n";
                $message .= "Verify installation:\n";
                $message .= "   which soffice\n";
                $message .= "   soffice --version\n";
            }
            
            $message .= "\n\nAlternatively, use the 'PDF All' button for HTML-based PDF export.";

            abort(500, $message);
        }

        // Merge PDFs
        $fpdi = new Fpdi();

        foreach ($pdfPaths as $path) {
            try {
                $pageCount = $fpdi->setSourceFile($path);
                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $tplId = $fpdi->importPage($pageNo);
                    $size = $fpdi->getTemplateSize($tplId);
                    $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
                    $fpdi->AddPage($orientation, [$size['width'], $size['height']]);
                    $fpdi->useTemplate($tplId);
                }
            } catch (\Exception $e) {
                \Log::error("FPDI merge failed for {$path}: " . $e->getMessage());
            }
        }

        $mergedPath = $exportPath . DIRECTORY_SEPARATOR . 'workers-merged.pdf';
        $fpdi->Output($mergedPath, 'F');

        return response()->download(
            $mergedPath,
            'workers-merged-' . $timestamp . '.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Add red shading to Friday row cells (serial and date only)
     */
    private function addRedShadingToFridayCells($filePath)
    {
        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            return; // Silently fail if we can't modify
        }

        $xml = $zip->getFromName('word/document.xml');
        if ($xml === false) {
            $zip->close();
            return;
        }

        $dom = new \DOMDocument();
        $dom->loadXML($xml);
        
        // Register namespace for Word XML
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        
        // Find all table rows
        $rows = $xpath->query('//w:tr');
        
        foreach ($rows as $row) {
            $rowXml = $dom->saveXML($row);
            
            // Check if this row contains a Friday date
            if ($this->isFridayRow($rowXml)) {
                // Get only the first two cells (serial and date)
                $cells = $xpath->query('.//w:tc', $row);
                $cellCount = 0;
                
                foreach ($cells as $cell) {
                    if ($cellCount >= 2) break; // Only process first 2 cells
                    
                    // Get or create tcPr element
                    $tcPrList = $xpath->query('.//w:tcPr', $cell);
                    
                    if ($tcPrList->length > 0) {
                        $tcPr = $tcPrList->item(0);
                    } else {
                        $tcPr = $dom->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:tcPr');
                        $cell->insertBefore($tcPr, $cell->firstChild);
                    }
                    
                    // Remove existing shading if present
                    $existingShd = $xpath->query('.//w:shd', $tcPr);
                    foreach ($existingShd as $shd) {
                        $tcPr->removeChild($shd);
                    }
                    
                    // Add red shading
                    $shd = $dom->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:shd');
                    $shd->setAttribute('w:val', 'clear');
                    $shd->setAttribute('w:color', 'auto');
                    $shd->setAttribute('w:fill', 'FF0000'); // Red color
                    $tcPr->appendChild($shd);
                    
                    $cellCount++;
                }
            }
        }

        $zip->deleteName('word/document.xml');
        $zip->addFromString('word/document.xml', $dom->saveXML());
        $zip->close();
    }

    /**
     * Check if the row XML contains a Friday date
     */
    private function isFridayRow($rowXml)
    {
        // Extract date from XML (assumes format j/n/Y or d/m/Y)
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

    /**
     * Find LibreOffice executable path
     */
    private function findLibreOffice()
    {
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        
        // Try to find in PATH first (works on all systems)
        if ($isWindows) {
            exec('where soffice 2>nul', $output, $returnCode);
        } else {
            exec('which soffice 2>/dev/null', $output, $returnCode);
            if ($returnCode !== 0 || empty($output[0])) {
                exec('which libreoffice 2>/dev/null', $output, $returnCode);
            }
        }
        
        if ($returnCode === 0 && !empty($output[0])) {
            return trim($output[0]);
        }

        // Check common paths based on OS
        if ($isWindows) {
            $paths = [
                'C:\\Program Files\\LibreOffice\\program\\soffice.exe',
                'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe',
                'C:\\LibreOffice\\program\\soffice.exe',
            ];
        } else {
            // Linux/Unix paths (including Ubuntu)
            $paths = [
                '/usr/bin/soffice',
                '/usr/bin/libreoffice',
                '/usr/local/bin/soffice',
                '/usr/local/bin/libreoffice',
                '/snap/bin/libreoffice',
                '/opt/libreoffice/program/soffice',
                '/Applications/LibreOffice.app/Contents/MacOS/soffice', // macOS
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

        return view('themes.blk.back.workers.export', [
            'worker' => $worker,
            'project' => $project,
        ]);
    }
}
