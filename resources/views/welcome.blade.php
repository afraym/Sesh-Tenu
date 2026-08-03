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
                    <p class="text-uppercase text-white-50 mb-2" style="letter-spacing: 0.18em;">{{ __('welcome.hero_eyebrow') }}</p>
                    <h1 class="display-3 font-weight-bold mb-3">{{ __('welcome.hero_title') }}</h1>
                    <p class="lead text-white mb-4">
                        {{ __('welcome.hero_description') }}
                    </p>
                    <p class="text-white mb-4">
                        <strong>{{ $projectName }}</strong> · {{ $companyName }}
                    </p>
                    <div class="d-flex flex-wrap">
                        <a href="/login" class="btn btn-success btn-round mr-2 mb-2">{{ __('welcome.cta_primary') }}</a>
                        <a href="#platform-modules" class="btn btn-outline-light btn-round mb-2">{{ __('welcome.cta_secondary') }}</a>
                    </div>
                </div>
                <div class="col-lg-5 col-md-4">
                    <div class="card card-stats mb-0">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 mb-2">
                                    <h5 class="card-title mb-0">{{ __('welcome.highlights_title') }}</h5>
                                </div>
                                <div class="col-6 mt-3">
                                    <p class="card-category mb-1">{{ __('welcome.metric_manpower') }}</p>
                                    <h3 class="card-title">{{ $statsData['workers'] ?? 0 }}</h3>
                                </div>
                                <div class="col-6 mt-3">
                                    <p class="card-category mb-1">{{ __('welcome.metric_equipment') }}</p>
                                    <h3 class="card-title">{{ $statsData['equipment'] ?? 0 }}</h3>
                                </div>
                                <div class="col-6 mt-3">
                                    <p class="card-category mb-1">{{ __('welcome.metric_companies') }}</p>
                                    <h3 class="card-title">{{ $statsData['companies'] ?? 0 }}</h3>
                                </div>
                                <div class="col-6 mt-3">
                                    <p class="card-category mb-1">{{ __('welcome.metric_projects') }}</p>
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
                        <h2 class="title">{{ __('welcome.overview_title') }}</h2>
                        <p class="description">
                            {{ __('welcome.overview_description') }}
                        </p>
                    </div>
                </div>

                <div class="row" id="platform-modules">
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <img src="{{ $siteImage1 }}" class="card-img-top" alt="{{ __('welcome.project_site_alt_1') }}" style="height:180px; object-fit:cover;">
                            <div class="card-body">
                                <h4 class="card-title">{{ __('welcome.module_manpower_title') }}</h4>
                                <p class="card-text">{{ __('welcome.module_manpower_description') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <img src="{{ $siteImage2 }}" class="card-img-top" alt="{{ __('welcome.project_site_alt_2') }}" style="height:180px; object-fit:cover;">
                            <div class="card-body">
                                <h4 class="card-title">{{ __('welcome.module_equipment_title') }}</h4>
                                <p class="card-text">{{ __('welcome.module_equipment_description') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <img src="{{ $siteImage3 }}" class="card-img-top" alt="{{ __('welcome.project_site_alt_3') }}" style="height:180px; object-fit:cover;">
                            <div class="card-body">
                                <h4 class="card-title">{{ __('welcome.module_document_title') }}</h4>
                                <p class="card-text">{{ __('welcome.module_document_description') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h4 class="card-title">{{ __('welcome.module_project_title') }}</h4>
                                <p class="card-text">{{ __('welcome.module_project_description') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h4 class="card-title">{{ __('welcome.module_access_title') }}</h4>
                                <p class="card-text">{{ __('welcome.module_access_description') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h4 class="card-title">{{ __('welcome.module_reporting_title') }}</h4>
                                <p class="card-text">{{ __('welcome.module_reporting_description') }}</p>
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
                        <h3 class="title">{{ __('welcome.steps_title') }}</h3>
                    </div>
                </div>
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <h4 class="mb-1">1</h4>
                        <p class="description">{{ __('welcome.step1') }}</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <h4 class="mb-1">2</h4>
                        <p class="description">{{ __('welcome.step2') }}</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <h4 class="mb-1">3</h4>
                        <p class="description">{{ __('welcome.step3') }}</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <h4 class="mb-1">4</h4>
                        <p class="description">{{ __('welcome.step4') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="section pt-0">
            <div class="container">
                <div class="card card-plain">
                    <div class="card-body text-center">
                        <h3 class="mb-3">{{ __('welcome.closing_title') }}</h3>
                        <a href="/login" class="btn btn-primary btn-round mr-2">{{ __('welcome.sign_in') }}</a>
                        <a href="/admin/dashboard" class="btn btn-info btn-round">{{ __('welcome.open_dashboard') }}</a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection