@extends('layouts.popup')
@section('title', 'Edd Setup | ' . config('app.name'))
@section('content')
<div class="container">
    <!-- Application Dashboard -->
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-default">
                <div class="card-header">{{__('Store Edd Setup')}}</div>
                <div class="card-body text-success">
                    {{$message}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@if ($message != "")
    @section('javascript')
    <script type="text/javascript">
    window.opener.location.reload(true);
    </script>
    @endsection
@endif