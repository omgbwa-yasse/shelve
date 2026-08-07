@extends('layouts.app')

@section('content')
    <div class="container">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Description </th>
                    <th>Dates </th>
                    <th>Producteur</th>
                    <th>Versement (cote - intitulé)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transferrings as $transferring)
                <tr>
                    <td>{{ $transferring->id }}</td>
                    <td>{{ $transferring->code }}</td>
                    <td>{{ $transferring->name }}</td>
                    <td>{{ $transferring->content }}</td>
                    <td>{{ $transferring->date ?? 'NAN' }}</td>
                    <td>{{ $transferring->author->name ?? 'Sans producteur' }}</td>
                    <td>{{ $transferring->slip->code }} - {{ $transferring->slip->name }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
