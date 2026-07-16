@extends('layouts.app')

@section('content')
<div class="container shadow-sm p-4 bg-white rounded">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="text-primary mb-1">Intérims des responsables</h1>
            <p class="text-muted mb-0">
                Tant qu'un intérim est actif, le courrier adressé au service est routé vers l'intérimaire.
            </p>
        </div>
        <a href="{{ route('organisation-interims.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Désigner un intérimaire
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Service / Direction</th>
                    <th>Titulaire</th>
                    <th>Intérimaire</th>
                    <th>Période</th>
                    <th>État</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($interims as $interim)
                    <tr>
                        <td>{{ $interim->organisation->name ?? '—' }}</td>
                        <td>{{ $interim->titular->name ?? '—' }} {{ $interim->titular->surname ?? '' }}</td>
                        <td>{{ $interim->interim->name ?? '—' }} {{ $interim->interim->surname ?? '' }}</td>
                        <td>
                            du {{ optional($interim->start_date)->format('d/m/Y') }}
                            @if($interim->end_date)
                                au {{ $interim->end_date->format('d/m/Y') }}
                            @else
                                <span class="text-muted">(sans fin définie)</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $today = \Illuminate\Support\Carbon::today();
                                $enCours = $interim->is_active
                                    && $interim->start_date <= $today
                                    && (!$interim->end_date || $interim->end_date >= $today);
                            @endphp
                            @if($enCours)
                                <span class="badge bg-success">En cours</span>
                            @elseif(!$interim->is_active)
                                <span class="badge bg-secondary">Clôturé</span>
                            @else
                                <span class="badge bg-light text-dark">Programmé / expiré</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($interim->is_active)
                                <form action="{{ route('organisation-interims.deactivate', $interim->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-x-circle"></i> Clôturer
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('organisation-interims.destroy', $interim->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Supprimer définitivement cet intérim ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Aucun intérim enregistré.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $interims->links() }}
</div>
@endsection
