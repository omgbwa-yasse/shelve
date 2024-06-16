@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Liste des courriers entrants</h1>
    <table class="table">
        <thead>
            <tr>

                <th>Nom</th>
                <th>De</th>
                <th>Pour </th>
                <th>Type</th>
                <th>Date Creation</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transactions as $transaction)
            <tr>
                <td>{{ $transaction->mails->name }}</td>
                <td>{{ $transaction->organisationSend->name }}</td>
                <td>{{ $transaction->organisationReceived->name }}</td>
                <td>{{ $transaction->document_type_id}}</td>
                <td> {{ date('Y-m-d', strtotime($transaction->date_creation)) }} </td>
                <td>
                    <a href="{{ route('mail-received.show', $transaction) }}" class="btn btn-primary">Show</a>
                    <a href="{{ route('mail-received.edit', $transaction) }}" class="btn btn-secondary">Edit</a>
                    <form action="{{ route('mail-received.destroy', $transaction) }}" method="POST" style="display: inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
