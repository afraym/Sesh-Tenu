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
        return view('themes.blk.back.jobtypes.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('themes.blk.back.jobtypes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Job_type $job_type)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Job_type $job_type)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Job_type $job_type)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Job_type $job_type)
    {
        //
    }
}
