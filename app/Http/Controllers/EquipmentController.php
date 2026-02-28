<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Project;
use Illuminate\Http\Request;

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
}
