@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Retention</h1>
        <form action="{{ route('retentions.update', $retention->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="code">Code</label>
                <input type="text" name="code" id="code" class="form-control" value="{{ $retention->code }}" required>
            </div>
            <div class="form-group">
                <label for="name">Nom</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ $retention->name }}" required maxlength="200">
            </div>
            <div class="form-group">
                <label for="duration">Duration</label>
                <input type="number" name="duration" id="duration" class="form-control" value="{{ $retention->duration }}" required>
            </div>
            <div class="form-group">
                <label for="sort_id">Sort final</label>
                <select name="sort_id" id="sort_id" class="form-control" required>
                    @foreach ($sorts as $sort)
                        <option value="{{ $sort->id }}" {{ $sort->id == $retention->sort_id ? 'selected' : '' }}>{{ $sort->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection
