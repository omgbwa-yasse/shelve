@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Activity</h1>
        <form action="{{ route('activities.update', $activity->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="code">Code</label>
                <input type="text" name="code" id="code" class="form-control" value="{{ $activity->code }}" required>
            </div>
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ $activity->name }}" required>
            </div>
            <div class="form-group">
                <label for="observation">Observation</label>
                <textarea name="observation" id="observation" class="form-control">{{ $activity->observation }}</textarea>
            </div>
            <div class="form-group">
                <label for="parent_id">Parent ID</label>
                <select name="parent_id" id="parent_id" class="form-control">
                    <option value="">None</option>
                    @foreach ($parents as $parent)
                        <option value="{{ $parent->id }}" {{ $parent->id == $activity->parent_id ? 'selected' : '' }}>{{ $parent->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection
