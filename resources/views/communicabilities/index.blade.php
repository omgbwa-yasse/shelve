@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Communicabilities</h1>
        <a href="{{ route('communicabilities.create') }}" class="btn btn-primary mb-3">Create New Communicability</a>
        <table class="table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Duration (année) </th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($communicabilities as $communicability)
                    <tr>
                        <td>{{ $communicability->code }}</td>
                        <td>{{ $communicability->name }}</td>
                        <td>{{ $communicability->duration }}</td>
                        <td>
                            <a href="{{ route('communicabilities.show', $communicability->id) }}" class="btn btn-info">Paramètres</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
