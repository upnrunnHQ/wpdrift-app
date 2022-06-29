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
                            <a class="nav-link active" href="#list" aria-controls="list" role="tab" data-toggle="tab">
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
                    <div role="tabcard" class="tab-pane active" id="list">
                        @include('settings.site.list')
                    </div>
                </div>
            </div>
        </div>
    </div>
</spark-settings>
@endsection
