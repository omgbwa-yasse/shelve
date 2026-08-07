@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create Sort</h1>
        <form action="{{ route('sorts.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="code">Code</label>
                <select name="code" id="code" class="form-control" required>
                    <option value="">Sélectionner un code</option>
                    <option value="E">E - Élimination</option>
                    <option value="T">T - Tri/Transfert</option>
                    <option value="C">C - Conservation</option>
                </select>
                <small class="form-text text-muted">
                    E = Élimination, T = Tri/Transfert, C = Conservation définitive
                </small>
            </div>
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <input type="text" name="description" id="description" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Create</button>
        </form>
    </div>
@endsection
