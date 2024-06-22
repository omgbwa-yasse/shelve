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
                <td>
                @foreach($mail->authors as $author)
                    {{ $author->name }}
                @endforeach
                </td>
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

    <span class="badge bg-primary" >Transactions</span> <br>
    @if($mail->transactions)
    <table class="table">
        <thead>
            <tr>
                <th scope="col">Code</th>
                <th scope="col">Date Creation</th>
                <th scope="col">Structure Emetrice </th>
                <th scope="col">Emeteur </th>
                <th scope="col">Structure Réceptrice </th>
                <th scope="col">Receveur </th>
                <th scope="col">Créer </th>
                <th scope="col">Mise à jour </th>
                <th scope="col">Mail Type</th>
                <th scope="col">Type de document</th>
            </tr>
        </thead>
        <tbody>
            @foreach($mail->transactions as $transaction)
                <tr>
                    <td>{{ $transaction->code }}</td>
                    <td>{{ $transaction->date_creation }}</td>
                    <td>{{ $transaction->organisationSend->name }}</td>
                    <td>{{ $transaction->userSend->name}}</td>
                    <td>{{ $transaction->organisationReceived->name }}</td>
                    <td>{{ $transaction->userReceived->name }}</td>
                    <td>{{ $transaction->create_at }}</td>
                    <td>{{ $transaction->update_at }}</td>
                    <td><!--   $transaction->type->name  --> à completer</td>
                    <td>{{ $transaction->documentType->name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @else
        Aucune transaction.
    @endif








@endsection
