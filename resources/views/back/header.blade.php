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
  <link href="{{ asset('assets/css/black-dashboard.css?v=1.0.0') }}" rel="stylesheet" />
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
          <a href="javascript:void(0)" class="simple-text logo-mini">
           س ت
          </a>
          <a href="javascript:void(0)" class="simple-text logo-normal">
       سش تنو
          </a>
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
              <i class="fa-solid fa-person-digging"></i>
              <p>العمال</p>
            </a>
          </li>
          <li class="{{ request()->routeIs('workers.create') ? 'active' : '' }}">
            <a href="{{ route('workers.create') }}">
              <i class="tim-icons icon-simple-add"></i>
              <p>إضافة عامل</p>
            </a>
          </li>
          <li class="{{ request()->routeIs('equipment.index') ? 'active' : '' }}">
            <a href="{{ route('equipment.index') }}">
              <i class="tim-icons icon-delivery-fast"></i>
              <p>المعدات</p>
            </a>
          </li>
          <li class="{{ request()->routeIs('equipment-types.index') ? 'active' : '' }}">
            <a href="{{ route('equipment-types.index') }}">
            <i class="fa-solid fa-tractor"></i>
            <p>أنواع المعدات</p>
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
              <p>متابعة السيركي</p>
            </a>
          </li>
          <li class="{{ request()->routeIs('worker-document-deliveries.receive') ? 'active' : '' }}">
            <a href="{{ route('worker-document-deliveries.receive') }}">
              <i class="fas fa-user-check"></i>
              <p>استلام سيركي</p>
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
          <a class="navbar-brand">
            @if(auth()->check() && auth()->user()->company)
              <img src="{{ asset(auth()->user()->company->logo)  }}" alt="{{ auth()->user()->company->name }}" class="company-logo" style="width: 90px;height: 90px;">
            @endif
          </a>
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
              <li class="dropdown nav-item">
                <a href="javascript:void(0)" class="dropdown-toggle nav-link" data-toggle="dropdown">
                  <div class="notification d-none d-lg-block d-xl-block"></div>
                  <i class="tim-icons icon-sound-wave"></i>
                  <p class="d-lg-none">
                    Notifications
                  </p>
                </a>
                <ul class="dropdown-menu dropdown-menu-right dropdown-navbar">
                  <li class="nav-link"><a href="#" class="nav-item dropdown-item">Mike John responded to your email</a></li>
                  <li class="nav-link"><a href="javascript:void(0)" class="nav-item dropdown-item">You have 5 more tasks</a></li>
                  <li class="nav-link"><a href="javascript:void(0)" class="nav-item dropdown-item">Your friend Michael is in town</a></li>
                  <li class="nav-link"><a href="javascript:void(0)" class="nav-item dropdown-item">Another notification</a></li>
                  <li class="nav-link"><a href="javascript:void(0)" class="nav-item dropdown-item">Another one</a></li>
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