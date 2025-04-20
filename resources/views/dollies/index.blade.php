@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Chariot</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dollies as $dolly)
            <tr>
                <td>{{ $dolly->name }}</td>
                <td>{{ $dolly->description }}</td>
                <td>
                    @switch($dolly->type->name ?? '')
                        @case('record')
                            Archives
                            @break
                        @case('mail')
                            Courrier
                            @break
                        @case('communication')
                            Communication des archives
                            @break
                        @case('room')
                            Salle d'archives
                            @break
                        @case('container')
                            Boites d'archives et chronos
                            @break
                        @case('shelf')
                            Etagère
                            @break
                        @case('slip_record')
                            Archives (versement)
                            @break
                    @endswitch

                </td>
                <td>
                    <a href="{{ route('dolly.show', $dolly) }}" class="btn btn-info">Afficher</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
