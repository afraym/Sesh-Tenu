<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkerController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\JobTypeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\WorkerDocumentController;

Route::get('/', function () {
    return auth()->check() ? redirect('/admin/workers') : view('welcome');
});

Auth::routes();

Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'super.admin']], function () {
    Route::resource('companies', CompanyController::class);
    Route::resource('projects', ProjectController::class);
    Route::resource('jobtypes', JobTypeController::class);
    Route::resource('users', UserController::class);
});

Route::group(['prefix' => 'admin', 'middleware' => ['auth']], function () {
    Route::get('workers/{worker}/export-pdf', [WorkerDocumentController::class, 'exportPdf'])->name('workers.export.pdf');
    Route::get('workers/export-pdf-merged', [WorkerDocumentController::class, 'exportPdfMerged'])->name('workers.export.pdf.merged');
    Route::get('workers/{worker}/export-word', [WorkerDocumentController::class, 'exportWord'])->name('workers.export.word');
    Route::get('workers/export-word-all', [WorkerDocumentController::class, 'exportWordAll'])->name('workers.export.word.all');
    Route::get('workers/export-word-merged', [WorkerDocumentController::class, 'exportWordMerged'])->name('workers.export.word.merged');
    Route::get('workers/{worker}/export-word-pdf', [WorkerDocumentController::class, 'exportWordPdf'])->name('workers.export.wordpdf');
    Route::get('workers/export-word-pdf-all', [WorkerDocumentController::class, 'exportWordPdfAll'])->name('workers.export.wordpdf.all');
    // Route::get('/workers/{worker}/preview', [WorkerController::class, 'preview'])->name('workers.preview');

    Route::resource('workers', WorkerController::class);
    Route::resource('equipment', EquipmentController::class);
    
    Route::get('/equipment/{equipment}/export-word', [EquipmentController::class, 'exportWord'])
        ->name('equipment.exportWord');
});