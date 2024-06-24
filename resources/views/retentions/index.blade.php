@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Retentions</h1>
        <a href="{{ route('retentions.create') }}" class="btn btn-primary mb-3">Create Retention</a>
        <table class="table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Duration</th>
                    <th>Sort</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($retentions as $retention)
                    <tr>
                        <td>{{ $retention->code }}</td>
                        <td>{{ $retention->duration }} ans</td>
                        <td>{{ $retention->sort->name }} ({{ $retention->sort->code }})</td>
                        <td>
                            <a href="{{ route('retentions.show', $retention->id) }}" class="btn btn-info">Paramètres</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
