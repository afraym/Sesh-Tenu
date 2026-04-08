<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Carbon\Carbon;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Company;
use App\Models\EquipmentType;
use App\Models\Worker;

class EquipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Equipment::query()->with('company');
        $allowedSorts = [
            'id',
            'project_name',
            'company_id',
            'equipment_type',
            'model_year',
            'equipment_code',
            'equipment_number',
            'current_driver',
            'manufacture',
            'entry_per_ser',
            'created_at',
        ];
        if ($user && !$user->isSuperAdmin()) {
            $query->where('company_id', $user->company_id);
        } else {
            if ($request->filled('company_id')) {
                $query->where('company_id', $request->company_id);
            }
        }

        $sort = $request->input('sort', 'created_at');
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        $direction = strtolower((string) $request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $equipments = $query
            ->orderBy($sort, $direction)
            ->paginate(100)
            ->withQueryString();

        return view('back.equipment.index', compact('equipments', 'sort', 'direction'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = \App\Models\Company::orderBy('name')->get();
        $projects = Project::orderBy('name')->get(['id', 'name']);
        $equipmentTypes = EquipmentType::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $drivers = \App\Models\User::orderBy('name')->get(['id', 'name', 'company_id']);
        $workerDrivers = Worker::query()
            ->whereHas('jobType', function ($query) {
                $query->where('name', 'like', '%سائق%');
            })
            ->with(['jobType:id,name'])
            ->orderBy('name')
            ->get(['id', 'name', 'job_type_id']);

        return view('back.equipment.create', compact('companies', 'projects', 'equipmentTypes', 'drivers', 'workerDrivers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'company_id' => 'required|exists:companies,id',
            'previous_driver' => 'nullable|string|max:255',
            'current_driver' => 'nullable|string|max:255',
            'equipment_type' => 'required|string|max:255|exists:equipment_types,name',
            'model_year' => 'nullable|string|max:255',
            'equipment_code' => 'required|string|max:255|unique:equipment,equipment_code',
            'equipment_number' => 'nullable|string|max:255',
            'manufacture' => 'nullable|string|max:255',
            'entry_per_ser' => 'nullable|string|max:255',
            'reg_no' => 'nullable|string|max:255',
            'equip_reg_issue' => 'nullable|string|max:255',
            'custom_clearance' => 'nullable|string|max:255',
            'equipment_option' => 'required|in:فعلي,اختياري',
            'driver_user_id' => 'nullable|exists:users,id',
            'driver_worker_id' => 'nullable|exists:workers,id',
        ]);

        $project = Project::findOrFail($request->project_id);

        // If a linked user driver is selected, use their name as current_driver
        $currentDriver = $request->current_driver;
        if ($request->filled('driver_worker_id')) {
            $workerDriver = Worker::find($request->driver_worker_id);
            if ($workerDriver) {
                $currentDriver = $workerDriver->name;
            }
        }

        if ($request->filled('driver_user_id')) {
            $driverUser = \App\Models\User::find($request->driver_user_id);
            if ($driverUser) {
                $currentDriver = $driverUser->name;
            }
        }

        Equipment::create([
            'project_name' => $project->name,
            'company_id' => $request->company_id,
            'previous_driver' => $request->previous_driver,
            'current_driver' => $currentDriver,
            'driver_user_id' => $request->driver_user_id,
            'driver_worker_id' => $request->driver_worker_id,
            'equipment_type' => $request->equipment_type,
            'model_year' => $request->model_year,
            'equipment_code' => $request->equipment_code,
            'equipment_number' => $request->equipment_number,
            'manufacture' => $request->manufacture,
            'entry_per_ser' => $request->entry_per_ser,
            'reg_no' => $request->reg_no,
            'equip_reg_issue' => $request->equip_reg_issue,
            'custom_clearance' => $request->custom_clearance,
            'equipment_option' => $request->equipment_option,
        ]);

        return redirect()->route('equipment.index')->with('success', 'تم إضافة المُعدة بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Equipment $equipment)
    {
        $equipment->load('company');
        return view('back.equipment.show', compact('equipment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Equipment $equipment)
    {
        $companies = \App\Models\Company::orderBy('name')->get();
        $projects = Project::orderBy('name')->get(['id', 'name']);

        return view('back.equipments.edit', compact('equipment', 'companies', 'projects'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Equipment $equipment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Equipment $equipment)
    {
        //
    }

    public function exportWord(Equipment $equipment, $year = null, $month = null)
    {
        $equipment->load('company');

           $year = $year ?: now()->year;
        $month = $month ?: now()->month;
        $firstDay = Carbon::create($year, $month, 1)->startOfDay();
        $lastDay = $firstDay->copy()->endOfMonth();
        $templatePath = $this->resolveDailyInspectionTemplatePath($equipment);
        if (!is_file($templatePath)) {
            abort(404, 'Equipment Word template not found.');
        }

        $processor = new TemplateProcessor($templatePath);

        $weekStart = now()->startOfMonth();
        $weekStart = $firstDay->copy()->startOfWeek(Carbon::SATURDAY);

        $values = [
            'report_month'       => $weekStart->format('F Y'),
            'company_short_name' => $equipment->company->short_name ?? '',
            'equip_reg_issue'    => $equipment->equip_reg_issue ?? '',
            'driver'             => $equipment->current_driver ?? '',
            'equipment_code'     => $equipment->equipment_code ?? '',
            'equipment_number'   => $equipment->equipment_number ?? '',
            'equipment_model'    => trim(implode(' ', array_filter([
                $equipment->manufacture ?? null,
                $equipment->model_year ?? null,
            ]))) ?: '',
            'daily'              => '',
        ];

        $values += $this->buildEquipmentWeekValues($weekStart,$month);

        $processor->setValues($values);

        $fileName = 'equipment-' . $equipment->id . '-' . now()->format('Ymd_His') . '.docx';
        $outPath = storage_path('app/temp/' . $fileName);

        if (!is_dir(dirname($outPath))) {
            mkdir(dirname($outPath), 0775, true);
        }

        $processor->saveAs($outPath);

        $this->applyGreyShadingToMarkedCells($outPath);

        return response()->download($outPath, $fileName)->deleteFileAfterSend(true);
    }

private function buildEquipmentWeekValues(Carbon $weekStart, int $month): array
{
    $days = ['sat', 'sun', 'mon', 'tue', 'wed', 'thu', 'fri'];
    $out = [];

    foreach ($days as $i => $day) {
        $date = $weekStart->copy()->addDays($i);
        $inMonth = ((int) $date->month === (int) $month);

        // keep date cell visually empty but shaded for out-of-month days
        $out["{$day}_date"] = $inMonth ? $date->format('d/m') : '[[GREY]]';
        // keep daily cell visually empty but shaded for out-of-month days
        $out["{$day}_daily"] = $inMonth ? 'يومي' : '[[GREY]]';

        // keep grey marker for out-of-month cells
        $out["{$day}_check_column"] = $inMonth ? '' : '[[GREY]]';
    }

    return $out;
}

    /**
     * Replace [[GREY]] markers with real Word cell background shading.
     */
    private function applyGreyShadingToMarkedCells(string $docxPath): void
    {
        $zip = new \ZipArchive();
        if ($zip->open($docxPath) !== true) {
            return;
        }

        $xml = $zip->getFromName('word/document.xml');
        if ($xml === false) {
            $zip->close();
            return;
        }

        $xml = preg_replace_callback('/<w:tc\b[\s\S]*?<\/w:tc>/', function ($m) {
            $tc = $m[0];

            if (strpos($tc, '[[GREY]]') === false) {
                return $tc;
            }

            // Remove marker text
            $tc = str_replace('[[GREY]]', '', $tc);

            // Add/replace shading
            if (preg_match('/<w:tcPr\b[\s\S]*?<\/w:tcPr>/', $tc)) {
                if (preg_match('/<w:shd\b[^>]*\/>/', $tc)) {
                    $tc = preg_replace(
                        '/<w:shd\b[^>]*\/>/',
                        '<w:shd w:val="clear" w:color="auto" w:fill="D9D9D9"/>',
                        $tc,
                        1
                    );
                } else {
                    $tc = preg_replace(
                        '/<\/w:tcPr>/',
                        '<w:shd w:val="clear" w:color="auto" w:fill="D9D9D9"/></w:tcPr>',
                        $tc,
                        1
                    );
                }
            } else {
                $tc = preg_replace(
                    '/<w:tc>/',
                    '<w:tc><w:tcPr><w:shd w:val="clear" w:color="auto" w:fill="D9D9D9"/></w:tcPr>',
                    $tc,
                    1
                );
            }

            return $tc;
        }, $xml);

        $zip->addFromString('word/document.xml', $xml);
        $zip->close();
    }

    public function exportMonthWord(Equipment $equipment, $year = null, $month = null)
    {
        $equipment->load('company');
        $templatePath = $this->resolveDailyInspectionTemplatePath($equipment);
        if (!is_file($templatePath)) {
            abort(404, 'Template not found.');
        }

        $year = $year ?: now()->year;
        $month = $month ?: now()->month;
        $firstDay = Carbon::create($year, $month, 1)->startOfDay();
        $lastDay = $firstDay->copy()->endOfMonth();

        // Find the first Saturday on or before the 1st of the month
        $firstWeekStart = $firstDay->copy()->subDays(($firstDay->dayOfWeek + 1) % 7);

        $weeks = [];
        $current = $firstWeekStart->copy();
        while ($current->lte($lastDay)) {
            $weeks[] = $current->copy();
            $current->addWeek();
        }

        $files = [];
        foreach ($weeks as $weekStart) {
            $processor = new TemplateProcessor($templatePath);

            $values = [
                // Add other placeholders as needed
                'company_short_name' => $equipment->company->short_name ?? '',
                'equip_reg_issue'    => $equipment->equip_reg_issue ?? '',
                'driver'             => $equipment->current_driver ?? '',
                'equipment_code'     => $equipment->equipment_code ?? '',
                'equipment_number'   => $equipment->equipment_number ?? '',
                'equipment_model'    => trim(implode(' ', array_filter([
                    $equipment->manufacture ?? null,
                    $equipment->model_year ?? null,
                ]))) ?: '',
                'report_month'       => $firstDay->format('F Y'),
            ];
            $values += $this->buildEquipmentWeekValues($weekStart, $month);

            $processor->setValues($values);

            $fileName = 'equipment-' . $equipment->id . '-' . $weekStart->format('Ymd') . '.docx';
            $outPath = storage_path('app/temp/' . $fileName);

            if (!is_dir(dirname($outPath))) {
                mkdir(dirname($outPath), 0775, true);
            }

            $processor->saveAs($outPath);
            $this->applyGreyShadingToMarkedCells($outPath);
            $files[] = $outPath;
        }

        // Optionally: zip all files for download
        $zipPath = storage_path('app/temp/equipment-weeks-' . now()->format('Ymd_His') . '.zip');
        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        foreach ($files as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close();

        // Clean up temp files after zipping
        foreach ($files as $file) {
            @unlink($file);
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    public function exportWordSelected(Request $request, $year = null, $month = null)
    {
        $ids = collect(explode(',', (string) $request->query('ids')))
            ->filter(fn ($value) => trim($value) !== '')
            ->map(fn ($value) => (int) $value)
            ->values();

        if ($ids->isEmpty()) {
            abort(404, 'No equipment selected.');
        }

        $equipments = Equipment::with('company')
            ->whereIn('id', $ids)
            ->orderByRaw('FIELD(id,' . $ids->implode(',') . ')')
            ->get();

        if ($equipments->isEmpty()) {
            abort(404, 'No equipment selected.');
        }

        $selectedMonth = trim((string) $request->query('month', ''));
        if (preg_match('/^(\d{4})-(\d{2})$/', $selectedMonth, $matches) === 1) {
            $year = (int) $matches[1];
            $month = (int) $matches[2];
        } else {
            $year = $year ?: now()->year;
            $month = $month ?: now()->month;
        }

        $firstDay = Carbon::create($year, $month, 1)->startOfDay();
        $lastDay = $firstDay->copy()->endOfMonth();
        $firstWeekStart = $firstDay->copy()->startOfWeek(Carbon::SATURDAY);

        $weeks = [];
        $current = $firstWeekStart->copy();
        while ($current->lte($lastDay)) {
            $weeks[] = $current->copy();
            $current->addWeek();
        }

        $timestamp = now()->format('Ymd_His');
        $tempDir = storage_path('app/temp/equipment-selected-' . $timestamp);

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $docxPaths = [];

        foreach ($equipments as $equipment) {
            $templatePath = $this->resolveDailyInspectionTemplatePath($equipment);
            if (!is_file($templatePath)) {
                abort(404, 'Equipment Word template not found.');
            }

            foreach ($weeks as $weekStart) {
                $processor = new TemplateProcessor($templatePath);

                $values = [
                    'report_month'       => $weekStart->format('F Y'),
                    'company_short_name' => $equipment->company->short_name ?? '',
                    'equip_reg_issue'    => $equipment->equip_reg_issue ?? '',
                    'driver'             => $equipment->current_driver ?? '',
                    'equipment_code'     => $equipment->equipment_code ?? '',
                    'equipment_number'   => $equipment->equipment_number ?? '',
                    'equipment_model'    => trim(implode(' ', array_filter([
                        $equipment->manufacture ?? null,
                        $equipment->model_year ?? null,
                    ]))) ?: '',
                    'daily'              => '',
                ];

                $values += $this->buildEquipmentWeekValues($weekStart, $month);
                $processor->setValues($values);

                $fileName = 'equipment-' . $equipment->id . '-' . $weekStart->format('Ymd') . '.docx';
                $outPath = $tempDir . DIRECTORY_SEPARATOR . $fileName;

                $processor->saveAs($outPath);
                $this->applyGreyShadingToMarkedCells($outPath);
                $docxPaths[] = $outPath;
            }
        }

        $combinedDocxPath = storage_path('app/temp/equipment-daily-selected-' . $timestamp . '.docx');
        $this->mergeDocxFiles($docxPaths, $combinedDocxPath);

        foreach ($docxPaths as $docxPath) {
            @unlink($docxPath);
        }
        @rmdir($tempDir);

        return response()->download(
            $combinedDocxPath,
            'equipment-daily-selected-' . $timestamp . '.docx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
        )->deleteFileAfterSend(true);
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

    private function resolveDailyInspectionTemplatePath(Equipment $equipment): string
    {
        $defaultTemplatePath = storage_path('app/templates/Qalab-daily-inspection.docx');
        $harasTemplatePath = storage_path('app/templates/haras-daily-inspection.docx');
        $qalabTemplatePath = storage_path('app/templates/Qalab-daily-inspection.docx');

        $type = trim((string) ($equipment->equipment_type ?? ''));
        if ($type !== '' && mb_stripos($type, 'قلاب') !== false && is_file($qalabTemplatePath)) {
            return $qalabTemplatePath;
        }

        if ($type !== '' && mb_stripos($type, 'هراس') !== false && is_file($harasTemplatePath)) {
            return $harasTemplatePath;
        }

        return $defaultTemplatePath;
    }

}
