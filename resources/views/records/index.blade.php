@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Records</div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <a href="{{ route('records.create') }}" class="btn btn-primary mb-3">Create New Record</a>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Name</th>
                                <th>Date Format</th>
                                <th>Date Start</th>
                                <th>Date End</th>
                                <th>Date Exact</th>
                                <th>Description</th>
                                <th>Level</th>
                                <th>Status</th>
                                <th>Support</th>
                                <th>Classification</th>
                                <th>Parent</th>
                                <th>Container</th>
                                <th>Transfer</th>
                                <th>User</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($records as $record)
                                <tr>
                                    <td>{{ $record->reference }}</td>
                                    <td>{{ $record->name }}</td>
                                    <td>{{ $record->date_format }}</td>
                                    <td>{{ $record->date_start }}</td>
                                    <td>{{ $record->date_end }}</td>
                                    <td>{{ $record->date_exact }}</td>
                                    <td>{{ $record->description }}</td>
                                    <td>{{ $record->level->name }}</td>
                                    <td>{{ $record->status->name }}</td>
                                    <td>{{ $record->support->name }}</td>
                                    <td>{{ $record->classification->name }}</td>
                                    <td>{{ $record->parent ? $record->parent->name : '-' }}</td>
                                    <td>{{ $record->container->name }}</td>
                                    <td>{{ $record->transfer ? $record->transfer->name : '-' }}</td>
                                    <td>{{ $record->user->name }}</td>
                                    <td>
                                        <a href="{{ route('records.show', $record->id) }}" class="btn btn-info">Show</a>
                                        <a href="{{ route('records.edit', $record->id) }}" class="btn btn-primary">Edit</a>
                                        <form action="{{ route('records.destroy', $record->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
