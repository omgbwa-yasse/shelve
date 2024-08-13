@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Courrier sortant : fiche</h1>

    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <strong>Nom du Mail :</strong>
            </div>
            <div class="col-md-9">
                {{ $mailTransaction->mail->name ?? 'N/A' }}
            </div>
            <div class="col-md-3">
                <strong>Expédier par :</strong>
            </div>
            <div class="col-md-9">
                {{ $mailTransaction->userSend->name ?? 'N/A' }}
            </div>
            <div class="col-md-3">
                <strong>le :</strong>
            </div>
            <div class="col-md-9">
                {{ $mailTransaction->mail->date ?? 'N/A' }}
            </div>
            <div class="col-md-3">
                <strong>Affaire :</strong>
            </div>
            <div class="col-md-9">
                {{ $mailTransaction->mail->typology->name ?? 'N/A' }}
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <strong>Organisation de reception :</strong>
            </div>
            <div class="col-md-9">
                {{ $mailTransaction->organisationReceived->name ?? 'N/A' }}
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <strong>Organisation Emeteur :</strong>
            </div>
            <div class="col-md-9">
                {{ $mailTransaction->organisationSend->name ?? 'N/A' }}
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

        <div class="row">
            <div class="col-md-3">
                <strong>Action :</strong>
            </div>
            <div class="col-md-9">
                {{ $mailTransaction->action->name ?? 'N/A' }}
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <strong> Description :</strong>
            </div>
            <div class="col-md-9">
                {{ $mailTransaction->description ?? 'N/A' }}
            </div>
        </div>


        <a href="{{ route('mail-send.index') }}" class="btn btn-secondary mt-3">Retour</a>
        <a href="{{ route('mail-send.edit', $mailTransaction->id) }}" class="btn btn-secondary mt-3">Edit</a>
            <form action="{{ route('mail-send.destroy', $mailTransaction->id) }}" method="POST" style="display: inline-block;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger mt-3">Delete</button>
        </form>
    </div>
</div>
@endsection
