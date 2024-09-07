@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Chariot</h1>
    <a href="{{ route('dolly.create') }}" class="btn btn-primary mb-3">Create Dolly</a>
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
                        Description des archives
                    @elseif($dolly->type->name == 'mail')
                        Courrier
                    @elseif($dolly->type->name == 'communication')
                        Communication des archives
                    @elseif($dolly->type->name == 'room')
                        Salle d'archives
                    @elseif($dolly->type->name == 'building')
                        Bâtiments d'archives
                    @elseif($dolly->type->name == 'container')
                        Boites d'archives et chronos
                    @elseif($dolly->type->name == 'shelf')
                        Etagère
                    @elseif($dolly->type->name == 'slip')
                        Versement
                    @elseif($dolly->type->name == 'slip_record')
                        Description de versement
                    @endif

                </td>
                <td>
                    <a href="{{ route('dolly.show', $dolly) }}" class="btn btn-info">View</a>
                    <a href="{{ route('dolly.edit', $dolly) }}" class="btn btn-warning">Edit</a>
                    <form action="{{ route('dolly.destroy', $dolly) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this dolly?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
