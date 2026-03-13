@extends('layouts.front')

@section('content')
<style>
  body.register-page .page-header {
    background:
      radial-gradient(circle at 12% 20%, rgba(255, 239, 163, 0.85) 0 9%, rgba(255, 239, 163, 0) 24%),
      linear-gradient(180deg, #082a60 0%, #0c4f8f 50%, #f3b24b 100%);
  }

  body.register-page .page-header .page-header-image {
    background:
      radial-gradient(circle at 78% 12%, rgba(255, 212, 114, 0.4) 0 16%, rgba(255, 212, 114, 0) 36%),
      linear-gradient(150deg, rgba(10, 56, 108, 0.52), rgba(6, 27, 62, 0.18));
    opacity: 0.95;
  }

  body.register-page .register-bg {
    background: linear-gradient(180deg, rgba(7, 37, 78, 0.32) 0%, rgba(8, 53, 102, 0.16) 45%, rgba(244, 177, 67, 0.24) 100%);
    border-radius: 24px;
  }

  body.register-page .square {
    background: linear-gradient(145deg, rgba(80, 183, 255, 0.26), rgba(255, 197, 96, 0.26));
    border: 1px solid rgba(255, 231, 186, 0.22);
    box-shadow: 0 10px 22px rgba(7, 24, 55, 0.22);
  }
</style>

<body class="register-page">
    @include('front.navbar')
    <div class="page-header">
      <div class="page-header-image"></div>
      <div class="content">
        <div class="container">
          <div class="row">
            <div class="col-lg-5 col-md-6 offset-lg-0 offset-md-3">
              <div id="square7" class="square square-7"></div>
              <div id="square8" class="square square-8"></div>
              <div class="card card-register">
                <div class="card-header">
                  <img class="card-img" src="{{ asset('assets/img/square1.png') }}" alt="Card image">
                  <h4 class="card-title">دخول</h4>
                </div>
                <div class="card-body">
                  <form class="form" method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <div class="input-group-text">
                          <i class="tim-icons icon-email-85"></i>
                        </div>
                      </div>
                      <input type="text" placeholder="رقم الهاتف او الايميل" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email,phone">
                    </div>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <div class="input-group-text">
                          <i class="tim-icons icon-lock-circle"></i>
                        </div>
                      </div>
                      <input type="password" class="form-control @error('password') is-invalid @enderror" placeholder="كلمة المرور" name="password" required autocomplete="current-password">
                    </div>
                    <div class="form-check text-left">
                      <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span class="form-check-sign"></span>
                        تذكرني.
                      </label>
                    </div>
                  
                </div>
                <div class="card-footer" type="submit">
                  <button type="submit" class="btn btn-info btn-round btn-lg">دخول </button>
                  </form>
                  @if (Route::has('password.request'))
                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                        {{ __('هل نسيت كلمة المرور؟') }}
                                    </a>
                                @endif
                </div>
              </div>
            </div>
          </div>
          <div class="register-bg"></div>
          <div id="square1" class="square square-1"></div>
          <div id="square2" class="square square-2"></div>
          <div id="square3" class="square square-3"></div>
          <div id="square4" class="square square-4"></div>
          <div id="square5" class="square square-5"></div>
          <div id="square6" class="square square-6"></div>
        </div>
      </div>
    </div>
@endsection
