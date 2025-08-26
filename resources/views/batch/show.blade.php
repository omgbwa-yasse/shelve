@extends('layouts.app')
@section('content')
<div class="container">
    <h1 class="mt-5">Parapheur : fiche</h1>
    <table class="table">
        <tbody>
            <tr>
                <td>Reference : {{  $mailBatch->code  }}</td>
            </tr>
            <tr>
                <td>Désignation : {{  $mailBatch->name  }}</td>
            </tr>
        </tbody>
    </table>
    <a href="{{ route('batch.index') }}" class="btn btn-secondary mt-3">Back</a>
    <a href="{{ route('batch.edit', $mailBatch->id) }}" class="btn btn-warning mt-3">Edit</a>
    <form action="{{ route('batch.destroy', $mailBatch->id) }}" method="POST" style="display: inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger mt-3" onclick="return confirm('Are you sure you want to delete this mail batch?')">Delete</button>
    </form>
    <a href="{{ route('batch.mail.create', $mailBatch) }}" class="btn btn-warning mt-3">Ajouter des courrier</a>
</div>
@foreach ( $mailBatch->mails as $mail)

        <div class="card text-start mt-1">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <h4 class="card-title mb-2">
                        @php
                            $showRoute = '#';
                            if ($mail->isIncoming()) {
                                $showRoute = route('mail-received.show', $mail->id);
                            } elseif ($mail->isOutgoing()) {
                                $showRoute = route('mail-send.show', $mail->id);
                            } elseif ($mail->external_sender_id || $mail->external_recipient_id) {
                                // Pour les courriers externes, utiliser les routes externes si disponibles
                                $showRoute = route('mails.incoming.show', $mail->id);
                            } else {
                                // Fallback pour les courriers internes
                                $showRoute = route('mails.incoming.show', $mail->id);
                            }
                        @endphp
                        <a href="{{ $showRoute }}">
                            <strong>{{ $mail->code }} : {{ $mail->name }}
                                @if($mail->attachments->count() > 1 )
                                    ({{ $mail->attachments->count() }} fichiers)
                                @elseif($mail->attachments->count() == 1 )
                                    ({{ $mail->attachments->count() }} fichier)
                                @endif
                            </strong>
                        </a>
                    </h4>
                    <p class="card-text mb-1">
                        Du {{ $mail->date }} Par
                        @foreach($mail->authors as $author)
                            {{ $author->name }}
                        @endforeach
                    </p>
                    <p class="mb-1">{{ $mail->description }}</p>
                    <p class="mb-0">
                        <small>
                            Priority: {{ $mail->priority ? $mail->priority->name : 'N/A' }}
                            | Mail Type: {{ $mail->type ? $mail->type->name : 'N/A' }}
                            | Business Type: {{ $mail->typology ? $mail->typology->name : 'N/A' }}
                            | Nature: {{ $mail->documentType ? $mail->documentType->name : 'N/A' }}
                        </small>
                    </p>
                </div>
                <form action="{{ route('batch.mail.destroy', [$mailBatch, $mail->id]) }}" method="POST" style="display: inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm ms-2" onclick="return confirm('Are you sure you want to delete this batch mail?')">Retirer</button>
                </form>
            </div>
        </div>

@endforeach

@endsection
