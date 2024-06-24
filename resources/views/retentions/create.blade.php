@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create Retention</h1>
        <form action="{{ route('retentions.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="code">Code</label>
                <input type="text" name="code" id="code" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="duration">Duration en année</label>
                <input type="number" name="duration" id="duration" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="sort_id">Sort </label>
                <select name="sort_id" id="sort_id" class="form-control" required>
                    @foreach ($sorts as $sort)
                        <option value="{{ $sort->id }}">{{ $sort->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Create</button>
        </form>
    </div>
@endsection
