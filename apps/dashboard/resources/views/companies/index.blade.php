@extends('spark::layouts.app')
@section('title', 'Companies |')
@section('content')
<div class="io-dashboard">
    @include('partials.side-nav')
    @include('layouts.offcanvas')
    <div class="main">
      <div class="container">
      <!-- Application Dashboard -->
      @include('partials.success')
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="mb-4">
          <h2>Companies</h2>
          <a  class="pull-right btn btn-primary btn-sm" href="/settings/companies/create">
            <i class="fa fa-plus-square" aria-hidden="true"></i>  Create new</a>
        </div>
        <div class="row">
          @foreach($companies as $company)
          <div class="col-md-4">
              <div class="card">
                  <div class="card-header">
                    <div class="float-left">
                        <h4 class="card-title"><a href="/settings/companies/{{ $company->id }}" >{{ $company->name }}</a> #{{ $company->id }}</h4>
                    </div>
                    <div class="float-right">
                      <a href="/settings/companies/{{ $company->id }}/edit">Edit</a>
                    </div>
                  </div>
                  <div class="card-body">
                      <p class="card-text">{{ $company->description }}</p>
                      <small>Created {{ $company->created_at->diffForHumans() }} </small>
                  </div>
              </div>
          </div>
          @endforeach
        </div>
      </div><!-- .container -->
    </div><!-- .main -->
</div><!-- .app-dashboard -->
@endsection
