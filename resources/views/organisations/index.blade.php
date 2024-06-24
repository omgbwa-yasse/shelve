@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Organisations</h1>
        <a href="{{ route('organisations.create') }}" class="btn btn-primary mb-3">Ajouter une unité administrative</a>
        <table class="table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Parent </th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($organisations as $organisation)
                    <tr>
                        <td>{{ $organisation->code }}</td>
                        <td>{{ $organisation->name }}</td>
                        <td>{{ $organisation->description }}</td>
                        <td>{{ $organisation->parent->name ?? '' }}</td>
                        <td>
                            <a href="{{ route('organisations.show', $organisation->id) }}" class="btn btn-info">Show</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
