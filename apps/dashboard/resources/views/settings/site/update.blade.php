<div class="card card-default">
    <div class="card-header">{{__('Update Details')}}</div>
    <div class="card-body">
        <form method="post" action="{{ route('sites.update',[$store['id']]) }}" enctype="multipart/form-data">
            {{ csrf_field() }}
            <input type="hidden" name="_method" value="put">
            <div class="form-group row">
                <label class="col-md-4 col-form-label text-md-right" for="store-name">Name <span class="text-danger">*</span></label>
                <div class="col-md-6">
                    <input placeholder="Enter name"
                         id="store-name"
                         required
                         name="name"
                         spellcheck="false"
                         class="form-control"
                         value="{{ $store['name'] }}"/>
                    {!! $errors->first('name', '<small class="text-danger">:message</small>') !!}
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-4 col-form-label text-md-right" for="store-url">URL <span class="text-danger">*</span></label>
                <div class="col-md-6">
                    <input placeholder="Enter URL"
                        id="store-url"
                        required
                        name="auth_server_url"
                        spellcheck="false"
                        class="form-control"
                        value="{{ $store['auth_server_url'] }}"/>
                    <small>Please include <i>http://</i> OR <i>https://</i></small>
                    <br />{!! $errors->first('auth_server_url', '<small class="text-danger">:message</small>') !!}
                    @if($store['auth_server_url'] != "")
                        @if(!\Helpers::get_url_response($store['auth_server_url']))
                            <br /><span class="text-danger">(Error: Your url is not valid site. Please update with actual site URL.)</span>
                        @endif
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-4 col-form-label text-md-right" for="store-photo">Logo</label>
                <div class="col-md-6">
                    <input id="store-photo"
                        name="store_photo"
                        class="form-control"
                        type="file"/>
                    <small>allowed extensions: <i>jpeg, png, jpg, gif</i> AND Image size max: <i>2 MB</i></small>
                    <br />{!! $errors->first('store_photo', '<small class="text-danger">:message</small>') !!}
                    @if($store['photo_url'] != "")
                        <p>
                            <img src="{{ $store['photo_url'] }}" alt="store logo" width="150" border="0" />
                        </p>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label class="col-md-4 col-form-label text-md-right" for="select-company">Company</label>
                <div class="col-md-6">
                    <a href="/settings/companies/{{ $company->id }}">{{ $company->name }}</a></label>
                    @if($companies_store_count < 21)
                        <div class="panel-heading" style="margin-bottom: 10px;">
                            <a class="pull-right btn btn-primary btn-sm" href="/settings/companies/create">
                            <i class="fa fa-plus-square" aria-hidden="true"></i>  Add Company</a>
                        </div>
                    @else
                        <div class="text-danger">You are having more than <strong>20 Companies</strong>, To add more contact Admin.</div>
                    @endif

                    {{--@if($companies_store_count > 0)
                        <select name="company-id" id="company-id">
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    @endif--}}
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
                        class="form-control autosize-target text-left">{{ $store['description'] }}</textarea>
                    {!! $errors->first('description', '<small class="text-danger">:message</small>') !!}
                </div>
            </div>

            <div class="form-group row">
                <div class="col-md-4"></div>
                <div class="col-md-6">
                    <input type="submit" class="btn btn-primary" value="Submit"/>
                </div>
            </div>
        </form>
    </div>
</div>
