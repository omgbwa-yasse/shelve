@extends('layouts.app')
@section('content')

<div class="container">
    <h1 class="mt-5">Créer un parapheur</h1>
    <form action="{{ route('batch.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="code" class="form-label">Reference :</label>
            <input type="text" class="form-control" id="code" name="code">
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Designation :</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </form>
</div>

@endsection
