@extends('spark::layouts.app')
@section('title', $company->name.' |')
@section('content')
<div class="io-dashboard">
    @include('partials.side-nav')
    @include('layouts.offcanvas')
    <div class="main">
      <div class="container">
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

        <div class="">
            <div class="row">
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-body">
                            <h1>{{ $company->name }} #{{ $company->id }}</h1>
                            <p class="lead">{{ $company->description }}</p>
                            <small>Created at {{ $company->created_at }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-7">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="/settings/companies/{{ $company->id }}/edit">Edit</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/settings/sites/create">Add Site</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/settings/companies">My Companies</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/settings/companies/create">Create new Company</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div>
            <h6>Sites</h6>
            <div class="row">
              @foreach($companies_stores as $store)
              <div class="col-md-3">
                @if($store->id == $default_store)
                <div class="card store active">
                @else
                <div class="card store">
                @endif
                  <div class="card-body">
                        <div class="mb-3">
                            <h4 class="card-title">
                              @if($store->photo_url != "")
                              <img src="{{ $store->photo_url }}" alt="" border="0" width="32" />
                              @endif
                               {{ $store->name }} #{{ $store->id }}
                            </h4>
                            <p class="lead">{{ $store->description }}</p>
                            <small>Created at {{ $store->created_at->diffForHumans() }}</small>
                        </div>
                        <div class="">
                            @if($store->id != $default_store)
                              @if($store->companies_store_credentials != "")
                                <a class="btn btn-block btn-sm btn-primary" href="/set-default-site/{{ $store->id }}">Switch to {{ $store->name }} >></a>
                              @endif
                            @endif
                            <a class="btn btn-block btn-sm btn-secondary" href="/settings/sites/{{ $store->id}}">View Site Details</a>
                        </div>
                    </div>
                </div>
              </div>
              @endforeach
            </div>
        </div>
    </div><!-- .container -->
  </div><!-- .main -->
</div><!-- .app-dashboard -->
@endsection
