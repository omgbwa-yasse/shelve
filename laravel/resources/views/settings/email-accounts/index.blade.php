@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-envelope-at"></i> Comptes de messagerie</h1>
        <a href="{{ route('settings.email-accounts.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nouveau compte</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-1">Email</h6>
                <p class="mb-0 text-muted small">
                    Tant qu'il est désactivé, la section « Email » n'apparaît pas dans le menu Mails
                    et la boîte de messagerie reste inaccessible — seule cette page de configuration l'est.
                </p>
            </div>
            <form action="{{ route('settings.email.toggle') }}" method="POST">
                @csrf
                <button class="btn {{ $moduleEnabled ? 'btn-success' : 'btn-outline-secondary' }}">
                    <i class="bi bi-toggle-{{ $moduleEnabled ? 'on' : 'off' }}"></i>
                    {{ $moduleEnabled ? 'Activé — cliquer pour désactiver' : 'Désactivé — cliquer pour activer' }}
                </button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Adresse</th>
                        <th>IMAP</th>
                        <th>SMTP</th>
                        <th>Statut</th>
                        <th>Dernière synchro</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $account)
                        <tr>
                            <td>{{ $account->name }}</td>
                            <td>{{ $account->email_address }}</td>
                            <td><span class="font-monospace small">{{ $account->imap_host }}:{{ $account->imap_port }}</span></td>
                            <td><span class="font-monospace small">{{ $account->smtp_host }}:{{ $account->smtp_port }}</span></td>
                            <td>
                                <form action="{{ route('settings.email-accounts.toggle-active', $account) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm p-0 border-0 badge {{ $account->is_active ? 'bg-success' : 'bg-secondary' }}" title="Cliquer pour {{ $account->is_active ? 'désactiver' : 'activer' }}">
                                        {{ $account->is_active ? 'Actif' : 'Inactif' }}
                                    </button>
                                </form>
                                @if($account->last_sync_error)
                                    <span class="badge bg-danger" title="{{ $account->last_sync_error }}">Erreur</span>
                                @endif
                            </td>
                            <td>{{ $account->last_synced_at?->diffForHumans() ?? 'Jamais' }}</td>
                            <td class="text-end">
                                <form action="{{ route('settings.email-accounts.sync', $account) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-secondary" title="Synchroniser maintenant"><i class="bi bi-arrow-repeat"></i></button>
                                </form>
                                <a href="{{ route('settings.email-accounts.edit', $account) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('settings.email-accounts.destroy', $account) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce compte ?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Aucun compte de messagerie configuré.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $accounts->links() }}</div>
</div>
@endsection
