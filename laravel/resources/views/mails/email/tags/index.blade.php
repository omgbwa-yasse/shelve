@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mb-3"><i class="bi bi-envelope"></i> Messagerie</h1>

    @include('mails.email._nav', ['folder' => null])

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <h6>Nouvelle étiquette</h6>
            <form method="POST" action="{{ route('mails.email.tags.manage.store') }}" class="d-flex gap-2 align-items-end">
                @csrf
                <div>
                    <label class="form-label">Nom</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div>
                    <label class="form-label">Couleur</label>
                    <input type="color" name="color" value="#6b7280" class="form-control form-control-color">
                </div>
                <button class="btn btn-primary">Ajouter</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th>Étiquette</th><th>Messages</th><th></th></tr></thead>
                <tbody>
                    @forelse($tags as $tag)
                        <tr>
                            <td><span class="badge" style="background-color: {{ $tag->color }}">{{ $tag->name }}</span></td>
                            <td>{{ $tag->messages_count }}</td>
                            <td class="text-end">
                                <form action="{{ route('mails.email.tags.manage.destroy', $tag) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette étiquette ?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">Aucune étiquette.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
