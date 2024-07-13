@extends('layouts.app')
@section('content')

<div class="container">
    <h1>Ajouter une nouvelle typologie de Courrier</h1>
    <form action="{{ route('mail-typology.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description"></textarea>
        </div>
        <div class="mb-3">
            <label for="activity_id" class="form-label">Class</label>
            <select class="form-select" id="activity_id" name="activity_id" required>
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</div>






@endsection
