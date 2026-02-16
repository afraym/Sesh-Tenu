<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\JobType;
use App\Models\Worker;
use Barryvdh\DomPDF\Facade\Pdf;
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

        $pdf = Pdf::loadView('themes.blk.back.workers.export', [
            'worker' => $worker,
        ])->setPaper('a4', 'portrait')
          ->setOptions([
              'defaultFont' => 'ArialCustom',
              'isHtml5ParserEnabled' => true,
              'isRemoteEnabled' => true,
              'chroot' => public_path(),
          ]);

        return $pdf->download('worker-' . $worker->id . '.pdf');
    }

    public function exportWord(Worker $worker)
    {
        $worker->load(['company', 'jobType']);

        $content = view('themes.blk.back.workers.export', [
            'worker' => $worker,
        ])->render();

        return response($content)
            ->header('Content-Type', 'application/msword; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="worker-' . $worker->id . '.doc"');
    }
}
