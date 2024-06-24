@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Organisation</h1>
        <form action="{{ route('organisations.update', $organisation->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="code">Code</label>
                <input type="text" name="code" id="code" class="form-control" value="{{ $organisation->code }}" required>
            </div>
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ $organisation->name }}" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" class="form-control">{{ $organisation->description }}</textarea>
            </div>
            <div class="form-group">
                <label for="parent_id">Parent Organisation</label>
                <select name="parent_id" id="parent_id" class="form-control">
                    <option value="">None</option>
                    @foreach ($organisations as $org)
                        <option value="{{ $org->id }}" {{ $org->id == $organisation->parent_id ? 'selected' : '' }}>{{ $org->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection
