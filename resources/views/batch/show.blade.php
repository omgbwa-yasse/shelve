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
                        <a href="{{route('mails.show', $mail->id ) }}">
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
                        Du <a href="{{route('mails.sort') }}?categ=date&value={{ $mail->date }}">{{ $mail->date }}</a> Par
                        @foreach($mail->authors as $author)
                            <a href="{{route('mails.sort') }}?categ=author&id={{ $author->id }}">{{ $author->name }}</a>
                        @endforeach
                    </p>
                    <p class="mb-1">{{ $author->description }}</p>
                    <p class="mb-0">
                        <small>
                            Priority:
                            <a href="{{route('mails.sort') }}?categ=priority&id={{ $mail->priority->id }}">{{ $mail->priority ? $mail->priority->name : 'N/A' }}</a>
                            Mail Type:
                            <a href="{{route('mails.sort') }}?categ=type&id={{ $mail->type->id }}">{{ $mail->type ? $mail->type->name : 'N/A' }}</a>
                            Business Type:
                            <a href="{{route('mails.sort') }}?categ=typology&id={{ $mail->typology->id }}">{{ $mail->typology ? $mail->typology->name : 'N/A' }}</a>
                            Nature:
                            <a href="{{route('mails.sort') }}?categ=documentType&id={{ $mail->documentType->id }}">{{ $mail->documentType ? $mail->documentType->name : 'N/A' }}</a>
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
