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
    Route::get('workers/{worker}/export-word', [WorkerController::class, 'exportWord'])->name('workers.export.word');

    Route::resource('workers', WorkerController::class);
    Route::resource('companies', CompanyController::class);
    Route::resource('projects', ProjectController::class);
    Route::resource('jobtypes', JobTypeController::class);
});