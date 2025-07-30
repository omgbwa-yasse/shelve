@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Sorts</h1>
        <a href="{{ route('sorts.create') }}" class="btn btn-primary mb-3">Create Sort</a>
        <table class="table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sorts as $sort)
                    <tr>
                        <td>
                            <span class="badge badge-primary">{{ $sort->code }}</span>
                            <small class="text-muted ml-1">{{ $sort->code_description }}</small>
                        </td>
                        <td>{{ $sort->name }}</td>
                        <td>{{ $sort->description }}</td>
                        <td>
                            <a href="{{ route('sorts.show', $sort->id) }}" class="btn btn-info">Paramètres</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
