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
                            <a class="nav-link" href="/settings/companies">
                                {{__('Companies')}}
                            </a>
                        </li>
                    </ul>
                </aside>
            </div>

            <!-- Tab cards -->
            <div class="col-md-9">
                <div class="card card-default">
                    <div class="card-header">{{__('Add company')}}</div>
                    <div class="card-body">
                        <form method="post" action="{{ route('companies.store') }}">
                            {{ csrf_field() }}
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label text-md-right" for="company-name">
                                    Name <span class="required text-danger">*</span>
                                </label>
                                <div class="col-md-6">
                                    <input placeholder="Enter name"
                                    id="company-name"
                                    required
                                    name="name"
                                    spellcheck="false"
                                    class="form-control"/>
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
                                    </textarea>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-4"></div>
                                <div class="col-md-6">
                                    <input type="submit" class="btn btn-primary"value="Submit"/>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</spark-settings>
@endsection
