@include('partials.success')
@if($default_store == $store['id'])
<div class="card store active">
@else
<div class="card store">
@endif
    <div class="card-body">
        <div class="d-flex w-100 justify-content-between">
            <h1>
                @if( $store['photo_url'] != "" )
                  <img src="{{ $store['photo_url'] }}" alt="" border="0" width="48" />
                @endif
                {{ $store['name'] }} #{{ $store['id'] }}
            </h1>
            <small class="mr-4">{{ $store['created_at'] }}</small>
        </div>
        <p class="lead">
          {{ $store['description'] }}
          <br /><a href="{{ $store['auth_server_url'] }}" target="_blank">{{ $store['auth_server_url'] }}</a>
          <p>Company: <a href="/settings/companies/{{ $company->id }}">{{ $company->name }}</a>
        </p>
        <button class="btn btn-danger"
            onclick="
            var result = confirm('Are you sure you wish to delete this Site?');
                if( result ){
                        event.preventDefault();
                        document.getElementById('delete-form').submit();
                }
                    "
                    >
            Delete
        </button>
        <form id="delete-form" action="{{ route('sites.destroy',[$store['id']]) }}" method="POST" style="display: none;">
            {{ csrf_field() }}
            <input type="hidden" name="_method" value="delete">
        </form>
    </div>
</div>

<div class="card card-default">
    <div class="card-body">
        <ul class="nav flex-column">
            <li class="nav-item">
                @if($plugin_status == "no")
                  <div class="text-danger">Error: Plugin is not installed at your WordPress site.</div>
                @endif
                <strong>Note:</strong> Your WordPress version should be <b>4.7</b> or above.
                <br />
                @if($plugin_version == "")
                  Install "<b>WPdrift IO – Worker</b>" plugin to Your WordPress site:
                  <button type="button" onclick="javascript:opnStoreSite();" class="btn btn-primary">Install!</button>
                @else
                  "<b>WPdrift IO – Worker</b>" Plugin is installed on your WordPress(version {{$wp_store_details->version}}) site, version <b>{{ $plugin_version }}</b>
                @endif
            </li>
            @if( $url_valid && $plugin_version != "" )
            <li class="nav-item">
              @if($expire_token_time == "")
              <button type="button" onclick="javascript:opnAuthPage();"  class="btn btn-primary">
                <span>
                      Authorize Your Store
                </span>
              </button>
              @else
                @if(\Carbon\Carbon::now() > $expire_token_time)
                <button type="button" onclick="javascript:opnReAuthPage();"  class="btn btn-primary">
                  <span>
                        Re-Authorize Your Store
                  </span>
                </button>
                @endif
              @endif
            </li>
              @if($expire_token_time != "")
              <li class="nav-item">
              @if(\Carbon\Carbon::now() > $expire_token_time)
                Token had expired on {{ \Carbon\Carbon::parse($expire_token_time)->format('F j Y h:i:s a')}}
              @else
                Token will expiring on {{ \Carbon\Carbon::parse($expire_token_time)->format('F j Y h:i:s a')}}
              @endif
              </li>
              @endif
            @endif

            @if($expire_token_time != "")
              <li class="nav-item">
              @if(\Carbon\Carbon::now() < $expire_token_time)
                @if($edd_plugin_version != "")
                  @if( $store['edd_enabled'] == 0 )
                    <br />
                    <b>EDD Not Enabled</b> - <a class="btn btn-primary"  href="/enable_edd?id={{ $store['id'] }}">Enable EDD</a>
                  @elseif( $store['edd_enabled'] == 2 )
                    EDD: Setup In Progress, Once done, it will show as <b>EDD Enabled</b>
                  @elseif( $store['edd_enabled'] == 1 )
                    <br />
                    <b>EDD Enabled</b>, <a class="btn btn-primary" href="/disable_edd?id={{ $store['id'] }}">Disable EDD</a>
                  @endif
                @endif
              @endif
              </li>
            @endif
        </ul>
    </div>
</div>

<script type="text/javascript">
    function opnAuthPage() {
      window.open("{{ url('/oauth-site/') }}"+"/"+"{{ $store['id'] }}", 'Authorize Site', 'width=600,height=500,scrollbars=yes');
    }
    function opnReAuthPage() {
      window.open("{{ url('/re-oauth-site/') }}"+"/"+"{{ $store['id'] }}", 'Re-Authorize Site', 'width=600,height=500,scrollbars=yes');
    }
    function opnStoreSite() {
      win = window.open("{{ $store['auth_server_url'] }}"+"/wp-admin/plugin-install.php?tab=plugin-information&plugin=wpdrift-io-worker&TB_iframe=true", 'Install WPdrift IO – Worker plugin', 'width=600,height=600,scrollbars=yes');
      var timer = setInterval(function() {
        if(win.closed) {
            clearInterval(timer);
            window.location.href =   "{{ url('/settings/sites/') }}" + "/" + "{{ $store['id'] }}";
        }
    }, 1000);
    }
    function opnEddSetupPage() {
      window.open("{{ url('/enable_edd/') }}", 'Enable Edd', 'width=600,height=500,scrollbars=yes');
    }
</script>
