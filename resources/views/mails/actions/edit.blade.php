@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Mail Action</h1>
    <form action="{{ route('mail-action.update', $mailAction) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $mailAction->name }}" required>
        </div>
        <div class="mb-3">
            <label for="duration" class="form-label">Duration (en heures)</label>
            <input type="number" name="duration" id="duration" class="form-control" value="{{ $mailAction->duration }}" required>
        </div>
        <div class="mb-3">
            <label for="to_return" class="form-label">To Return</label>
            <select name="to_return" id="to_return" class="form-select">
                <option value="1" {{ $mailAction->to_return ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ !$mailAction->to_return ? 'selected' : '' }}>No</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control" >{{ $mailAction->description }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
