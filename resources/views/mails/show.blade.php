@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Courrier : fiche</h1>
        <table class="table">
            <tr>
                <th>ID</th>
                <td>{{ $mail->id }}</td>
            </tr>
            <tr>
                <th>Code</th>
                <td>{{ $mail->code }}</td>
            </tr>
            <tr>
                <th>Object</th>
                <td>{{ $mail->name }}</td>
            </tr>
            <tr>
                <th>Auteur </th>
                <td>{{ $mail->author }}</td>
            </tr>
            <tr>
                <th>Description</th>
                <td>{{ $mail->description }}</td>
            </tr>
            <tr>
                <th>Date</th>
                <td>{{ $mail->date }}</td>
            </tr>
            <tr>
                <th>Priorité</th>
                <td>{{ $mail->priority->name }}</td>
            </tr>
            <tr>
                <th>Type de courrier</th>
                <td>{{ $mail->type->name }}</td>
            </tr>
            <tr>
                <th>Type d'affaire</th>
                <td>{{ $mail->typology->name }}</td>
            </tr>
            <tr>
                <th>Nature</th>
                <td>{{ $mail->documentType->name }}</td>
            </tr>
        </table>
        <a href="{{ route('mails.index') }}" class="btn btn-secondary">Back</a>
        <a href="{{ route('mails.edit', $mail->id) }}" class="btn btn-warning btn-secondary">Edit</a>
        <form action="{{ route('mails.destroy', $mail->id) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-secondary">Delete</button>
        </form>
    </div>
@endsection
