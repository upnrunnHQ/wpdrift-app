@extends('spark::layouts.app')
@section('content')
<spark-settings :user="user" :teams="teams" inline-template>
    <div class="spark-screen container">
        <div class="row">
            <!-- Tabs -->
            <div class="col-md-3 spark-settings-tabs">
                <aside>
                    <h3 class="nav-heading ">
                        {{__('Settings')}}
                    </h3>

                    <ul class="nav flex-column mb-4 ">
                        <li class="nav-item ">
                            <a class="nav-link active" href="#details" aria-controls="details" role="tab" data-toggle="tab">
                                {{__('Details')}}
                            </a>
                        </li>

                        <li class="nav-item ">
                            <a class="nav-link" href="#update" aria-controls="update" role="tab" data-toggle="tab">
                                {{__('Update')}}
                            </a>
                        </li>
                    </ul>
                </aside>

                <aside>
                    <h3 class="nav-heading ">
                        {{__('More')}}
                    </h3>

                    <ul class="nav flex-column mb-4 ">
                        <li class="nav-item ">
                            <a class="nav-link" href="#list" aria-controls="list" role="tab" data-toggle="tab">
                                {{__('Sites')}}
                            </a>
                        </li>
                    </ul>
                </aside>
            </div>

            <!-- Tab cards -->
            <div class="col-md-9">
                <div class="tab-content">
                    <!-- Details -->
                    <div role="tabcard" class="tab-pane active" id="details">
                        @if ( $url_valid )
                            @include('settings.site.details')
                        @else
                            @include('settings.site.brif')
                        @endif
                    </div>

                    <!-- Update -->
                    <div role="tabcard" class="tab-pane" id="update">
                        @include('settings.site.update')
                    </div>

                    <!-- Sites -->
                    <div role="tabcard" class="tab-pane" id="list">
                        @include('settings.site.list')
                    </div>
                </div>
            </div>
        </div>
    </div>
</spark-settings>
@endsection
