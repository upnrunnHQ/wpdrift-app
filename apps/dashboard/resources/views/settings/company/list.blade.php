<div class="card card-default">
    <div class="card-header">
        {{__('Companies')}}
    </div>
    <div class="list-group list-group-flush">
        @foreach($companies as $company)
        <div class="list-group-item list-group-item-action flex-column align-items-start">
            <div class="d-flex w-100 justify-content-between">
                <h5 class="mb-1">{{ $company->name }} #{{ $company->id }}</h5>
                <small>{{ $company->created_at->diffForHumans() }}</small>
            </div>
            <p class="mb-1">{{ $company->description }}</p>
            <ul class="list-inline">
                <li class="list-inline-item">
                    <small><a href="/settings/companies/{{ $company->id }}">Settings</a></small>
                </li>
            </ul>
        </div>
        @endforeach
    </div>

    <div class="card-body">
        <a href="/settings/companies/create" class="card-link">Add new</a>
    </div>
</div>
