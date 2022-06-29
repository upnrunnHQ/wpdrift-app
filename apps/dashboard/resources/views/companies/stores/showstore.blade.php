@extends('spark::layouts.app')

@section('content')
<home :user="user" inline-template>
  <div class="container">
      <!-- Application Dashboard -->
      <div class="row justify-content-center">
        <div class="col-md-12">
          {{-- <form method="post" action="{{ route('sites.store') }}"> --}}
            {{ csrf_field() }}
            <table>
              <tr><td>Add your store to WpDrift.io</td></tr>
              <tr><td>What's the name of your company?</td></tr>
              <tr><td>Company: {{ $company->name }}</td></tr>
              <tr><td>Are you sure you want to create a new company? It will have its own subscription and billing.</td></tr>
              <tr><td>What's your store's website?</td></tr>
              <tr><td>Store URL: {{ $store->auth_server_url }}</td></tr>
              <tr><td>Store: {{ $store->name }}</td></tr>
              <tr><td>Install OAuth Server plugin to Your wp site:<button type="button" onclick="javascript:opnStoreSite();">Install!</button></td></tr>
              <tr><td><button type="button" onclick="javascript:opnAuthPage();"  class="el-button add-store-button el-button--primary el-button--large"><!----><!----><span>
                      Authorize Your Store
                  </span></button></td></tr>
              <tr>
                <td>
                  @if($default_store == $store->id)
                    This is default store, already.
                  @else
                    @if($oauth_access_token != "")
                      Set this store as default store: <a href="{{ url('/add-store/?store='.$store->id.'&default=true') }}">Set Default</a>
                    @endif
                  @endif
                </td>
              </tr>
            </table>
          {{-- </form> --}}
        </div>
      </div><!-- .justify-content-center -->
    </div><!-- .container -->
  </home>
  @endsection
@section('javascript')
<script type="text/javascript">
function opnAuthPage() {
  window.open("{{ url('/oauth-site/') }}"+"/"+"{{ $store->id }}", 'Authorize Store', 'width=600,height=500,scrollbars=yes');
}
function opnStoreSite() {
  window.open("{{ $store->auth_server_url }}"+"/wp-admin/plugin-install.php?tab=upload", 'Install oAuth Server plugin', 'width=800,height=700,scrollbars=yes');
}
</script>
@endsection
