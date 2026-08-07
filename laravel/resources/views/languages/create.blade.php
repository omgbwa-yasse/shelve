@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Add New Language</h1>
        <form action="{{ route('languages.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="code">Code</label>
                <input type="text" name="code" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('languages.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
@endsection
