@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-envelope-plus"></i> Nouveau compte de messagerie</h1>
        <a href="{{ route('settings.email-accounts.index') }}" class="btn btn-outline-secondary">Retour</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('settings.email-accounts.store') }}">
                @csrf
                @include('settings.email-accounts._form', ['account' => null])
                <div class="mt-4">
                    <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Créer le compte</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
