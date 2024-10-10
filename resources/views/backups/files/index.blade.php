@extends('layouts.app')

@section('content')
    <h1>Backup Files for Backup {{ $backup->id }}</h1>
    <a href="{{ route('backups.files.create', $backup->id) }}" class="btn btn-primary mb-3">Create New Backup File</a>
    <table class="table">
        <thead>
            <tr>
                <th>Path Original</th>
                <th>Path Storage</th>
                <th>Size</th>
                <th>Hash</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($files as $file)
                <tr>
                    <td>{{ $file->path_original }}</td>
                    <td>{{ $file->path_storage }}</td>
                    <td>{{ $file->size }}</td>
                    <td>{{ $file->hash }}</td>
                    <td>
                        <a href="{{ route('backups.files.show', [$backup->id, $file->id]) }}" class="btn btn-sm btn-info">View</a>
                        <a href="{{ route('backups.files.edit', [$backup->id, $file->id]) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('backups.files.destroy', [$backup->id, $file->id]) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
