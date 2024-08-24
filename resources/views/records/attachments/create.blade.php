@extends('layouts.app')

@section('content')
    <h1>Add Attachment to {{ $record->name }}</h1>
    <form action="{{ route('records.attachments.store', $record) }}" method="POST">
        @csrf
        <div>
            <label for="file_path">File Path:</label>
            <input type="text" name="file_path" id="file_path" required>
        </div>
        <div>
            <label for="description">Description:</label>
            <input type="text" name="description" id="description">
        </div>
        <button type="submit">Add Attachment</button>
    </form>
@endsection
