<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $equipments = Equipment::with('company')->orderBy('created_at', 'desc')->paginate(30);
        return view('themes.blk.back.equipment.index', compact('equipments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = \App\Models\Company::orderBy('name')->get();
        return view('themes.blk.back.equipment.create', compact('companies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'project_name' => 'required|string|max:255',
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

        $equipment = Equipment::create($request->all());
        return redirect()->route('equipment.index')->with('success', 'Equipment created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Equipment $equipment)
    {
        $equipment->load('company');
        return view('themes.blk.back.equipment.show', compact('equipment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Equipment $equipment)
    {
        //
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
