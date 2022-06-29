@extends('spark::layouts.app')
@section('title', 'My Sites |')
@section('content')
<div class="io-dashboard">
    @include('partials.side-nav')
    @include('layouts.offcanvas')
    <div class="main">
      <div class="container">
        @include('partials.success')
        <div class="mb-4">
          <h2>Sites</h2>
          <a  class="pull-right btn btn-primary btn-sm" href="/settings/sites/create">
            <i class="fa fa-plus-square" aria-hidden="true"></i>  Create new</a>
        </div>
        <div class="row">
          @foreach($stores as $store)
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
      </div><!-- .container -->
    </div><!-- .main -->
</div><!-- .app-dashboard -->
@endsection
