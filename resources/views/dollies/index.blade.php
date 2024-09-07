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
                    @if($dolly->type->name == 'record')
                        Archives
                    @elseif($dolly->type->name == 'mail')
                        Courrier
                    @elseif($dolly->type->name == 'communication')
                        Communication des archives
                    @elseif($dolly->type->name == 'room')
                        Salle d'archives
                    @elseif($dolly->type->name == 'container')
                        Boites d'archives et chronos
                    @elseif($dolly->type->name == 'shelf')
                        Etagère
                    @elseif($dolly->type->name == 'slip_record')
                        Archives (versement)
                    @endif

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
