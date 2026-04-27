<?php

use Illuminate\Support\Facades\Route;
use App\Models\Company;
use App\Models\Equipment;
use App\Models\Project as ProjectModel;
use App\Models\Worker;
use App\Http\Controllers\WorkerController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\JobTypeController;
use App\Http\Controllers\EquipmentTypeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\SystemCommandController;
use App\Http\Controllers\WorkerDocumentController;
use App\Http\Controllers\WorkerDocumentDeliveryController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SubscriptionAdminController;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/admin/workers');
    } else {
        return redirect('/login');
    }

    $project = ProjectModel::latest('id')->with('company')->first();
    $stats = [
        'workers' => Worker::count(),
        'equipment' => Equipment::count(),
        'companies' => Company::count(),
        'projects' => ProjectModel::count(),
    ];

    return view('welcome', compact('project', 'stats'));
});

Route::get('/locale/{locale}', function (string $locale) {
    $supportedLocales = config('app.supported_locales', ['en', 'ar']);

    if (! in_array($locale, $supportedLocales, true)) {
        abort(404);
    }

    session(['locale' => $locale]);

    return redirect()->back();
})->name('locale.switch');

Auth::routes();

Route::middleware('auth')->group(function () {
    Route::get('/billing', [SubscriptionController::class, 'index'])->name('billing.index');
    Route::post('/billing/subscribe/{plan}', [SubscriptionController::class, 'store'])->name('billing.subscribe');
    Route::post('/billing/cancel', [SubscriptionController::class, 'destroy'])->name('billing.cancel');
});

Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'super.admin', 'company.subscription']], function () {
    Route::resource('companies', CompanyController::class);
    Route::resource('projects', ProjectController::class);
    Route::resource('jobtypes', JobTypeController::class);
    Route::resource('equipment-types', EquipmentTypeController::class)
        ->parameters(['equipment-types' => 'equipmentType']);
    Route::resource('users', UserController::class);
    Route::post('system/update-optimize', [SystemCommandController::class, 'updateAndOptimize'])
        ->name('system.update-optimize');
});

Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'manage.all', 'company.subscription']], function () {
    Route::get('subscriptions/manage', [SubscriptionAdminController::class, 'index'])->name('admin.subscriptions.manage');
    Route::post('subscriptions/plans', [SubscriptionAdminController::class, 'storePlan'])->name('admin.subscriptions.plans.store');
    Route::put('subscriptions/plans/{plan}', [SubscriptionAdminController::class, 'updatePlan'])->name('admin.subscriptions.plans.update');
    Route::delete('subscriptions/plans/{plan}', [SubscriptionAdminController::class, 'destroyPlan'])->name('admin.subscriptions.plans.destroy');
    Route::post('subscriptions/companies/{company}', [SubscriptionAdminController::class, 'assignCompanySubscription'])->name('admin.subscriptions.companies.assign');
    Route::post('subscriptions/companies/{company}/cancel', [SubscriptionAdminController::class, 'cancelCompanySubscription'])->name('admin.subscriptions.companies.cancel');
});

Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'company.subscription']], function () {
    Route::get('dashboard', function () {
        return view('back.dashboard');
    })->name('dashboard');
    Route::get('workers/{worker}/export-pdf', [WorkerDocumentController::class, 'exportPdf'])->name('workers.export.pdf');
    Route::get('workers/export-pdf-merged', [WorkerDocumentController::class, 'exportPdfMerged'])->name('workers.export.pdf.merged');
    Route::get('workers/{worker}/export-word', [WorkerDocumentController::class, 'exportWord'])->name('workers.export.word');
    Route::get('workers/export-word-all', [WorkerDocumentController::class, 'exportWordAll'])->name('workers.export.word.all');
    Route::get('workers/export-word-merged', [WorkerDocumentController::class, 'exportWordMerged'])->name('workers.export.word.merged');
    Route::get('workers/{worker}/export-word-pdf', [WorkerDocumentController::class, 'exportWordPdf'])->name('workers.export.wordpdf');
    Route::get('workers/export-word-pdf-all', [WorkerDocumentController::class, 'exportWordPdfAll'])->name('workers.export.wordpdf.all');
    Route::get('workers/{worker}/export-daily-equipment-inspection', [WorkerDocumentController::class, 'exportDailyEquipmentInspection'])->name('workers.export.daily-equipment-inspection');
    // Route::get('/workers/{worker}/preview', [WorkerController::class, 'preview'])->name('workers.preview');

    Route::resource('workers', WorkerController::class);
    Route::get('/equipment/export-word-selected', [EquipmentController::class, 'exportWordSelected'])
        ->name('equipment.exportWordSelected');
    Route::get('/equipment/{equipment}/export-word', [EquipmentController::class, 'exportWord'])
        ->name('equipment.exportWord');
    Route::resource('equipment', EquipmentController::class);
    Route::resource('worker-document-deliveries', WorkerDocumentDeliveryController::class);
    Route::get('worker-document-receive', [WorkerDocumentDeliveryController::class, 'quickEntry'])->name('worker-document-deliveries.receive');
    Route::post('worker-document-deliveries-bulk', [WorkerDocumentDeliveryController::class, 'storeBulk'])->name('worker-document-deliveries.bulk-store');
    Route::post('worker-document-deliveries-ajax-update', [WorkerDocumentDeliveryController::class, 'updateDeliveryAjax'])->name('worker-document-deliveries.ajax-update');
});