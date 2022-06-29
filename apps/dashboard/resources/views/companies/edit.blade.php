@extends('spark::layouts.app')
@section('title', 'Edit Company |')
@section('content')
<div class="io-dashboard">
    @include('partials.side-nav')
    @include('layouts.offcanvas')
    <div class="main">
      <div class="container">
        <div class="row">
          <div class=" col-md-8 col-lg-8 col-sm-8 pull-left ">
            <div class="col-md-12 col-lg-12 col-sm-12" >
              <div class="card">
                <div class="card-header">
                  <div class="float-left">Update company</div>
                  <div class="float-right">
                    <ol class="list-unstyled">
                      <li>
                        <a href="/settings/companies/{{ $company->id }}"><i class="fa fa-building-o" aria-hidden="true"></i>
                       View Company</a>
                      </li>
                      <li>
                        <a href="/settings/companies"><i class="fa fa-building" aria-hidden="true"></i> All companies</a>
                      </li>
                    </ol>
                  </div>
                </div>
                <div class="card-body">
                  <form method="post" action="{{ route('companies.update',[$company->id]) }}">
                    {{ csrf_field() }}
                    <input type="hidden" name="_method" value="put">
                    <div class="form-group row">
                      <label class="col-md-4 col-form-label text-md-right" for="company-name">Name<span class="required text-danger">*</span></label>
                      <div class="col-md-6">
                        <input placeholder="Enter name"
                             id="company-name"
                             required
                             name="name"
                             spellcheck="false"
                             class="form-control"
                             value="{{ $company->name }}"
                        />
                        {!! $errors->first('name', '<small class="text-danger">:message</small>') !!}
                      </div>
                    </div>
                    <div class="form-group row">
                      <label class="col-md-4 col-form-label text-md-right" for="company-content">Description</label>
                      <div class="col-md-6">
                      <textarea placeholder="Enter description"
                            style="resize: vertical"
                            id="company-content"
                            name="description"
                            rows="5" spellcheck="false"
                            class="form-control autosize-target text-left">
                            {{ $company->description }}</textarea>
                      </div>
                    </div>
                    <div class="form-group row">
                      <div class="col-md-4"></div>
                      <div class="col-md-6">
                        <input type="submit" class="btn btn-primary"
                          value="Submit"/>
                      </div>
                    </div>
                  </form>
                </div>{{-- .card-body --}}
              </div>{{-- .card --}}
            </div>{{-- .col-md-12 col-lg-12 col-sm-12 --}}
          </div>{{-- .col-md-8 col-lg-8 col-sm-8 pull-left --}}
      </div><!-- .container -->
    </div><!-- .main -->
  </div><!-- .app-dashboard -->
</div>
@endsection
