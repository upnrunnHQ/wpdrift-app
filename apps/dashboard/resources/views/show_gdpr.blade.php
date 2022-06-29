@extends('spark::layouts.app')
@section('title', 'GDPR Download |')
@section('content')
<div class="app-dashboard">
    @include('partials.side-nav')
    <div class="main">
      <div class="">
          <div class="row">
            <div class="col-md-5">
              <div class="card">
                <div class="card-header">
                  <div class="float-left">Download GDPR</div>
                </div>
                <div class="card-body">
                  <form method="post" action="/gdpr/download" enctype="multipart/form-data">
                      @csrf
                      <div class="form-group">
                          <label for="user-password">Password</label>
                          <input placeholder="Enter password"
                                    id="user-password"
                                    required
                                    name="password"
                                    spellcheck="false"
                                    class="form-control"
                                    type="password"
                                     />
                      </div>
                      <div class="form-group">
                          <input type="submit" class="btn btn-primary"
                                 value="Submit"/>
                      </div>
                  </form>
                </div>
            </div>
          </div>
      </div><!-- .container -->
    </div><!-- .main -->
</div><!-- .app-dashboard -->
@endsection
