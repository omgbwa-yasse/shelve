@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Languages</h1>
        <a href="{{ route('languages.create') }}" class="btn btn-primary mb-3">Ajouter une langue</a>
        <table class="table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($languages as $language)
                    <tr>
                        <td>{{ $language->code }}</td>
                        <td>{{ $language->name }}</td>
                        <td>
                            <a href="{{ route('languages.show', $language->id) }}" class="btn btn-info">Paramètres</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
