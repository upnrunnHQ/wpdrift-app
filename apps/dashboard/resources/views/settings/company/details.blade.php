@include('partials.success')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <h1>{{ $company->name }} #{{ $company->id }}</h1>
        <p class="lead">{{ $company->description }}</p>
        <small>Created at {{ $company->created_at }}</small>
    </div>
</div>

@include('settings.site.list')
