@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create Building</h1>
        <form action="{{ route('buildings.store') }}" method="POST">
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
                <label for="visibility" class="form-label">Visibilité</label>
                <select class="form-control" id="visibility" name="visibility" required>
                    @foreach($visibilityOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Create</button>
        </form>
    </div>
@endsection
