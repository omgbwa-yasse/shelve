@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Language</h1>
        <form action="{{ route('languages.update', $language->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="code">Code</label>
                <input type="text" name="code" class="form-control" value="{{ $language->code }}" required>
            </div>
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" class="form-control" value="{{ $language->name }}" required>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('languages.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
@endsection
