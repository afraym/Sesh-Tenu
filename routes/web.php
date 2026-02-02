<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkerController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::group(['prefix' => 'admin'], function () {
Route::resource('workers', WorkerController::class);
Route::resource('companies', CompanyController::class);
Route::resource('projects', ProjectController::class);
Route::resource('jobtypes', JobTypeController::class);
});