@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('transactions.index') }}">Communications</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('transactions.show', $communication) }}">
                    {{ $communicationRecord->communication->code ?? 'Communication' }}
                </a>
            </li>
            <li class="breadcrumb-item active">Enregistrement #{{ $communicationRecord->id }}</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Détails de l'Enregistrement #{{ $communicationRecord->id }}</h1>
        <div>
            <a href="{{ route('transactions.index') }}" class="btn btn-secondary me-2">
                <i class="bi bi-arrow-left"></i> Retour
            </a>
            <a href="{{ route('transactions.records.edit', [$communication, $communicationRecord]) }}" class="btn btn-warning me-2">
                <i class="bi bi-pencil"></i> Modifier
            </a>
            <form action="{{ route('transactions.records.destroy', [$communication, $communicationRecord]) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger"
                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet enregistrement ?')">
                    <i class="bi bi-trash"></i> Supprimer
                </button>
            </form>
        </div>
    </div>

    <!-- Informations -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Informations de l'Enregistrement</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th style="width: 200px;">Identifiant</th>
                        <td>#{{ $communicationRecord->id }}</td>
                    </tr>
                    <tr>
                        <th>Communication</th>
                        <td>
                            {{ $communicationRecord->communication->code ?? 'Non spécifié' }}
                            @if($communicationRecord->communication->name)
                                <br><small class="text-muted">{{ $communicationRecord->communication->name }}</small>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Enregistrement</th>
                        <td>
                            {{ $communicationRecord->record->name ?? 'Non spécifié' }}
                            @if($communicationRecord->record->code)
                                <br><small class="text-muted">Code: {{ $communicationRecord->record->code }}</small>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Type de Document</th>
                        <td>
                            <span class="badge {{ $communicationRecord->is_original ? 'bg-success' : 'bg-info' }}">
                                {{ $communicationRecord->is_original ? 'Original' : 'Copie' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Date de Retour Prévue</th>
                        <td>
                            @if($communicationRecord->return_date)
                                {{ \Carbon\Carbon::parse($communicationRecord->return_date)->format('d/m/Y') }}
                                <br><small class="text-muted">{{ \Carbon\Carbon::parse($communicationRecord->return_date)->diffForHumans() }}</small>
                            @else
                                <span class="text-muted">Non définie</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Date de Retour Effective</th>
                        <td>
                            @if($communicationRecord->return_effective)
                                {{ \Carbon\Carbon::parse($communicationRecord->return_effective)->format('d/m/Y') }}
                                <br><small class="text-muted">Retourné {{ \Carbon\Carbon::parse($communicationRecord->return_effective)->diffForHumans() }}</small>
                            @else
                                <span class="badge bg-warning text-dark">En attente de retour</span>
                            @endif
                        </td>
                    </tr>
                    @if($communicationRecord->communication)
                    <tr>
                        <th>Opérateur</th>
                        <td>{{ $communicationRecord->communication->operator->name ?? 'Non assigné' }}</td>
                    </tr>
                    <tr>
                        <th>Statut Communication</th>
                        <td>{{ $communicationRecord->communication->status->name ?? 'Non défini' }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
