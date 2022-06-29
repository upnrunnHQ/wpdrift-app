<div class="card card-default">
    <div class="card-header">
        {{__('Sites')}}
    </div>
    <div class="list-group list-group-flush">
        @foreach($sites as $site)
        @if($site->id == $default_store)
        <div class="list-group-item list-group-item-action flex-column align-items-start bg-light">
        @else
        <div class="list-group-item list-group-item-action flex-column align-items-start">
        @endif
            <div class="d-flex w-100 justify-content-between">
                <h5 class="mb-1">{{ $site->name }} #{{ $site->id }}</h5>
                <small>{{ $site->created_at->diffForHumans() }}</small>
            </div>
            <p class="mb-1">{{ $site->description }}</p>
            <ul class="list-inline">
                <li class="list-inline-item">
                    <small><a href="/settings/sites/{{ $site->id}}">{{__('Settings')}}</a></small>
                </li>
                @if($site->id != $default_store)
                    @if($site->companies_store_credentials != "")
                        <li class="list-inline-item">
                            <small><a href="/set-default-site/{{ $site->id }}">{{__('Activate')}}</a></small>
                        </li>
                    @endif
                @endif
            </ul>
        </div>
        @endforeach
    </div>

    <div class="card-body">
        <a href="/settings/sites/create" class="card-link">{{__('Add new')}}</a>
    </div>
</div>
