@extends('layouts.front')
@section('content')
<body class="index-page">
@include('front.navbar')
  <!-- End Navbar -->
  <div class="wrapper">
    <div class="page-header header-filter">
      <div class="squares square1"></div>
      <div class="squares square2"></div>
      <div class="squares square3"></div>
      <div class="squares square4"></div>
      <div class="squares square5"></div>
      <div class="squares square6"></div>
      <div class="squares square7"></div>
      <div class="container">
        <div class="content-center brand">
          <h1 class="h1-seo">Sesh Tnw•</h1>
          <h3>A beautiful Social System.</h3>
        </div>
      </div>
    </div>
    <div class="main">

      <!-- Sart Modal -->
      <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header justify-content-center">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="tim-icons icon-simple-remove"></i>
              </button>
              <h4 class="title title-up">Modal title</h4>
            </div>
            <div class="modal-body">
              <p>Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts. Separated they live in Bookmarksgrove right at the coast of the Semantics, a large language ocean. A small river named Duden flows by their place and supplies it with the necessary regelialia. It is a paradisematic country, in which roasted parts of sentences fly into your mouth.
              </p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default">Nice Button</button>
              <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>
      <!--  End Modal -->
      <!-- Mini Modal -->
      <div class="modal fade modal-mini modal-primary modal-mini" id="myModal1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header justify-content-center">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="tim-icons icon-simple-remove text-white"></i>
              </button>
              <div class="modal-profile">
                <i class="tim-icons icon-single-02"></i>
              </div>
            </div>
            <div class="modal-body">
              <p>Always have an access to your profile</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-link btn-neutral">Back</button>
              <button type="button" class="btn btn-link btn-neutral" data-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>
      <!-- Form Modal -->
      <div class="modal fade modal-black" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header justify-content-center">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="tim-icons icon-simple-remove text-white"></i>
              </button>
              <div class="text-muted text-center ml-auto mr-auto">
                <h3 class="mb-0">Sign in with</h3>
              </div>
            </div>
            <div class="modal-body">
              <div class="btn-wrapper text-center">
                <a href="#" class="btn btn-neutral btn-icon">
                  <img src="assets/img/github.svg">
                </a>
                <a href="#" class="btn btn-neutral btn-icon">
                  <img src="assets/img/google.svg">
                </a>
              </div>
              <div class="text-center text-muted mb-4 mt-3">
                <small>Or sign in with credentials</small>
              </div>
              <form role="form">
                <div class="form-group mb-3">
                  <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                      <span class="input-group-text">
                        <i class="tim-icons icon-email-85"></i>
                      </span>
                    </div>
                    <input class="form-control" placeholder="Email" type="email">
                  </div>
                </div>
                <div class="form-group">
                  <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                      <span class="input-group-text">
                        <i class="tim-icons icon-key-25"></i>
                      </span>
                    </div>
                    <input class="form-control" placeholder="Password" type="password">
                  </div>
                </div>
                <div class="form-check mt-3">
                  <label class="form-check-label">
                    <input class="form-check-input" type="checkbox" checked>
                    <span class="form-check-sign"></span>
                    Remember me!
                  </label>
                </div>
                <div class="text-center">
                  <button type="button" class="btn btn-primary my-4">Sign in</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <!--  End Modal -->
    </div>
 @endsection