@extends('layouts.app')

@section('content')
    <h1>Backup Plannings for Backup {{ $backup->id }}</h1>
    <a href="{{ route('backups.plannings.create', $backup->id) }}" class="btn btn-primary mb-3">Create New Backup Planning</a>
    <table class="table">
        <thead>
            <tr>
                <th>Frequency</th>
                <th>Week Day</th>
                <th>Month Day</th>
                <th>Hour</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($plannings as $planning)
                <tr>
                    <td>{{ $planning->frequence }}</td>
                    <td>{{ $planning->week_day }}</td>
                    <td>{{ $planning->month_day }}</td>
                    <td>{{ $planning->hour }}</td>
                    <td>
                        <a href="{{ route('backups.plannings.show', [$backup->id, $planning->id]) }}" class="btn btn-sm btn-info">View</a>
                        <a href="{{ route('backups.plannings.edit', [$backup->id, $planning->id]) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('backups.plannings.destroy', [$backup->id, $planning->id]) }}" method="POST" class="d-inline">
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
