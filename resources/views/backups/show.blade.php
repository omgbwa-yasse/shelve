@extends('layouts.app')

@section('content')
    <h1>Backup Details</h1>


    <p><strong>Date:</strong> {{ $backup->date_time }}</p>
    <p><strong>Type:</strong> {{ $backup->type }}</p>
    <p><strong>Description:</strong> {{ $backup->description }}</p>
    <p><strong>Status:</strong> {{ $backup->status }}</p>
    <p><strong>User:</strong> {{ $backup->user->name }}</p>
    <p><strong>Size:</strong> {{ $backup->size }}</p>
    <p><strong>Backup File:</strong> {{ $backup->backup_file }}</p>
    <p><strong>Path:</strong> {{ $backup->path }}</p>
    <form action="{{ route('backups.destroy', $backup->id) }}" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit">Delete</button>
    </form>
@endsection
