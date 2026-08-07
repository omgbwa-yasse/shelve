@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Container Statuses</h1>
        <a href="{{ route('container-status.create') }}" class="btn btn-primary mb-3">Create Container Status</a>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($containerStatuses as $containerStatus)
                    <tr>
                        <td>{{ $containerStatus->id }}</td>
                        <td>{{ $containerStatus->name }}</td>
                        <td>{{ $containerStatus->description }}</td>
                        <td>
                            <a href="{{ route('container-status.show', $containerStatus->id) }}" class="btn btn-info btn-sm">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
