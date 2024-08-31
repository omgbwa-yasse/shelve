@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">Liste des courriers entrants</h1>

        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="table">
                <tr>
                    <th scope="col">Référence</th>
                    <th scope="col">Nom</th>
                    <th scope="col">De</th>
                    <th scope="col">Pour</th>
                    <th scope="col">Type</th>
                    <th scope="col">Date de Création</th>
                    <th scope="col">Action</th>
                    <th scope="col">Description</th>
                    <th scope="col">Paramètre</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->code ?? 'N/A' }}</td>
                        <td>{{ $transaction->mail->name ?? 'N/A' }}</td>
                        <td>{{ $transaction->organisationSend->name ?? 'N/A' }}</td>
                        <td>{{ $transaction->organisationReceived->name ?? 'N/A' }}</td>
                        <td>{{ $transaction->documentType->name ?? 'N/A' }}</td>
                        <td>{{ $transaction->date_creation ? date('Y-m-d', strtotime($transaction->date_creation)) : 'N/A' }}</td>
                        <td>{{ $transaction->action->name }}</td>
                        <td>{{ $transaction->description }}</td>
                        <td class="text-center">
                            <a href="{{ route('mail-received.show', $transaction) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Détails
                            </a>
                            @if($transaction->type->name == 'inProgress')
                            <a href="{{ route('mails.approve') }}?id={{ $transaction->id}}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-check-all"></i> Recevoir
                            </a>
                            @endIf
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
