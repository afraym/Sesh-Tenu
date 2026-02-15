<?php

namespace App\Http\Controllers;

use App\Models\Worker;
use Illuminate\Http\Request;

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
        return view('themes.blk.back.workers.create')->with('companies', \App\Models\Company::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:workers,email',
            'company_id' => 'required|exists:companies,id',
            'phone' => 'nullable|string|max:20',
        ]); 
        $worker = new Worker();
        $worker->name = $request->name;
        $worker->email = $request->email;
        $worker->company_id = $request->company_id;
        $worker->phone = $request->phone;
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
        return view('themes.blk.back.workers.edit', ['worker' => $worker, 'companies' => \App\Models\Company::all()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Worker $worker)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:workers,email,' . $worker->id,
            'company_id' => 'required|exists:companies,id',
            'phone' => 'nullable|string|max:20',
        ]); 
        $worker->name = $request->name;
        $worker->email = $request->email;
        $worker->company_id = $request->company_id;
        $worker->phone = $request->phone;
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
}
