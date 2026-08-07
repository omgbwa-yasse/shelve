@php($folder = $folder ?? 'inbox')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <ul class="nav nav-pills">
        <li class="nav-item">
            <a class="nav-link {{ $folder === 'inbox' ? 'active' : '' }}" href="{{ route('mails.email.inbox', request()->only('account')) }}">
                <i class="bi bi-inbox"></i> Réception
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $folder === 'sent' ? 'active' : '' }}" href="{{ route('mails.email.sent', request()->only('account')) }}">
                <i class="bi bi-send"></i> Envoyés
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('mails.email.tags.manage.index') }}">
                <i class="bi bi-tags"></i> Étiquettes
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('settings.email-accounts.index') }}">
                <i class="bi bi-gear"></i> Paramètres
            </a>
        </li>
    </ul>
    <div class="d-flex gap-2 align-items-center">
        @if(($accounts ?? collect())->count() > 1)
            <form method="GET" class="d-inline">
                <select name="account" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach($accounts as $a)
                        <option value="{{ $a->id }}" @selected(($account?->id ?? null) == $a->id)>{{ $a->name }}</option>
                    @endforeach
                </select>
            </form>
        @endif
        <a href="{{ route('mails.email.compose') }}" class="btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i> Composer</a>
    </div>
</div>
