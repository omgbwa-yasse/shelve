@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Modifier un contenant d'archives</h1>
        <form action="{{ route('slips.containers.update', $container->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="code" class="form-label">Code</label>
                <input type="text" class="form-control" id="code" name="code" value="{{ $container->code ??'' }}" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <input type="text" class="form-control" id="description" name="description" value="{{ $container->description??'' }}" required>
            </div>

            <div class="mb-3">
                <label for="shelve_id" class="form-label">Shelf</label>
                <select class="form-select" id="shelve_id" name="shelve_id" required>
                    @foreach ($shelves as $shelf)
                        <option value="{{ $shelf->id }}" {{ $shelf->id == $container->shelve_id ? 'selected' : '' }}>
                            {{ $shelf->code }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="status_id" class="form-label">Status</label>
                <select class="form-select" id="status_id" name="status_id" required>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->id }}" {{ $status->id == $container->status_id ? 'selected' : '' }}>
                            {{ $status->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="property_id" class="form-label">Property</label>
                <select class="form-select" id="property_id" name="property_id" required>
                    @foreach ($properties as $property)
                        <option value="{{ $property->id }}" {{ $property->id == $container->property_id ? 'selected' : '' }}>
                            {{ $property->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection
