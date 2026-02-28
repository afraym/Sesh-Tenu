<?php

namespace App\Http\Controllers;

use App\Models\Jobtype;
use Illuminate\Http\Request;

class JobTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('back.jobtypes.index')->with('jobtypes', Jobtype::orderBy('created_at', 'desc')->paginate(10));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('back.jobtypes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string|max:1000',
        ]);
        $jobtype = new Jobtype();
        $jobtype->name = $request->name;
        $jobtype->is_active = $request->is_active ?? 0;
        $jobtype->description = $request->description ?? null;
        $jobtype->save();
        return redirect()->route('jobtypes.index')->with('success', 'Job type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Jobtype $jobtype)
    {
        return view('back.jobtypes.show')->with('jobtype', $jobtype);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Jobtype $jobtype)
    {
        return view('back.jobtypes.edit')->with('jobtype', $jobtype);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Jobtype $jobtype)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $jobtype->name = $request->name;
        $jobtype->is_active = $request->is_active ?? 0;
        $jobtype->description = $request->description ?? null;
        $jobtype->save();
        return redirect()->route('jobtypes.index')->with('success', 'Job type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Jobtype $jobtype   )
    {
        $jobtype->delete();
        return redirect()->route('jobtypes.index')->with('success', 'Job type deleted successfully.');
    }
}
