@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1><i class="bi bi-trash"></i> Modifier la liste {{ $declassementList->code }}</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('declassement-lists.update', $declassementList) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Nom</label>
                <input type="text" name="name" class="form-control" maxlength="200" required value="{{ old('name', $declassementList->name) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $declassementList->description) }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Enregistrer</button>
            <a href="{{ route('declassement-lists.show', $declassementList) }}" class="btn btn-outline-secondary">Annuler</a>
        </form>
    </div>
@endsection
