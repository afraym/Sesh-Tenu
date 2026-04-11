<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  {{-- <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png"> --}}
  <link rel="apple-touch-icon" sizes="180x180" href="/assets/img//apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/img//favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/assets/img//favicon-16x16.png">
<link rel="manifest" href="/site.webmanifest">
  <title>
    Sesh Tenu 📋
  </title>
  <!--     Fonts and icons     -->
  <link href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,600,700,800" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link href="https://use.fontawesome.com/releases/v5.0.6/css/all.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">

  <!-- Nucleo Icons -->
  <link href="{{ asset('assets/css/nucleo-icons.css') }}" rel="stylesheet" />
  <!-- CSS Files -->
  {{-- <link href="{{ asset('assets/css/black-dashboard.min.css?v=1.0.0') }}" rel="stylesheet" /> --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/afraym/Sesh-Tenu/public/assets/css/black-dashboard.min.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-rtl/3.4.0/css/bootstrap-rtl.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker3.min.css" rel="stylesheet" />
  <!-- CSS Just for demo purpose, don't include it in your project -->
  <link href="{{ asset('assets/css/custom.min.css') }}" rel="stylesheet" />
  <style>
    select.form-control option,
    select.form-control optgroup {
      color: #1f2333 !important;
      background-color: #ffffff !important;
    }

    .datepicker,
    .datepicker table,
    .datepicker table tr td,
    .datepicker table tr th {
      font-family: 'Cairo', 'Segoe UI', Tahoma, Arial, sans-serif;
    }

    .navbar-center-logo {
      position: absolute;
      left: 50%;
      transform: translateX(-50%);
      top: 50%;
      transform: translate(-50%, -50%);
      pointer-events: none;
      z-index: 2;
    }

    .navbar-center-logo img {
      height: 35px;
      width: auto;
      object-fit: contain;
      filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.25));
    }

    .navbar-center-logo .logo-bright {
      display: none;
    }

    body.white-content .navbar-center-logo .logo-dark {
      display: none;
    }

    body.white-content .navbar-center-logo .logo-bright {
      display: block;
    }

    @media (max-width: 991.98px) {
      .navbar-center-logo img {
        height: 26px;
      }
    }
  </style>

</head>

<body class=" rtl menu-on-right ">
  <div class="wrapper">
    <div class="sidebar" data="blue">
      <!--
        Tip 1: You can change the color of the sidebar using: data-color="blue | green | orange | red"
    -->
      <div class="sidebar-wrapper" >
        <div class="logo">
          <img src="{{asset('assets/img/shtnwlogo.png')}}" alt="  Sesh Tenu Logo" class="logo-image img-fluid" style="max-width: 80%; margin: 0 auto; display: block;">
        </div>
        <ul class="nav">
           <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}">
              <i class="fa-solid fa-tv"></i>
              <p>الرئيسة</p>
            </a>
          </li>
          <li class="{{ request()->routeIs('workers.index') ? 'active' : '' }}">
            <a href="{{ route('workers.index') }}">
              <i class="fa-solid fa-helmet-safety"></i>
              <p>القوى العاملة</p>
            </a>
          </li>
          <li class="{{ request()->routeIs('workers.create') ? 'active' : '' }}">
            <a href="{{ route('workers.create') }}">
              <i class="fa-solid fa-user-plus"></i>
              <p>ادخال جديد</p>
            </a>
          </li>
          <li class="{{ request()->routeIs('equipment.index') ? 'active' : '' }}">
            <a href="{{ route('equipment.index') }}">
              <i class="tim-icons icon-delivery-fast"></i>
              <p>المُعدات</p>
            </a>
          </li>
          <li class="{{ request()->routeIs('equipment-types.index') ? 'active' : '' }}">
            <a href="{{ route('equipment-types.index') }}">
            <i class="fa-solid fa-tractor"></i>
            <p>أنواع المٌعدات</p>
            </a>
          </li>
          {{-- <li class="{{ request()->routeIs('equipment.create') ? 'active' : '' }}">
            <a href="{{ route('equipment.create') }}">
              <i class="tim-icons icon-simple-add"></i>
              <p>إضافة معدة</p>
            </a>
          </li> --}}
          <li class="{{ request()->routeIs('worker-document-deliveries.index') ? 'active' : '' }}">
            <a href="{{ route('worker-document-deliveries.index') }}">
              <i class="far fa-calendar-check"></i>
              <p>متابعة السركي</p>
            </a>
          </li>
          <li class="{{ request()->routeIs('worker-document-deliveries.receive') ? 'active' : '' }}">
            <a href="{{ route('worker-document-deliveries.receive') }}">
              <i class="fas fa-user-check"></i>
              <p>استلام سركي</p>
            </a>
          </li>
          {{-- <li class="{{ request()->routeIs('worker-document-deliveries.create') ? 'active' : '' }}">
            <a href="{{ route('worker-document-deliveries.create') }}">
              <i class="tim-icons icon-simple-add"></i>
              <p>إضافة تسليم</p>
            </a>
          </li> --}}
          @if(auth()->check() && auth()->user()->isSuperAdmin())
            <li class="{{ request()->routeIs('companies.index') ? 'active' : '' }}">
                <a href="{{ route('companies.index') }}">
                <i class="tim-icons icon-bank"></i>
                <p>الشركات</p>
                </a>
          </li>
            {{-- <li class="{{ request()->routeIs('companies.create') ? 'active' : '' }}">
                <a href="{{ route('companies.create') }}">
                <i class="tim-icons icon-simple-add"></i>
                <p>إضافة شركة</p>
                </a>
          </li> --}}
          <li class="{{ request()->routeIs('projects.index') ? 'active' : '' }}">
            <a href="{{ route('projects.index') }}">
              <i class="tim-icons icon-chart-pie-36"></i>
              <p>المشاريع</p>
            </a>
          </li>
          {{-- <li class="{{ request()->routeIs('projects.create') ? 'active' : '' }}">
            <a href="{{ route('projects.create') }}">
              <i class="tim-icons icon-simple-add"></i>
              <p>إضافة مشروع</p>
            </a>
          </li> --}}
            <li class="{{ request()->routeIs('jobtypes.index') ? 'active' : '' }}">
                <a href="{{ route('jobtypes.index') }}">
                <i class="tim-icons icon-bullet-list-67"></i>
                <p>أنواع الوظائف</p>
                </a>
            </li>
            {{-- <li class="{{ request()->routeIs('jobtypes.create') ? 'active' : '' }}">
                <a href="{{ route('jobtypes.create') }}">
                <i class="tim-icons icon-simple-add"></i>
                <p>إضافة نوع وظيفة</p>
                </a>
            </li> --}}

            {{-- <li class="{{ request()->routeIs('equipment-types.create') ? 'active' : '' }}">
              <a href="{{ route('equipment-types.create') }}">
              <i class="tim-icons icon-simple-add"></i>
              <p>إضافة نوع معدة</p>
              </a>
            </li> --}}
            <li class="{{ request()->routeIs('users.index') ? 'active' : '' }}">
                <a href="{{ route('users.index') }}">
                <i class="tim-icons icon-single-02"></i>
                <p>المستخدمين</p>
                </a>
            </li>
            <li class="{{ request()->routeIs('users.create') ? 'active' : '' }}">
                <a href="{{ route('users.create') }}">
                <i class="tim-icons icon-simple-add"></i>
                <p>إضافة مستخدم</p>
                </a>
            </li>
            @endif
        </ul>
      </div>
    </div>
    <div class="main-panel">
      <!-- Navbar -->
      <nav class="navbar navbar-expand-lg navbar-absolute navbar-transparent">
        <div class="container-fluid">
          <div class="navbar-wrapper">
            <div class="navbar-toggle d-inline">
              <button type="button" class="navbar-toggler">
                <span class="navbar-toggler-bar bar1"></span>
                <span class="navbar-toggler-bar bar2"></span>
                <span class="navbar-toggler-bar bar3"></span>
              </button>
            </div>
         
          </div>
          <div class="navbar-center-logo d-flex align-items-center justify-content-center">
            <img src="{{ asset('logos/energychina-b.png') }}" alt="Energy China" class="logo-dark">
            <img src="{{ asset('logos/energychina-w.png') }}" alt="Energy China" class="logo-bright">
          </div>
          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navigation" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-bar navbar-kebab"></span>
            <span class="navbar-toggler-bar navbar-kebab"></span>
            <span class="navbar-toggler-bar navbar-kebab"></span>
          </button>
          <div class="collapse navbar-collapse" id="navigation">
            <ul class="navbar-nav  mr-auto">
              @if(auth()->check() && auth()->user()->isSuperAdmin())
                <li class="nav-item">
                  <form action="{{ route('system.update-optimize') }}" method="POST" class="d-inline" onsubmit="return confirm('Run git pull and php artisan optimize now?');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-info mb-0">
                      <i class="tim-icons icon-refresh-01"></i> تحديث 
                    </button>
                  </form>
                </li>
              @endif
              <li class="search-bar input-group">
                <button class="btn btn-link" id="search-button" data-toggle="modal" data-target="#searchModal"><i class="tim-icons icon-zoom-split" ></i>
                  <span class="d-lg-none d-md-block">Search</span>
                </button>
              </li>
              @php
                $notif_workers_today   = \App\Models\Worker::whereDate('created_at', now()->toDateString())->count();
                $notif_deliveries_today = \App\Models\WorkerDocumentDelivery::whereDate('created_at', now()->toDateString())->count();
                $notif_workers_week    = \App\Models\Worker::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
                $notif_workers_total   = \App\Models\Worker::count();
                $notif_equip_total     = \App\Models\Equipment::count();
                $notif_badge           = ($notif_workers_today > 0 || $notif_deliveries_today > 0) ? true : false;
              @endphp
              <li class="dropdown nav-item">
                <a href="javascript:void(0)" class="dropdown-toggle nav-link" data-toggle="dropdown">
                  @if($notif_badge)
                    <div class="notification d-none d-lg-block d-xl-block"></div>
                  @endif
                  <i class="tim-icons icon-sound-wave"></i>
                  <p class="d-lg-none">الإشعارات</p>
                </a>
                <ul class="dropdown-menu dropdown-menu-right dropdown-navbar" style="min-width:270px">
                  <li class="dropdown-header text-muted px-3 py-2" style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;">
                    اليوم — {{ now()->translatedFormat('l j M') }}
                  </li>
                  <li class="nav-link">
                    <a href="{{ route('workers.index') }}" class="nav-item dropdown-item d-flex justify-content-between align-items-center">
                      <span><i class="fa-solid fa-user-plus ml-1" style="color:#3987f6"></i> عمال مضافون اليوم</span>
                      <span class="badge badge-pill" style="background:rgba(57,135,246,.18);color:#3987f6;font-size:12px">{{ $notif_workers_today }}</span>
                    </a>
                  </li>
                  <li class="nav-link">
                    <a href="{{ route('worker-document-deliveries.index') }}" class="nav-item dropdown-item d-flex justify-content-between align-items-center">
                      <span><i class="fas fa-user-check ml-1" style="color:#22c55e"></i> استلامات سركي اليوم</span>
                      <span class="badge badge-pill" style="background:rgba(34,197,94,.18);color:#22c55e;font-size:12px">{{ $notif_deliveries_today }}</span>
                    </a>
                  </li>
                  <li class="dropdown-divider"></li>
                  <li class="nav-link">
                    <a href="{{ route('workers.index') }}" class="nav-item dropdown-item d-flex justify-content-between align-items-center">
                      <span><i class="fa-solid fa-users ml-1" style="color:#8b6df0"></i> عمال هذا الأسبوع</span>
                      <span class="badge badge-pill" style="background:rgba(139,109,240,.18);color:#8b6df0;font-size:12px">{{ $notif_workers_week }}</span>
                    </a>
                  </li>
                  <li class="nav-link">
                    <a href="{{ route('workers.index') }}" class="nav-item dropdown-item d-flex justify-content-between align-items-center">
                      <span><i class="fa-solid fa-person-digging ml-1" style="color:#f2994a"></i> إجمالي العمال</span>
                      <span class="badge badge-pill" style="background:rgba(242,153,74,.18);color:#f2994a;font-size:12px">{{ $notif_workers_total }}</span>
                    </a>
                  </li>
                  <li class="nav-link">
                    <a href="{{ route('equipment.index') }}" class="nav-item dropdown-item d-flex justify-content-between align-items-center">
                      <span><i class="tim-icons icon-delivery-fast ml-1" style="color:#00bcd4"></i> إجمالي المعدات</span>
                      <span class="badge badge-pill" style="background:rgba(0,188,212,.18);color:#00bcd4;font-size:12px">{{ $notif_equip_total }}</span>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="dropdown nav-item">
                <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
                  <div class="photo">
                    <img src="../assets/img/anime3.png" alt="Profile Photo">
                  </div>
                  <b class="caret d-none d-lg-block d-xl-block"></b>
                  <p class="d-lg-none">
                    Log out
                  </p>
                </a>
                <ul class="dropdown-menu dropdown-navbar">
                  {{-- <li class="nav-link"><a href="javascript:void(0)" class="nav-item dropdown-item">Profile</a></li>
                  <li class="nav-link"><a href="javascript:void(0)" class="nav-item dropdown-item">Settings</a></li> --}}
                  <li class="dropdown-divider"></li>
<li class="nav-link"><a href="#" class="nav-item dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">خروج <i class="tim-icons icon-button-power"></i></a></li>                </ul>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
                </form>
                
              <li class="separator d-lg-none"></li>
            </ul>
          </div>
        </div>
      </nav>
      @if(session('success'))
        <div class="container-fluid mt-2">
          <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
            <i class="tim-icons icon-check-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
        </div>
      @endif
      @if(session('error'))
        <div class="container-fluid mt-2">
          <div class="alert alert-danger alert-dismissible fade show mb-0" role="alert">
            <i class="tim-icons icon-alert-circle-exc"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
        </div>
      @endif
      <div class="modal modal-search fade" id="searchModal" tabindex="-1" role="dialog" aria-labelledby="searchModal" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <input type="text" class="form-control" id="inlineFormInputGroup" placeholder="SEARCH">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <i class="tim-icons icon-simple-remove"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
      <!-- End Navbar -->