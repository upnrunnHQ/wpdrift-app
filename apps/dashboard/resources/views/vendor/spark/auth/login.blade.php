@extends('spark::layouts.app')
@section('title', 'Login |')
@section('content')
<div class="container login">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card card-default">
                <div class="card-header">{{__('Login')}}</div>

                <div class="card-body">
                    @include('spark::shared.errors')

                    <form role="form" method="POST" action="/login">
                        {{ csrf_field() }}

                        <!-- E-Mail Address -->
                        <div class="form-group row">
                            <div class="col-md-12">
                                <input type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="{{__('E-Mail')}}" autofocus>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="form-group row">
                            <div class="col-md-12">
                                <input type="password" class="form-control" name="password" placeholder="{{__('Password')}}" >
                            </div>
                        </div>

                        <!-- Remember Me -->
                        <div class="form-group row">
                            <div class="col-md-10">
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="checkbox" name="remember" class="form-check-input"> {{__('Remember Me')}}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Login Button -->
                        <div class="form-group row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    {{__('Login')}}
                                </button>
                            </div>
                        </div>

                        <!-- Reset and Sigup -->
                        <div class="form-group row mb-0 lnks">
                            <div class="col-md-8 txt-lft pdl-0">
                                <a class="btn btn-link" href="{{ url('/password/reset') }}">{{__('Forgot Your Password?')}}</a>
                            </div>
                            <div class="col-md-4 txt-rgt pdr-0">
                                <a class="btn btn-link" href="{{ url('/register') }}">{{__('Register')}}</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
