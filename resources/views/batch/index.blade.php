@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Mes parapheurs</h1>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <table class="table">
            <thead>
                <tr>
                    <th>code</th>
                    <th>Intitulé </th>
                    <th>Organisation de départ</th>
                    <th>Organisation d'arrivée</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($mailBatches as $transaction)
                    <tr>
                        <td>Code</td>
                        <td>Name {{ $transaction->batch }}</td>
                        <td>{{ $transaction->organisationSend->name }}</td>
                        <td>{{ $transaction->organisationReceived->name }}</td>
                        <td>{{ $transaction->created_at }}</td>
                        <td>
                            <a href="{{ route('mails.sort') }}?categ=batch&id={{$transaction->id}}" class="btn btn-info btn-sm">Voir le contenu</a>
                            <a href="{{ route('batch.show', $transaction) }}" class="btn btn-warning btn-sm">Paramètre</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
