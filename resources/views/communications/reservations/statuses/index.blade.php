@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Reservation Statuses</h1>
        <a href="{{ route('reservation-status.create') }}" class="btn btn-primary mb-3">Create New Status</a>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($statuses as $status)
                    <tr>
                        <td>{{ $status->name }}</td>
                        <td>{{ $status->description }}</td>
                        <td>
                            <a href="{{ route('reservation-status.show', $status->id) }}" class="btn btn-info btn-sm">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
