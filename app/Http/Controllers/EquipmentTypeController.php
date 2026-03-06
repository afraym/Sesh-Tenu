<?php

namespace App\Http\Controllers;

use App\Models\EquipmentType;
use Illuminate\Http\Request;

class EquipmentTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('back.equipment-types.index')->with('equipmentTypes', EquipmentType::orderBy('created_at', 'desc')->paginate(100));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('back.equipment-types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:equipment_types,name',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string|max:1000',
        ]);

        EquipmentType::create([
            'name' => $request->name,
            'is_active' => $request->is_active ?? 0,
            'description' => $request->description ?? null,
        ]);

        return redirect()->route('equipment-types.index')->with('success', 'Equipment type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(EquipmentType $equipment_type)
    {
        $equipmentType = $equipment_type;
        return view('back.equipment-types.show')->with('equipmentType', $equipmentType);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EquipmentType $equipment_type)
    {
        $equipmentType = $equipment_type;
        return view('back.equipment-types.edit')->with('equipmentType', $equipmentType);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EquipmentType $equipment_type)
    {
        $equipmentType = $equipment_type;

        $request->validate([
            'name' => 'required|string|max:255|unique:equipment_types,name,' . $equipmentType->id,
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string|max:1000',
        ]);

        $equipmentType->update([
            'name' => $request->name,
            'is_active' => $request->is_active ?? 0,
            'description' => $request->description ?? null,
        ]);

        return redirect()->route('equipment-types.index')->with('success', 'Equipment type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EquipmentType $equipment_type)
    {
        $equipmentType = $equipment_type;
        $equipmentType->delete();

        return redirect()->route('equipment-types.index')->with('success', 'Equipment type deleted successfully.');
    }
}
