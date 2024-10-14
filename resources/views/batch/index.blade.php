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
                    <th>Code</th>
                    <th>Intitulé</th>
                    <th>Nombre courriers </th>
                    <th>Localisation</th>
                    <th>Option</th>
                </tr>
            </thead>
            <tbody>
                @foreach($mailBatches as $batch)
                    <tr>
                        <td>{{ $batch->code }}</td>
                        <td>
                            <strong>
                                <a href="{{ route('batch.show', $batch) }}">{{ $batch->name }}</a>
                            </strong>
                        </td>
                        <td>{{ $batch->mails->count() }}</td>
                        <td>
                            @if ( $batch->transactions->last()->organisationReceived->id == Auth()->user()->current_organisation_id )
                                    Présent
                            @elseif($batch->transactions->last()->organisationReceived->id != Auth()->user()->current_organisation_id)
                                {{ $batch->transactions->last()->organisationReceived->name }} / depuis : {{ $batch->transactions->last()->created_at }}
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('mails.sort') }}?categ=batch&id={{$batch->id}}" class="btn btn-info btn-sm">Voir le contenu</a>

                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
