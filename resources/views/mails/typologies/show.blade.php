@extends('layouts.app')
@section('content')

<div class="container">
    <h1>Mail Typology Details</h1>
    <table class="table">
        <tbody>
            <tr>
                <th>Code</th>
                <td>{{ $mailTypology->code }}</td>
            </tr>
            <tr>
                <th>Name</th>
                <td>{{ $mailTypology->name }}</td>
            </tr>
            <tr>
                <th>Description</th>
                <td>{{ $mailTypology->description }}</td>
            </tr>
            <tr>
                <th>Domaine d'activité</th>
                <td>{{ $mailTypology->activity->name ?? 'NAN'}}</td>
            </tr>
        </tbody>
    </table>
    <a href="{{ route('mail-typology.index') }}" class="btn btn-secondary">Back</a>
    <a href="{{ route('mail-typology.edit', $mailTypology->id) }}" class="btn btn-warning btn-sm">Edit</a>
    <form action="{{ route('mail-typology.destroy', $mailTypology->id) }}" method="POST" style="display: inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this mail typology?')">Delete</button>
    </form>
</div>

@endsection
