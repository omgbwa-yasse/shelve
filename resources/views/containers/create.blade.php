@extends('layouts.app')

@section('content')

    <div class="container">
        <h1>Create Container</h1>
        <form action="{{ route('containers.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="code" class="form-label">Code</label>
                <input type="text" class="form-control" id="code" name="code" required>
            </div>
            <div class="mb-3">
                <label for="shelve_id" class="form-label">Shelf</label>
                <select class="form-select" id="shelve_id" name="shelve_id" required>
                    @foreach ($shelves as $shelf)
                        <option value="{{ $shelf->id }}">{{ $shelf->code }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="status_id" class="form-label">Status</label>
                <select class="form-select" id="status_id" name="status_id" required>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="property_id" class="form-label">Property</label>
                <select class="form-select" id="property_id" name="property_id" required>
                    @foreach ($properties as $property)
                        <option value="{{ $property->id }}">{{ $property->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Create</button>
        </form>
    </div>
@endsection
