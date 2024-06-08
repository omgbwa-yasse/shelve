@extends('layouts.app')
@section('content')

<div class="container">
    <h1 class="mt-5">Créer un parapheur</h1>
    <form action="{{ route('batches.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="code" class="form-label">Code:</label>
            <input type="text" class="form-control" id="code" name="code">
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Name:</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</div>

@endsection
