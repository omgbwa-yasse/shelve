@extends('layouts.app')
@section('content')

    <div class="container">
    <h1 class="mt-5">Edit Mail Batch</h1>
    <form action="{{ route('mails.batch.update', $mailbatch->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="code" class="form-label">Code:</label>
            <input type="text" class="form-control" id="code" name="code" value="{{ $mailbatch->code }}">
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Name:</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $mailbatch->name }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>


@endsection
