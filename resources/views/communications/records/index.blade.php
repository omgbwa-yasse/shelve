@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Communications</h1>
        <a href="{{ route('communication-transactions.create') }}" class="btn btn-primary mb-3">Create New Communication</a>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Operator</th>
                    <th>Operator Organisation</th>
                    <th>User</th>
                    <th>User Organisation</th>
                    <th>Return Date</th>
                    <th>Return Effective</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($communications as $communication)
                    <tr>
                        <td>{{ $communication->id }}</td>
                        <td>{{ $communication->code }}</td>
                        <td>{{ $communication->operator->name }}</td>
                        <td>{{ $communication->operatorOrganisation->name }}</td>
                        <td>{{ $communication->user->name }}</td>
                        <td>{{ $communication->userOrganisation->name }}</td>
                        <td>{{ $communication->return_date }}</td>
                        <td>{{ $communication->return_effective }}</td>
                        <td>{{ $communication->status->name }}</td>
                        <td>
                            <a href="{{ route('communication-transactions.show', $communication->id) }}" class="btn btn-info">Show</a>
                            <a href="{{ route('communication-transactions.edit', $communication->id) }}" class="btn btn-warning">Edit</a>
                            <form action="{{ route('communication-transactions.destroy', $communication->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this communication?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
