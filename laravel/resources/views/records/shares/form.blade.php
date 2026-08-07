@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3"><i class="bi bi-share"></i> Partager — {{ $record->code }} — {{ $record->name }}</h1>
        </div>
        <a href="{{ route('records.show', $record) }}" class="btn btn-outline-secondary">Retour à la notice</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">Nouveau partage</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('records.shares.store', $record) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Utilisateur</label>
                            <select name="user_id" class="form-select">
                                <option value="">— Aucun —</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rôle (groupe)</label>
                            <select name="role_id" class="form-select">
                                <option value="">— Aucun —</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Permission</label>
                            <select name="permission" class="form-select">
                                <option value="view">Lecture seule</option>
                                <option value="edit">Lecture / modification</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Expiration (optionnelle)</label>
                            <input type="datetime-local" name="expires_at" class="form-control">
                        </div>
                        <button class="btn btn-primary"><i class="bi bi-share"></i> Partager</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">Partages existants ({{ $shares->count() }})</div>
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Destinataire</th>
                            <th>Permission</th>
                            <th>Expiration</th>
                            <th style="width: 60px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shares as $share)
                            <tr>
                                <td>{{ $share->user?->name ?? $share->role?->name ?? '—' }}</td>
                                <td><span class="badge {{ $share->permission === 'edit' ? 'bg-warning text-dark' : 'bg-light text-dark' }}">{{ $share->permission }}</span></td>
                                <td>
                                    @if($share->expires_at)
                                        <span class="{{ $share->isExpired() ? 'text-danger' : '' }}">{{ $share->expires_at->format('d/m/Y H:i') }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('records.shares.destroy', [$record, $share]) }}"
                                          onsubmit="return confirm('Révoquer ce partage ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">Aucun partage</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
