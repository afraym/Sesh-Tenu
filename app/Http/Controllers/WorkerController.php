<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\JobType;
use Illuminate\Http\Request;
use App\Models\Worker;

class WorkerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Worker::query()->with(['company', 'jobType', 'equipmentAsDriver']);
        $companies = collect();
        $selectedCompanyId = null;

        $allowedSorts = [
            'id',
            'name',
            'job_type_id',
            'national_id',
            'phone_number',
            'join_date',
            'is_on_company_payroll',
            'created_at',
        ];

        if ($user && !$user->isSuperAdmin()) {
            $selectedCompanyId = $user->company_id;
            $query->where('company_id', $user->company_id);
        } else {
            $companies = Company::orderBy('name')->get(['id', 'name']);
            if ($request->filled('company_id')) {
                $selectedCompanyId = (int) $request->company_id;
                $query->where('company_id', $request->company_id);
            }
        }

        $jobTypeFilter = (string) $request->input('job_type_id', '');
        if ($jobTypeFilter === 'equipment_operator') {
            $query->whereHas('jobType', function ($q) {
                $q->where('name', 'like', '%سائق%');
            });
        } elseif ($request->filled('job_type_id')) {
            $query->where('job_type_id', $request->job_type_id);
        }

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('national_id', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");

                if (is_numeric($search)) {
                    $q->orWhere('id', (int) $search);
                }
            });
        }

        $sort = $request->input('sort', 'created_at');
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        $direction = strtolower((string) $request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $workers = $query->orderBy($sort, $direction)->paginate(300)->withQueryString();
        $jobTypes = JobType::orderBy('name')->get(['id', 'name']);

        return view('back.workers.index', compact('workers', 'jobTypes', 'sort', 'direction', 'companies', 'selectedCompanyId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();

        return view('back.workers.create', [
            'companies' => $user && !$user->isSuperAdmin()
                ? Company::where('id', $user->company_id)->orderBy('name')->get()
                : Company::orderBy('name')->get(),
            'jobtypes' => JobType::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($request->user() && !$request->user()->isSuperAdmin()) {
            $request->merge(['company_id' => $request->user()->company_id]);
        }

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
            'has_training_course' => 'nullable|boolean',
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
        $worker->has_training_course = $request->boolean('has_training_course');
        $worker->is_local_community = $request->boolean('is_local_community');
        $worker->is_on_company_payroll = $request->boolean('is_on_company_payroll', true);
        $worker->save();

        return redirect()->route('workers.index')->with('success', 'تم إضافة العامل بنجاح.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Worker $worker)
    {
        $this->ensureWorkerVisibleForUser($worker);

        return view('back.workers.show', compact('worker'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Worker $worker)
    {
        $this->ensureWorkerVisibleForUser($worker);

        $user = auth()->user();

        return view('back.workers.edit', [
            'worker' => $worker,
            'companies' => $user && !$user->isSuperAdmin()
                ? Company::where('id', $user->company_id)->orderBy('name')->get()
                : Company::orderBy('name')->get(),
            'jobtypes' => JobType::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Worker $worker)
    {
        $this->ensureWorkerVisibleForUser($worker);

        if ($request->user() && !$request->user()->isSuperAdmin()) {
            $request->merge(['company_id' => $request->user()->company_id]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'company_id' => 'required|exists:companies,id',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'join_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:join_date',
            'salary' => 'nullable|numeric|min:0',
            'has_housing' => 'nullable|boolean',
            'has_training_course' => 'nullable|boolean',
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
        $worker->has_training_course = $request->boolean('has_training_course');
        $worker->is_local_community = $request->boolean('is_local_community');
        $worker->is_on_company_payroll = $request->boolean('is_on_company_payroll');
        $worker->save();
        return redirect()->route('workers.index')->with('success', 'تم تحديث بيانات العامل بنجاح.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Worker $worker)
    {
        $this->ensureWorkerVisibleForUser($worker);

        $worker->delete();
        return redirect()->route('workers.index')->with('success', 'تم حذف العامل بنجاح.'); 
    }

    private function ensureWorkerVisibleForUser(Worker $worker): void
    {
        $user = auth()->user();

        if ($user && !$user->isSuperAdmin() && (int) $worker->company_id !== (int) $user->company_id) {
            abort(403, 'ليس لديك صلاحية الوصول إلى هذا العامل.');
        }
    }
}
