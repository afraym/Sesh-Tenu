<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkerController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\JobTypeController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::group(['prefix' => 'admin'], function () {
    Route::get('workers/{worker}/export-pdf', [WorkerController::class, 'exportPdf'])->name('workers.export.pdf');
    Route::get('workers/export-pdf-merged', [WorkerController::class, 'exportPdfMerged'])->name('workers.export.pdf.merged');
    Route::get('workers/{worker}/export-word', [WorkerController::class, 'exportWord'])->name('workers.export.word');
    Route::get('workers/export-word-all', [WorkerController::class, 'exportWordAll'])->name('workers.export.word.all');
    Route::get('workers/{worker}/export-word-pdf', [WorkerController::class, 'exportWordPdf'])->name('workers.export.wordpdf');
    Route::get('workers/export-word-pdf-all', [WorkerController::class, 'exportWordPdfAll'])->name('workers.export.wordpdf.all');
    Route::get('/workers/{worker}/preview', [WorkerController::class, 'preview'])->name('workers.preview');

    Route::resource('workers', WorkerController::class);
    Route::resource('companies', CompanyController::class);
    Route::resource('projects', ProjectController::class);
    Route::resource('jobtypes', JobTypeController::class);
});