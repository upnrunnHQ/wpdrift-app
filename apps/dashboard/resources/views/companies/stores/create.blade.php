@extends('spark::layouts.app')
@section('title', 'Create Store |')
@section('content')
<div class="io-dashboard">
    <!-- App sidebar -->
    @include('partials.side-nav')

    <div class="main">
        <div class="row">
          <div class="col-md-8 col-lg-8 col-sm-8 pull-left">
            <div class="col-md-12 col-lg-12 col-sm-12">
              <div class="card">
                <div class="card-header">
                  <div class="float-left">Add Site</div>
                  <div class="float-right"><a href="/settings/sites"> <i class="fa fa-building" aria-hidden="true"></i> My Sites</a></div>
                </div>
                <div class="card-body">
                  <form method="post" action="{{ route('sites.store') }}" enctype="multipart/form-data">
                      {{ csrf_field() }}
                      <div class="form-group row">
                          <label class="col-md-4 col-form-label text-md-right" for="store-name">
                            Name <span class="required text-danger">*</span>
                          </label>
                          <div class="col-md-6">
                            <input placeholder="Enter name"
                                      id="store-name"
                                      required
                                      name="name"
                                      spellcheck="false"
                                      class="form-control"
                                      type="text"
                                       />
                             {!! $errors->first('name', '<small class="text-danger">:message</small>') !!}
                           </div>
                      </div>
                      <div class="form-group row">
                          <label class="col-md-4 col-form-label text-md-right" for="store-url">URL <span class="required text-danger">*</span></label>
                          <div class="col-md-6">
                            <input placeholder="Enter URL"
                                      id="store-url"
                                      required
                                      name="auth_server_url"
                                      spellcheck="false"
                                      class="form-control"
                                      type="text"
                                      value="http://"
                                       /><small>Please include <i>http://</i> OR <i>https://</i></small>
                             <br />{!! $errors->first('auth_server_url', '<small class="text-danger">:message</small>') !!}
                           </div>
                      </div>
                      <div class="form-group row">
                          <label class="col-md-4 col-form-label text-md-right" for="store-photo">Logo</label>
                          <div class="col-md-6">
                            <input id="store-photo"
                                    name="store_photo"
                                    class="form-control"
                                    type="file"
                                       />
                            <small>allowed extensions: <i>jpeg, png, jpg, gif</i> AND Image size max: <i>2 MB</i></small>
                            <br />{!! $errors->first('store_photo', '<small class="text-danger">:message</small>') !!}
                          </div>
                      </div>
                      <div class="form-group row">
                          <label class="col-md-4 col-form-label text-md-right" for="select-company">Company <span class="required text-danger">*</span></label>
                          <div class="col-md-6">
                            @if($companies_store_count > 0)
                                <select name="company_id" id="company-id">
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                                </select>
                            @endif
                            {!! $errors->first('company_id', '<small class="text-danger">:message</small>') !!}
                            @if($companies_store_count < 21)
                              <div class="panel-heading" style="margin-top: 10px;">
                                <a class="pull-right btn btn-primary btn-sm" href="/settings/companies/create">
                                <i class="fa fa-plus-square" aria-hidden="true"></i>  Add Company</a>
                              </div>
                            @else
                              <div class="text-danger" style="margin-top: 10px;">You are having more than <strong>20 Companies</strong>, To add more contact Site Admin.</div>
                            @endif
                          </div>
                      </div>
                      <div class="form-group row">
                          <label class="col-md-4 col-form-label text-md-right" for="store-content">Description</label>
                          <div class="col-md-6">
                            <textarea placeholder="Enter description"
                                      style="resize: vertical"
                                      id="store-content"
                                      name="description"
                                      rows="5" spellcheck="false"
                                      class="form-control autosize-target text-left"></textarea>
                            {!! $errors->first('description', '<small class="text-danger">:message</small>') !!}
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
        </div>{{-- .row --}}
    </div>

    <!-- App offcanvas -->
    @include('layouts.offcanvas')
</div>
@endsection
