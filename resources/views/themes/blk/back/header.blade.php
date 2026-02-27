<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
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
  <link href="https://use.fontawesome.com/releases/v5.0.6/css/all.css" rel="stylesheet">
  <!-- Nucleo Icons -->
  <link href="{{ asset('assets/css/nucleo-icons.css') }}" rel="stylesheet" />
  <!-- CSS Files -->
  <link href="{{ asset('assets/css/black-dashboard.css?v=1.0.0') }}" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-rtl/3.4.0/css/bootstrap-rtl.css" rel="stylesheet" />
  <!-- CSS Just for demo purpose, don't include it in your project -->
  <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet" />
</head>

<body class=" rtl menu-on-right ">
  <div class="wrapper">
    <div class="sidebar">
      <!--
        Tip 1: You can change the color of the sidebar using: data-color="blue | green | orange | red"
    -->
      <div class="sidebar-wrapper">
        <div class="logo">
          <a href="javascript:void(0)" class="simple-text logo-mini">
           س ت
          </a>
          <a href="javascript:void(0)" class="simple-text logo-normal">
       سش تنو
          </a>
        </div>
        <ul class="nav">
          <li class="{{ request()->routeIs('workers.index') ? 'active' : '' }}">
            <a href="{{ route('workers.index') }}">
              <i class="tim-icons icon-single-02"></i>
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
          <li class="{{ request()->routeIs('equipment.create') ? 'active' : '' }}">
            <a href="{{ route('equipment.create') }}">
              <i class="tim-icons icon-simple-add"></i>
              <p>إضافة معدة</p>
            </a>
          </li>
          @if(auth()->check() && auth()->user()->isSuperAdmin())
            <li class="{{ request()->routeIs('companies.index') ? 'active' : '' }}">
                <a href="{{ route('companies.index') }}">
                <i class="tim-icons icon-bank"></i>
                <p>الشركات</p>
                </a>
          </li>
            <li class="{{ request()->routeIs('companies.create') ? 'active' : '' }}">
                <a href="{{ route('companies.create') }}">
                <i class="tim-icons icon-simple-add"></i>
                <p>إضافة شركة</p>
                </a>
          </li>
          <li class="{{ request()->routeIs('projects.index') ? 'active' : '' }}">
            <a href="{{ route('projects.index') }}">
              <i class="tim-icons icon-chart-pie-36"></i>
              <p>المشاريع</p>
            </a>
          </li>
          <li class="{{ request()->routeIs('projects.create') ? 'active' : '' }}">
            <a href="{{ route('projects.create') }}">
              <i class="tim-icons icon-simple-add"></i>
              <p>إضافة مشروع</p>
            </a>
          </li>
            <li class="{{ request()->routeIs('jobtypes.index') ? 'active' : '' }}">
                <a href="{{ route('jobtypes.index') }}">
                <i class="tim-icons icon-bullet-list-67"></i>
                <p>أنواع الوظائف</p>
                </a>
            </li>
            <li class="{{ request()->routeIs('jobtypes.create') ? 'active' : '' }}">
                <a href="{{ route('jobtypes.create') }}">
                <i class="tim-icons icon-simple-add"></i>
                <p>إضافة نوع وظيفة</p>
                </a>
            </li>
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
<li class="nav-link"><a href="#" class="nav-item dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">خروج</a></li>                </ul>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
                </form>
                
              <li class="separator d-lg-none"></li>
            </ul>
          </div>
        </div>
      </nav>
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