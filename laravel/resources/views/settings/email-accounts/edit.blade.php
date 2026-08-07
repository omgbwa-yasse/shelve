@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-envelope-gear"></i> Modifier {{ $emailAccount->name }}</h1>
        <div class="d-flex gap-2">
            <form action="{{ route('settings.email-accounts.test-connection', $emailAccount) }}" method="POST">
                @csrf
                <button class="btn btn-outline-secondary"><i class="bi bi-plug"></i> Tester la connexion</button>
            </form>
            <a href="{{ route('settings.email-accounts.index') }}" class="btn btn-outline-secondary">Retour</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('settings.email-accounts.update', $emailAccount) }}">
                @csrf
                @method('PUT')
                @include('settings.email-accounts._form', ['account' => $emailAccount])
                <div class="mt-4">
                    <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
