@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Courrier entrant : fiche</h1>

    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <strong>Nom du Mail :</strong>
            </div>
            <div class="col-md-9">
                {{ $mailTransaction->mails->name ?? 'N/A' }}
            </div>
            <div class="col-md-3">
                <strong>Expédier par :</strong>
            </div>
            <div class="col-md-9">
                {{ $mailTransaction->mails->author ?? 'N/A' }}
            </div>
            <div class="col-md-3">
                <strong>le :</strong>
            </div>
            <div class="col-md-9">
                {{ $mailTransaction->mails->date ?? 'N/A' }}
            </div>
            <div class="col-md-3">
                <strong>Affaire :</strong>
            </div>
            <div class="col-md-9">
                {{ $mailTransaction->mails->typology->name ?? 'N/A' }}
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <strong>Organisation Expéditeur :</strong>
            </div>
            <div class="col-md-9">
                {{ $mailTransaction->organisationSend->name ?? 'N/A' }}
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <strong>Organisation Destinataire :</strong>
            </div>
            <div class="col-md-9">
                {{ $mailTransaction->organisationReceived->name ?? 'N/A' }}
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <strong>Type de Document (ID) :</strong>
            </div>
            <div class="col-md-9">
                {{ $mailTransaction->documentType->name ?? 'N/A' }}
            </div>
        </div>
        <a href="{{ route('mail-received.index') }}" class="btn btn-secondary mt-3">Retour</a>
        <a href="{{ route('mail-received.edit', $mailTransaction) }}" class="btn btn-secondary mt-3">Edit</a>
            <form action="{{ route('mail-received.destroy', $mailTransaction->id) }}" method="POST" style="display: inline-block;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger mt-3">Delete</button>
        </form>
    </div>
</div>
@endsection
