@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="bi bi-pencil"></i> Modifier {{ $record->code }} — {{ $record->name }}</h1>
            <a href="{{ route('records.show', $record) }}" class="btn btn-outline-secondary">Retour</a>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('records.update', $record) }}">
                    @csrf
                    @method('PUT')
                    @include('records.partials.form-fields', ['record' => $record])
                    <div class="mt-4">
                        <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
