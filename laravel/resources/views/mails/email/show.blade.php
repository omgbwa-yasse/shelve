@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">{{ $message->subject ?: '(Sans sujet)' }}</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('mails.email.compose', ['reply_to' => $message->id]) }}" class="btn btn-sm btn-primary"><i class="bi bi-reply"></i> Répondre</a>
            <form action="{{ route('mails.email.toggle-flag', $message) }}" method="POST" class="d-inline">
                @csrf
                <button class="btn btn-sm btn-outline-warning"><i class="bi bi-star{{ $message->is_flagged ? '-fill' : '' }}"></i></button>
            </form>
            <form action="{{ route('mails.email.destroy', $message) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce message du miroir local ?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
            <a href="{{ $message->folder === 'Sent' ? route('mails.email.sent') : route('mails.email.inbox') }}" class="btn btn-sm btn-outline-secondary">Retour</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <dl class="row mb-3">
                <dt class="col-sm-1">De</dt>
                <dd class="col-sm-11">{{ $message->from_name }} &lt;{{ $message->from_address }}&gt;</dd>
                <dt class="col-sm-1">À</dt>
                <dd class="col-sm-11">{{ implode(', ', array_column($message->to ?? [], 'mail')) }}</dd>
                @if(!empty($message->cc))
                    <dt class="col-sm-1">Cc</dt>
                    <dd class="col-sm-11">{{ implode(', ', array_column($message->cc, 'mail')) }}</dd>
                @endif
                <dt class="col-sm-1">Date</dt>
                <dd class="col-sm-11">{{ $message->sent_at?->format('d/m/Y H:i') }}</dd>
            </dl>

            <hr>

            @if($message->body_html)
                {{-- HTML d'un email externe = contenu non fiable : jamais rendu directement
                     dans la page (risque XSS via <script>/gestionnaires d'événements).
                     Isolé dans un iframe sandboxé sans `allow-scripts` — le HTML s'affiche,
                     rien ne peut s'y exécuter. `allow-same-origin` seul sert uniquement à
                     mesurer sa hauteur pour l'auto-redimensionner. --}}
                <iframe
                    srcdoc="{{ $message->body_html }}"
                    sandbox="allow-same-origin"
                    class="email-body-frame"
                    style="width: 100%; border: 0;"
                    onload="this.style.height = (this.contentWindow.document.body.scrollHeight + 20) + 'px';"
                ></iframe>
            @else
                <pre class="email-body" style="white-space: pre-wrap;">{{ $message->body_text }}</pre>
            @endif

            @if($message->attachments->isNotEmpty())
                <hr>
                <h6><i class="bi bi-paperclip"></i> Pièces jointes</h6>
                <ul class="list-unstyled">
                    @foreach($message->attachments as $attachment)
                        <li>
                            <a href="{{ route('mails.email.attachments.download', [$message, $attachment]) }}">
                                <i class="bi bi-file-earmark"></i> {{ $attachment->filename }}
                            </a>
                            <span class="text-muted small">({{ number_format(($attachment->size ?? 0) / 1024, 1) }} Ko)</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h6><i class="bi bi-tags"></i> Étiquettes</h6>
            <div class="d-flex flex-wrap gap-2 mb-2">
                @foreach($message->tags as $tag)
                    <span class="badge d-flex align-items-center gap-1" style="background-color: {{ $tag->color }}">
                        {{ $tag->name }}
                        <form action="{{ route('mails.email.tags.detach', [$message, $tag]) }}" method="POST" class="d-inline m-0">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm p-0 border-0 text-white" style="line-height: 1;">&times;</button>
                        </form>
                    </span>
                @endforeach
            </div>
            <form action="{{ route('mails.email.tags.attach', $message) }}" method="POST" class="d-flex gap-2">
                @csrf
                <select name="tag_id" class="form-select form-select-sm" style="max-width: 240px;" required>
                    <option value="">Ajouter une étiquette…</option>
                    @foreach($tags as $tag)
                        <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-outline-primary">Ajouter</button>
            </form>
        </div>
    </div>
</div>
@endsection
