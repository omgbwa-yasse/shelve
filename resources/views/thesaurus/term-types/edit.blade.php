@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mt-4">Modifier le type de terme</h1>
    <form action="{{ route('term-types.update', $termType->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="code" class="form-label">Code:</label>
            <input type="text" name="code" id="code" class="form-control" value="{{ $termType->code }}" required>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Nom:</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $termType->name }}" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description:</label>
            <textarea name="description" id="description" class="form-control">{{ $termType->description }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </form>
</div>
@endsection
