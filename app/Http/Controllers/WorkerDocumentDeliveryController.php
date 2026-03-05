<?php

namespace App\Http\Controllers;

use App\Models\WorkerDocumentDelivery;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkerDocumentDeliveryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Check if user can access worker's company data
     */
    private function canAccessWorker(Worker $worker): bool
    {
        return Auth::user()->isSuperAdmin() || $worker->company_id === Auth::user()->company_id;
    }

    /**
     * Check if user can access delivery
     */
    private function canAccessDelivery(WorkerDocumentDelivery $delivery): bool
    {
        return Auth::user()->isSuperAdmin() || $delivery->worker->company_id === Auth::user()->company_id;
    }

    /**
     * Quick bulk entry view
     */
    public function quickEntry(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc');

        $query = Worker::where('is_on_company_payroll', 1);
        
        // Limit to user's company if not super admin
        if (!Auth::user()->isSuperAdmin()) {
            $query->where('company_id', Auth::user()->company_id);
        }

        // Apply sorting
        if (in_array($sort, ['morning_delivery_date', 'evening_delivery_date'])) {
            // Join with deliveries for date sorting
            $query->leftJoin('worker_document_deliveries', function ($join) use ($year, $month) {
                $join->on('workers.id', '=', 'worker_document_deliveries.worker_id')
                    ->where('worker_document_deliveries.year', $year)
                    ->where('worker_document_deliveries.month', $month);
            })
            ->select('workers.*', 'worker_document_deliveries.morning_delivery_date', 'worker_document_deliveries.evening_delivery_date')
            ->orderBy('worker_document_deliveries.' . $sort, $direction)
            ->distinct();
        } elseif (in_array($sort, ['id', 'name', 'national_id', 'company_id', 'job_type_id'])) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('name', 'asc');
        }

        $workers = $query->get();
        
        // Load relationships after sorting
        $workers->load('company', 'jobType');

        // Get existing deliveries for this month (all shifts)
        $deliveriesQuery = WorkerDocumentDelivery::where('year', $year)
            ->where('month', $month);
        
        // Limit to user's company if not super admin
        if (!Auth::user()->isSuperAdmin()) {
            $deliveriesQuery->whereHas('worker', function ($q) {
                $q->where('company_id', Auth::user()->company_id);
            });
        }
        
        $deliveries = $deliveriesQuery->get()
            ->keyBy(function ($item) {
                return "{$item->worker_id}_{$item->shift}";
            });

        return view('back.worker-document-deliveries.quick-entry', [
            'workers' => $workers,
            'deliveries' => $deliveries,
            'year' => $year,
            'month' => $month,
        ]);
    }

    /**
     * Update single delivery date via AJAX from quick entry table.
     */
    public function updateDeliveryAjax(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'required|integer|exists:workers,id',
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
            'shift' => 'required|in:morning,evening',
            'date' => 'nullable|date',
        ]);

        $worker = Worker::findOrFail($validated['worker_id']);
        if (!$this->canAccessWorker($worker)) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح بالوصول لهذا العامل',
            ], 403);
        }

        $delivery = WorkerDocumentDelivery::firstOrNew([
            'worker_id' => $validated['worker_id'],
            'year' => $validated['year'],
            'month' => $validated['month'],
            'shift' => $validated['shift'],
        ]);

        $dateColumn = $validated['shift'] === 'morning' ? 'morning_delivery_date' : 'evening_delivery_date';
        $delivery->{$dateColumn} = $validated['date'];
        if (!$delivery->exists) {
            $delivery->created_by = Auth::id();
        }
        $delivery->save();

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ التسليم بنجاح',
        ]);
    }

    /**
     * Store bulk deliveries
     */
    public function storeBulk(Request $request)
    {
        $year = $request->input('year');
        $month = $request->input('month');
        $deliveries = $request->input('deliveries', []);

        foreach ($deliveries as $key => $delivery) {
            if (!isset($delivery['worker_id']) || !$delivery['worker_id']) {
                continue;
            }

            $worker_id = $delivery['worker_id'];
            $worker = Worker::find($worker_id);
            
            // Check if user can access this worker
            if (!$this->canAccessWorker($worker)) {
                abort(403, 'Unauthorized access to this worker.');
            }

            // Process morning delivery
            if (!empty($delivery['morning_date'])) {
                $existing = WorkerDocumentDelivery::where('worker_id', $worker_id)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->where('shift', 'morning')
                    ->first();

                if ($existing) {
                    $existing->update(['morning_delivery_date' => $delivery['morning_date']]);
                } else {
                    WorkerDocumentDelivery::create([
                        'worker_id' => $worker_id,
                        'year' => $year,
                        'month' => $month,
                        'shift' => 'morning',
                        'morning_delivery_date' => $delivery['morning_date'],
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            // Process evening delivery
            if (!empty($delivery['evening_date'])) {
                $existing = WorkerDocumentDelivery::where('worker_id', $worker_id)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->where('shift', 'evening')
                    ->first();

                if ($existing) {
                    $existing->update(['evening_delivery_date' => $delivery['evening_date']]);
                } else {
                    WorkerDocumentDelivery::create([
                        'worker_id' => $worker_id,
                        'year' => $year,
                        'month' => $month,
                        'shift' => 'evening',
                        'evening_delivery_date' => $delivery['evening_date'],
                        'created_by' => Auth::id(),
                    ]);
                }
            }
        }

        return redirect()->route('worker-document-deliveries.quick-entry', [
            'year' => $year,
            'month' => $month,
        ])->with('success', 'تم حفظ التسليمات بنجاح');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = WorkerDocumentDelivery::with(['worker', 'creator']);
        
        // Limit to user's company if not super admin
        if (!Auth::user()->isSuperAdmin()) {
            $query->whereHas('worker', function ($q) {
                $q->where('company_id', Auth::user()->company_id);
            });
        }

        // Search by worker name or national ID
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('worker', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('national_id', 'like', "%{$search}%");
            });
        }

        // Filter by year
        if ($request->filled('year')) {
            $query->where('year', $request->input('year'));
        }

        // Filter by month
        if ($request->filled('month')) {
            $query->where('month', $request->input('month'));
        }

        // Filter by shift
        if ($request->filled('shift')) {
            $query->where('shift', $request->input('shift'));
        }

        // Sorting
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');
        
        if (in_array($sort, ['morning_delivery_date', 'evening_delivery_date'])) {
            // Keep table as is, just order by the date columns directly
            $query->orderBy($sort, $direction);
        } else {
            // Validate sort column to prevent SQL injection
            $allowedSorts = ['worker_id', 'year', 'month', 'shift', 'created_at'];
            if (!in_array($sort, $allowedSorts)) {
                $sort = 'created_at';
            }
            $query->orderBy($sort, $direction);
        }

        $deliveries = $query->paginate(15);

        return view('back.worker-document-deliveries.index', [
            'deliveries' => $deliveries,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $workersQuery = Worker::with('company')
            ->where('is_on_company_payroll', 1);
        
        // Limit to user's company if not super admin
        if (!Auth::user()->isSuperAdmin()) {
            $workersQuery->where('company_id', Auth::user()->company_id);
        }
        
        $workers = $workersQuery->orderBy('name')->get();

        return view('back.worker-document-deliveries.create', [
            'workers' => $workers,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $worker = Worker::findOrFail($request->input('worker_id'));
        
        // Check if user can access this worker
        if (!$this->canAccessWorker($worker)) {
            abort(403, 'Unauthorized access to this worker.');
        }
        
        $validated = $request->validate([
            'worker_id' => 'required|exists:workers,id',
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
            'shift' => 'required|in:morning,evening,mixed',
            'morning_delivery_date' => 'nullable|date',
            'evening_delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = Auth::id();

        WorkerDocumentDelivery::create($validated);

        return redirect()->route('worker-document-deliveries.index')
            ->with('success', 'تم إضافة تسليم السيركي بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(WorkerDocumentDelivery $workerDocumentDelivery)
    {
        $workerDocumentDelivery->load(['worker', 'creator']);
        
        // Check if user can access this delivery
        if (!$this->canAccessDelivery($workerDocumentDelivery)) {
            abort(403, 'Unauthorized access to this delivery.');
        }

        return view('back.worker-document-deliveries.show', [
            'delivery' => $workerDocumentDelivery,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WorkerDocumentDelivery $workerDocumentDelivery)
    {
        // Check if user can access this delivery
        if (!$this->canAccessDelivery($workerDocumentDelivery)) {
            abort(403, 'Unauthorized access to this delivery.');
        }
        
        $workersQuery = Worker::with('company')
            ->where('is_on_company_payroll', 1);
        
        // Limit to user's company if not super admin
        if (!Auth::user()->isSuperAdmin()) {
            $workersQuery->where('company_id', Auth::user()->company_id);
        }
        
        $workers = $workersQuery->orderBy('name')->get();

        return view('back.worker-document-deliveries.edit', [
            'delivery' => $workerDocumentDelivery,
            'workers' => $workers,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WorkerDocumentDelivery $workerDocumentDelivery)
    {
        // Check if user can access this delivery
        if (!$this->canAccessDelivery($workerDocumentDelivery)) {
            abort(403, 'Unauthorized access to this delivery.');
        }
        
        $worker = Worker::findOrFail($request->input('worker_id'));
        
        // Check if user can access the new worker
        if (!$this->canAccessWorker($worker)) {
            abort(403, 'Unauthorized access to this worker.');
        }
        
        $validated = $request->validate([
            'worker_id' => 'required|exists:workers,id',
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
            'shift' => 'required|in:morning,evening,mixed',
            'morning_delivery_date' => 'nullable|date',
            'evening_delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $workerDocumentDelivery->update($validated);

        return redirect()->route('worker-document-deliveries.index')
            ->with('success', 'تم تحديث تسليم السيركي بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WorkerDocumentDelivery $workerDocumentDelivery)
    {
        // Check if user can access this delivery
        if (!$this->canAccessDelivery($workerDocumentDelivery)) {
            abort(403, 'Unauthorized access to this delivery.');
        }
        
        $workerDocumentDelivery->delete();

        return redirect()->route('worker-document-deliveries.index')
            ->with('success', 'تم حذف تسليم السيركي بنجاح');
    }
}
