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

<div class="card card-defaault">
    <div class="card-body">
        <p class="card-text">Your url is not valid site. Please update with actual site URL</p>       
    </div>
</div>
