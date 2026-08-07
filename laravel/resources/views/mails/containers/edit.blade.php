@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Mail Container</h1>
        <form action="{{ route('mail-container.update', $mailContainer) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="code" class="form-label">Code</label>
                <input type="text" class="form-control" id="code" name="code" value="{{ $mailContainer->code }}" required>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $mailContainer->name }}" required>
            </div>
            <div class="mb-3">
                <label for="property_id" class="form-label">Type</label>
                <select class="form-select" id="property_id" name="property_id" required>
                    @foreach ($containerProperties as $containerProperty)
                        <option value="{{ $containerProperty->id }}" {{ $containerProperty->id == $mailContainer->property_id ? 'selected' : '' }}>
                            {{ $containerProperty->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection
