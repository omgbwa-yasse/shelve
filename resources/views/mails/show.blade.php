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
                <td>{{ $mail->object }}</td>
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
                <th>Mail Priority</th>
                <td>{{ $mail->priority->name }}</td>
            </tr>
            <tr>
                <th>Mail Type</th>
                <td>{{ $mail->type->name }}</td>
            </tr>
            <tr>
                <th>Mail Typology</th>
                <td>{{ $mail->typology->name }}</td>
            </tr>
        </table>
        <a href="{{ route('mails.index') }}" class="btn btn-secondary">Back</a>
    </div>
@endsection
