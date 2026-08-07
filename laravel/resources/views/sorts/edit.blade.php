@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Sort</h1>
        <form action="{{ route('sorts.update', $sort->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="code">Code</label>
                <select name="code" id="code" class="form-control" required>
                    <option value="">Sélectionner un code</option>
                    <option value="E" {{ $sort->code == 'E' ? 'selected' : '' }}>E - Élimination</option>
                    <option value="T" {{ $sort->code == 'T' ? 'selected' : '' }}>T - Tri/Transfert</option>
                    <option value="C" {{ $sort->code == 'C' ? 'selected' : '' }}>C - Conservation</option>
                </select>
                <small class="form-text text-muted">
                    E = Élimination, T = Tri/Transfert, C = Conservation définitive
                </small>
            </div>
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ $sort->name }}" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <input type="text" name="description" id="description" class="form-control" value="{{ $sort->description }}">
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection
