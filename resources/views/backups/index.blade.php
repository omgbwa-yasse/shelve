@extends('layouts.app')

@section('content')
    <h1>Backups</h1>
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Description</th>
                <th>Status</th>
                <th>User</th>
                <th>Size</th>
                <th>Backup File</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($backups as $backup)
                <tr>
                    <td>{{ $backup->date_time }}</td>
                    <td>{{ $backup->type }}</td>
                    <td>{{ $backup->description }}</td>
                    <td>{{ $backup->status }}</td>
                    <td>{{ $backup->user->name }}</td>
                    <td>{{ $backup->size }}</td>
                    <td>{{ $backup->backup_file }}</td>
                    <td>
                        <a href="{{ route('backups.show', $backup->id) }}" class="btn btn-sm btn-info">Voir</a>
                        <a href="{{ route('backups.edit', $backup->id) }}" class="btn btn-sm btn-warning">Éditer</a>
                        <form action="{{ route('backups.destroy', $backup->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
