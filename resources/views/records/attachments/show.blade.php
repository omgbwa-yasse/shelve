@extends('layouts.app')

@section('content')
    <h1>{{ $record->name }}</h1>
    <p>{{ $record->description }}</p>
    <h2>Attachments</h2>
    <ul>
        @foreach($record->attachments as $attachment)
            <li>{{ $attachment->file_path }} - {{ $attachment->description }}</li>
        @endforeach
    </ul>
    <a href="{{ route('records.attachments.create', $record) }}">Add Attachment</a>
    <a href="{{ route('records.attachments.index', $record) }}">View Attachments</a>
@endsection
