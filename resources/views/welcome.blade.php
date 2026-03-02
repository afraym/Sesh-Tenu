@extends('layouts.front')
@section('content')
@include('front.navbar')
@php
    $projectData = $project ?? null;
    $statsData = $stats ?? [];
    $projectName = optional($projectData)->name ?? 'Abydos 2 Solar Power Project';
    $companyName = optional(optional($projectData)->company)->name ?? 'Solar Consortium';
    $siteImage1 = asset('assets/img/site/002.jpeg');
    $siteImage2 = asset('assets/img/site/003.jpeg');
    $siteImage3 = asset('assets/img/site/004.jpeg');
@endphp

<div class="wrapper index-page">
    <div class="page-header header-filter" style="min-height: 78vh;">
        <div class="squares square1"></div>
        <div class="squares square2"></div>
        <div class="squares square3"></div>
        <div class="squares square4"></div>
        <div class="squares square5"></div>
        <div class="squares square6"></div>
        <div class="squares square7"></div>

        <div class="container">
            <div class="row align-items-center" style="min-height: 70vh;">
                <div class="col-lg-7 col-md-8">
                    <h1 class="display-3 font-weight-bold mb-3">Solar Panel Project Management System</h1>
                    <p class="lead text-white mb-4">
                        A centralized platform to plan, execute, and monitor utility-scale solar projects across workforce, equipment, and documentation.
                    </p>
                    <p class="text-white mb-4"><strong>{{ $projectName }}</strong> · {{ $companyName }}</p>
                    <div class="d-flex flex-wrap">
                        <a href="/login" class="btn btn-success btn-round mr-2 mb-2">Get Started</a>
                        <a href="#platform-modules" class="btn btn-outline-light btn-round mb-2">Explore Modules</a>
                    </div>
                </div>
                <div class="col-lg-5 col-md-4">
                    <div class="card card-stats mb-0">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 mb-2">
                                    <h5 class="card-title mb-0">System Highlights</h5>
                                </div>
                                <div class="col-6 mt-3">
                                    <p class="card-category mb-1">Workforce</p>
                                    <h3 class="card-title">{{ $statsData['workers'] ?? 0 }}</h3>
                                </div>
                                <div class="col-6 mt-3">
                                    <p class="card-category mb-1">Equipment</p>
                                    <h3 class="card-title">{{ $statsData['equipment'] ?? 0 }}</h3>
                                </div>
                                <div class="col-6 mt-3">
                                    <p class="card-category mb-1">Companies</p>
                                    <h3 class="card-title">{{ $statsData['companies'] ?? 0 }}</h3>
                                </div>
                                <div class="col-6 mt-3">
                                    <p class="card-category mb-1">Projects</p>
                                    <h3 class="card-title text-success">{{ $statsData['projects'] ?? 0 }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="main" id="project-overview">
        <section class="section section-lg">
            <div class="container">
                <div class="row mb-5">
                    <div class="col-lg-8 mx-auto text-center">
                        <h2 class="title">Built for Solar Project Operations</h2>
                        <p class="description">
                            Manage field teams, monitor project resources, and generate operational documents from one unified dashboard.
                        </p>
                    </div>
                </div>

                <div class="row" id="platform-modules">
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <img src="{{ $siteImage1 }}" class="card-img-top" alt="Solar Site Image 1" style="height:180px; object-fit:cover;">
                            <div class="card-body">
                                <h4 class="card-title">Worker Management</h4>
                                <p class="card-text">Register workforce details, assign job types, and maintain payroll and community status.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <img src="{{ $siteImage2 }}" class="card-img-top" alt="Solar Site Image 2" style="height:180px; object-fit:cover;">
                            <div class="card-body">
                                <h4 class="card-title">Equipment Module</h4>
                                <p class="card-text">Track project equipment records and keep resource availability visible to operations teams.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <img src="{{ $siteImage3 }}" class="card-img-top" alt="Solar Site Image 3" style="height:180px; object-fit:cover;">
                            <div class="card-body">
                                <h4 class="card-title">Document Automation</h4>
                                <p class="card-text">Generate worker timesheets and project documents in Word/PDF formats with one click.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h4 class="card-title">Project Registry</h4>
                                <p class="card-text">Maintain project metadata and company associations for structured, auditable records.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h4 class="card-title">Role-Based Access</h4>
                                <p class="card-text">Secure access with admin controls and authentication to protect operational data.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h4 class="card-title">Export & Reporting</h4>
                                <p class="card-text">Produce consolidated outputs for management review and contractor handover.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section pt-0">
            <div class="container">
                <div class="row text-center mb-4">
                    <div class="col-lg-8 mx-auto">
                        <h3 class="title">How It Works</h3>
                    </div>
                </div>
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <h4 class="mb-1">1</h4>
                        <p class="description">Set up company, project, and job types.</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <h4 class="mb-1">2</h4>
                        <p class="description">Register workers and equipment records.</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <h4 class="mb-1">3</h4>
                        <p class="description">Manage daily operations from dashboard.</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <h4 class="mb-1">4</h4>
                        <p class="description">Export project documents and reports.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="section pt-0">
            <div class="container">
                <div class="card card-plain">
                    <div class="card-body text-center">
                        <h3 class="mb-3">Ready to run your solar panel project efficiently?</h3>
                        <a href="/login" class="btn btn-primary btn-round mr-2">Sign In</a>
                        <a href="/admin/workers" class="btn btn-info btn-round">Open Dashboard</a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection