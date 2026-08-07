@if(!$account)
    <div class="alert alert-info">
        Aucun compte de messagerie actif. <a href="{{ route('settings.email-accounts.create') }}">Configurer un compte</a>.
    </div>
@else
    <form method="GET" class="mb-3">
        <input type="hidden" name="account" value="{{ $account->id }}">
        <div class="input-group input-group-sm" style="max-width: 320px;">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Rechercher un sujet…">
        </div>
    </form>

    <div class="list-group">
        @forelse($messages as $message)
            <a href="{{ route('mails.email.show', $message) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-start {{ !$message->is_read ? 'fw-bold' : '' }}">
                <div class="me-3" style="min-width: 220px;">
                    {{ $message->from_name ?: $message->from_address ?: implode(', ', array_column($message->to ?? [], 'mail')) }}
                    @if($message->is_flagged)<i class="bi bi-star-fill text-warning ms-1"></i>@endif
                </div>
                <div class="flex-grow-1">
                    {{ $message->subject ?: '(Sans sujet)' }}
                    @foreach($message->tags as $tag)
                        <span class="badge ms-1" style="background-color: {{ $tag->color }}">{{ $tag->name }}</span>
                    @endforeach
                    @if($message->has_attachments)<i class="bi bi-paperclip text-muted ms-1"></i>@endif
                </div>
                <small class="text-muted ms-3">{{ $message->sent_at?->format('d/m/Y H:i') }}</small>
            </a>
        @empty
            <div class="text-center text-muted py-5">Aucun message.</div>
        @endforelse
    </div>

    <div class="mt-3">{{ $messages->appends(request()->query())->links() }}</div>
@endif
