  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg fixed-top navbar-transparent " color-on-scroll="100">
    <div class="container">
      <div class="navbar-translate">
        <a class="navbar-brand" href="/" rel="tooltip" title="Coded by Afraym" data-placement="bottom">
          <span>ST•</span> {{ __('ui.system_name') }}
        </a>
        <button class="navbar-toggler navbar-toggler" type="button" data-toggle="collapse" data-target="#navigation" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-bar bar1"></span>
          <span class="navbar-toggler-bar bar2"></span>
          <span class="navbar-toggler-bar bar3"></span>
        </button>
      </div>
      <div class="collapse navbar-collapse justify-content-end" id="navigation">
        <div class="navbar-collapse-header">
          <div class="row">
            <div class="col-6 collapse-brand">
              <a>
                ST•
              </a>
            </div>
            <div class="col-6 collapse-close text-right">
              <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navigation" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
                <i class="tim-icons icon-simple-remove"></i>
              </button>
            </div>
          </div>
        </div>
        <ul class="navbar-nav">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="frontLocaleDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="tim-icons icon-world"></i> {{ strtoupper(app()->getLocale()) }}
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="frontLocaleDropdown">
              <a class="dropdown-item" href="{{ route('locale.switch', ['locale' => 'ar']) }}">{{ __('ui.arabic') }}</a>
              <a class="dropdown-item" href="{{ route('locale.switch', ['locale' => 'en']) }}">{{ __('ui.english') }}</a>
            </div>
          </li>
          <li class="nav-item p-0">
            <a class="nav-link" rel="tooltip" title="Follow us on Twitter" data-placement="bottom" href="https://twitter.com/afraymng" target="_blank">
              <i class="fab fa-twitter"></i>
              <p class="d-lg-none d-xl-none">{{ __('ui.twitter') }}</p>
            </a>
          </li>
          <li class="nav-item p-0">
            <a class="nav-link" rel="tooltip" title="Like us on Facebook" data-placement="bottom" href="https://www.facebook.com/afraymng" target="_blank">
              <i class="fab fa-facebook-square"></i>
              <p class="d-lg-none d-xl-none">{{ __('ui.facebook') }}</p>
            </a>
          </li>
          <li class="nav-item p-0">
            <a class="nav-link" rel="tooltip" title="Follow us on Instagram" data-placement="bottom" href="https://www.instagram.com/afraymng" target="_blank">
              <i class="fab fa-instagram"></i>
              <p class="d-lg-none d-xl-none">{{ __('ui.instagram') }}</p>
            </a>
          </li>
          <li class=" nav-item">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('login') }}">
                <i class="tim-icons icon-single-02"></i> {{ __('ui.login') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('register') }}">
                <i class="tim-icons icon-laptop"></i> {{ __('ui.register') }}
                </a>
            </li>

        </ul>
      </div>
    </div>
  </nav>