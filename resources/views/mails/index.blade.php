@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Liste des courriers</h1>
        <a href="{{ route('mails.create') }}" class="btn btn-primary mb-3">Create Mail</a>
        <table class="table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Object, Auteur</th>
                    <th>Date</th>
                    <th>Producteur</th>
                    <th>Localisation</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($mails as $mail)
                    <tr>
                        <td>{{ $mail->code }}</td>
                        <td>{{ $mail->name }},

                            @foreach($mail->authors as $author)
                                {{ $author->name }}
                            @endforeach

                        </td>
                        <td>{{ $mail->date }}</td>
                        <td> </td>
                        <td></td>

                        <td>
                            <a href="{{ route('mails.show', $mail->id) }}" class="btn btn-info btn-sm">Détails</a>
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@endsection
