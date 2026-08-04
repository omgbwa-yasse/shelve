@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h1><i class="bi bi-trash"></i> Listes de déclassement</h1>
            </div>
            <div class="col-auto">
                @can('create', App\Models\DeclassementList::class)
                    <a href="{{ route('declassement-lists.create') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-plus-circle"></i> Nouvelle liste
                    </a>
                @endcan
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <ul class="nav nav-tabs mb-3">
            @foreach(['draft' => 'Brouillon', 'requested' => "Demande d'approbation", 'approved' => 'Approuvé', 'validated' => 'Validé', 'treated' => 'Traité'] as $key => $label)
                <li class="nav-item">
                    <a class="nav-link {{ $categ === $key ? 'active' : '' }}" href="{{ route('declassement-lists.index') }}?categ={{ $key }}">
                        {{ $label }}
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Nom</th>
                        <th>Statut</th>
                        <th>Dossiers</th>
                        <th>Créée par</th>
                        <th>Créée le</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($declassementLists as $list)
                        <tr>
                            <td>{{ $list->code }}</td>
                            <td><a href="{{ route('declassement-lists.show', $list) }}">{{ $list->name }}</a></td>
                            <td><span class="badge bg-info">{{ $list->status->name ?? '' }}</span></td>
                            <td>{{ $list->records->count() }}</td>
                            <td>{{ $list->creator->name ?? '' }}</td>
                            <td>{{ $list->created_at->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('declassement-lists.show', $list) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Aucune liste de déclassement.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $declassementLists->appends(['categ' => $categ])->links() }}
    </div>
@endsection
