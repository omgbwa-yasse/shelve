<a href="{{ route('chats.show', $conv) }}"
   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $activeConversation && $activeConversation->id === $conv->id ? 'active' : '' }}">
    <div class="d-flex align-items-center gap-2 text-truncate">
        @if($conv->type === 'private')
            @php $other = $conv->participants->first(fn($p) => $p->user_id !== auth()->id()); @endphp
            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; flex-shrink: 0; font-size: 0.8rem;">
                {{ strtoupper(substr($other?->user?->name ?? '?', 0, 2)) }}
            </div>
        @elseif($conv->type === 'channel')
            <div class="rounded-circle bg-warning text-white d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; flex-shrink: 0;"><i class="bi bi-megaphone"></i></div>
        @else
            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; flex-shrink: 0;"><i class="bi bi-people"></i></div>
        @endif
        <div class="text-truncate">
            @if($conv->type === 'private')
                <span class="fw-semibold">{{ $other?->user?->name ?? 'Message privé' }}</span>
            @else
                <span class="fw-semibold">{{ $conv->name }}</span>
            @endif
            @if($conv->description)
                <div class="small text-muted text-truncate">{{ $conv->description }}</div>
            @endif
        </div>
    </div>
    @php $unread = $conv->unreadCountFor(auth()->id()); @endphp
    @if($unread > 0)
        <span class="badge bg-danger rounded-pill">{{ $unread }}</span>
    @endif
</a>
