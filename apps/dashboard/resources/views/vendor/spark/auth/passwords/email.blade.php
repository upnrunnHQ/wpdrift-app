@extends('spark::layouts.app')

<!-- Main Content -->
@section('content')
<div class="container resetpass">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card card-default">
                <div class="card-header">{{__('Reset Password')}}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form role="form" method="POST" action="{{ url('/password/email') }}">
                        {!! csrf_field() !!}

                        <!-- E-Mail Address -->
                        <div class="form-group row{{ $errors->has('email') ? ' is-invalid' : '' }}">
                            <div class="col-md-12">
                                <input type="email" placeholder="{{__('E-Mail Address')}}" class="form-control" name="email" value="{{ old('email') }}" autofocus>

                                @if ($errors->has('email'))
                                    <span class="invalid-feedback">
                                        {{ $errors->first('email') }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Send Password Reset Link Button -->
                        <div class="form-group row mb-0">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-btn fa-envelope"></i> {{__('Send Password Reset Link')}}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
