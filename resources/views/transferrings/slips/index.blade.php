@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Transferring Slips</h1>
        <a href="{{ route('slips.create') }}" class="btn btn-primary mb-3">Create New Slip</a>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($slip as $slip)
                    <tr>
                        <td>{{ $slip->id }}</td>
                        <td>{{ $slip->code }}</td>
                        <td>{{ $slip->name }}</td>
                        <td>{{ $slip->description }}</td>
                        <td>
                            <a href="{{ route('slips.show', $slip->id) }}" class="btn btn-info btn-sm">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
