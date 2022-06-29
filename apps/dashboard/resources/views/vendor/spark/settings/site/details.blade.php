<div class="card card-default">
    <div class="card-body">
        <div class="d-flex w-100 justify-content-between">
            <div class="media">
                @if( $site->photo_url != "" )
                    <img class="mr-3" src="{{ $site->photo_url }}" alt="" width="64" />
                @endif
                <div class="media-body">
                    <h2>{{ $site->name }} #{{ $site->id }}</h2>
                    <div class="card-subtitle mb-2 text-muted">
                        {{$site->auth_server_url}}
                    </div>
                </div>
            </div>
            <div class="small">
                <span>{{ $site->created_at->diffForHumans() }}</span>
            </div>
        </div>
        <p class="card-text">{{ $site->description }}</p>
        <ul class="list-inline small">
            @if(!$plugin_installed)
                <li class="list-inline-item">
                    <a class="btn btn-sm btn-outline-secondary" onclick="javascript:opnStoreSite();" href="#">{{ __('Install Plugin') }}</a>
                </li>
            @else
                @if($token_exists)
                    @if($token_expired)
                        <li class="list-inline-item">
                            <a class="btn btn-sm btn-outline-secondary" onclick="javascript:opnReAuthPage();" href="#">{{ __('Re-authorize') }}</a>
                        </li>
                    @endif
                @else
                    <li class="list-inline-item">
                        <a class="btn btn-sm btn-outline-secondary" onclick="javascript:opnAuthPage();" href="#">{{ __('Authorize') }}</a>
                    </li>
                @endif
            @endif
            <li class="list-inline-item">
                <a class="btn btn-sm btn-outline-info" href="/settings/sites/{{ $site->id}}">{{ __("Settings") }}</a>
            </li>
            <li class="list-inline-item">
                <a
                    class="btn btn-sm btn-outline-danger"
                    href="#"
                    onclick="
                    var result = confirm('Are you sure you wish to delete this Site?');
                        if( result ){
                            event.preventDefault();
                            document.getElementById('delete-form').submit();
                        }
                    ">
                    {{__('Delete')}}
                </a>
                <form id="delete-form" action="{{ route('sites.destroy',[$site->id]) }}" method="POST" style="display: none;">
                    {{ csrf_field() }}
                    <input type="hidden" name="_method" value="delete">
                </form>
            </li>
        </ul>
    </div>
</div>

<div class="card card-default">
    <div class="list-group list-group-flush">
        <div class="list-group-item">
            <span>{{__('Belong to')}}</span>
            <a href="/settings/companies/{{ $company->id }}">{{ $company->name }}</a>
        </div>
        <div class="list-group-item">
            <span>
                @if(!$plugin_installed)
                    {{__('Plugin is not installed at your WordPress site.')}}
                @else
                    {{__('Plugin is installed on your WordPress site.')}}
                @endif
            </span>
        </div>
        <div class="list-group-item">
            <span>{{__('Your WordPress version should be 4.7 or above')}}</span>
        </div>
    </div>
</div>

<script type="text/javascript">
    function opnAuthPage() {
        window.open("{{ url('/oauth-site/') }}" + "/" + "{{ $site->id }}", 'Authorize Site', 'width=600,height=500,scrollbars=yes');
    }

    function opnReAuthPage() {
        window.open("{{ url('/re-oauth-site/') }}" + "/" + "{{ $site->id }}", 'Re-Authorize Site', 'width=600,height=500,scrollbars=yes');
    }

    function opnStoreSite() {
        win = window.open("{{ $site->auth_server_url }}" + "/wp-admin/plugin-install.php?tab=plugin-information&plugin=wpdrift-io-worker&TB_iframe=true", 'Install WPdrift IO – Worker plugin', 'width=600,height=600,scrollbars=yes');
        var timer = setInterval(function() {
            if (win.closed) {
                clearInterval(timer);
                window.location.href = "{{ url('/settings/sites/') }}" + "/" + "{{ $site->id }}";
            }
        }, 1000);
    }
</script>
