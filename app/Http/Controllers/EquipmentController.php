<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Carbon\Carbon;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Http\Request;
use App\Models\Project;

class EquipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $equipments = Equipment::with('company')->orderBy('created_at', 'desc')->paginate(30);
        return view('back.equipment.index', compact('equipments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = \App\Models\Company::orderBy('name')->get();
        $projects = Project::orderBy('name')->get(['id', 'name']);
        return view('back.equipment.create', compact('companies', 'projects'));
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
            'equipment_type' => 'required|string|max:255',
            'model_year' => 'nullable|string|max:255',
            'equipment_code' => 'required|string|max:255|unique:equipment,equipment_code',
            'equipment_number' => 'nullable|string|max:255',
            'manufacture' => 'nullable|string|max:255',
            'entry_per_ser' => 'nullable|string|max:255',
            'reg_no' => 'nullable|string|max:255',
            'equip_reg_issue' => 'nullable|string|max:255',
            'custom_clearance' => 'nullable|string|max:255',
        ]);

        $project = Project::findOrFail($request->project_id);

        Equipment::create([
            'project_name' => $project->name,
            'company_id' => $request->company_id,
            'previous_driver' => $request->previous_driver,
            'current_driver' => $request->current_driver,
            'equipment_type' => $request->equipment_type,
            'model_year' => $request->model_year,
            'equipment_code' => $request->equipment_code,
            'equipment_number' => $request->equipment_number,
            'manufacture' => $request->manufacture,
            'entry_per_ser' => $request->entry_per_ser,
            'reg_no' => $request->reg_no,
            'equip_reg_issue' => $request->equip_reg_issue,
            'custom_clearance' => $request->custom_clearance,
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
        $templatePath = storage_path('app/templates/tipper-truck-daily-temp.docx');
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
            'daily'              => 'يومي',
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

        // hide date + daily if out of month
        $out["{$day}_date"] = $inMonth ? $date->format('d/m') : '';
        $out["{$day}_daily"] = $inMonth ? 'يومي' : '';

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
        $templatePath = storage_path('app/templates/tipper-truck-daily-temp.docx');
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

}
