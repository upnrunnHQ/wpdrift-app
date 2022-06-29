@extends('layouts.popup')
@section('title', 'Authorization Store | ' . config('app.name'))
@section('content')
<div class="container">
    <!-- Application Dashboard -->
    <div class="row justify-content-center">
        <div class="col-md-8">
            @if ($error)
                <div class="alert alert-danger"><strong>Error</strong>: {{ $error }}</div>
                <div class="alert alert-info">Contact site Administrator</div>
            @else
            <div class="card card-default">
                <div class="card-header">{{__('Store Authorization')}}</div>
                <div class="card-body text-success">
                    {{__('Your Store authorized successfully.')}}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
@if ($error == "")
    @section('javascript')
    <script type="text/javascript">
    window.opener.location.reload(true);
    </script>
    @endsection
@endif