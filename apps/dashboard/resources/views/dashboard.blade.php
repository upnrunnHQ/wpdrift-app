@extends('spark::layouts.app')

@section('content')
    <io-dashboard :user="user" :teams="teams" :current-team="currentTeam" inline-template>
        <div class="io-dashboard">
            <!-- App sidebar -->
            @include('layouts.sidebar')

            <!-- App main area -->
            @include('layouts.main')

            <!-- App offcanvas -->
            @include('layouts.offcanvas')
        </div>
    </io-dashboard>
@endsection
