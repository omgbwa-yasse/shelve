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
                <th>Description</th>
                <th>Paramètre</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transactions as $transaction)
            <tr>
                <td>{{ $transaction->mails->name ?? 'N/A' }}</td>
                <td>{{ $transaction->organisationSend->name ?? 'N/A' }}</td>
                <td>{{ $transaction->organisationReceived->name ?? 'N/A' }}</td>
                <td>{{ $transaction->document_type_id ?? 'N/A' }}</td>
                <td>{{ $transaction->date_creation ? date('Y-m-d', strtotime($transaction->date_creation)) : 'N/A' }}</td>
                <td>{{ $transaction->action->name }}</td>
                <td>{{ $transaction->description }}</td>
                <td>
                    <a href="{{ route('mail-received.show', $transaction) }}" class="btn btn-primary">Détails</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
