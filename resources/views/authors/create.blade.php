@extends('layouts.app')

@section('content')

<div class="container mt-4">

    <h2>Add New Author</h2>

    <form action="{{ route('mail-author.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="type_id" class="form-label">Type</label>
            <select id="type_id" name="type_id" class="form-control" required>
                @foreach ($authorTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" id="name" name="name" class="form-control" data-field="name" required>
        </div>

        <div class="mb-3">
            <label for="parallel_name" class="form-label">Parallel Name</label>
            <input type="text" id="parallel_name" name="parallel_name" class="form-control" data-field="parallel_name">
        </div>

        <div class="mb-3">
            <label for="other_name" class="form-label">Other Name</label>
            <input type="text" id="other_name" name="other_name" class="form-control" data-field="other_name">
        </div>

        <div class="mb-3">
            <label for="lifespan" class="form-label">Lifespan</label>
            <input type="text" id="lifespan" name="lifespan" class="form-control">
        </div>

        <div class="mb-3">
            <label for="locations" class="form-label">Locations</label>
            <input type="text" id="locations" name="locations" class="form-control" data-field="locations">
        </div>

        <div class="mb-3">
            <label for="parent_id" class="form-label">Parent Author</label>
            <select id="parent_id" name="parent_id" class="form-control">
                <option value="">None</option>
                @foreach ($parents as $parent)
                    <option value="{{ $parent->id }}">{{ $parent->name }} <i>({{ $parent->authorType->name }})</i>  </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Create Author</button>
    </form>
</div>

@endsection
