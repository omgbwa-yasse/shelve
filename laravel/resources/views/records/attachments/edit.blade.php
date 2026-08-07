@extends('layouts.app')

@section('content')
    <h1>Edit Attachment for {{ $record->name }}</h1>
    <form action="{{ route('records.attachments.update', [$record, $attachment]) }}" method="POST">
        @csrf
        @method('PUT')
        <div>
            <label for="file_path">File Path:</label>
            <input type="text" name="file_path" id="file_path" value="{{ $attachment->file_path }}" required>
        </div>
        <div>
            <label for="description">Description:</label>
            <input type="text" name="description" id="description" value="{{ $attachment->description }}">
        </div>
        <button type="submit">Update Attachment</button>
    </form>
@endsection
