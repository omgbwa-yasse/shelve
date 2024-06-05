@extends('layouts.app')
@section('content')
<div class="container">
    <h1 class="mt-5">Créer une affaire</h1>
    <form action="{{ route('subject.store') }}" method="POST" class="mt-3">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Name:</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</div>
@endsection
