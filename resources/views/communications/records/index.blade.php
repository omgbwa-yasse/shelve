@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <!-- En-tête avec bouton retour -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Retour
                </a>
                <h1 class="h3 mb-0">Communication Records</h1>
            </div>
            <a href="{{ route('transactions.records.create', $communication) }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-2"></i>Nouvel enregistrement
            </a>
        </div>

        <!-- Card contenant le tableau -->
        <div class="card">
            <div class="card-body">
                @if($communicationRecords->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Communication</th>
                                <th>Record</th>
                                <th>Original</th>
                                <th>Date de retour</th>
                                <th>Retour effectif</th>
                                <th class="text-end">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($communicationRecords as $record)
                                <tr>
                                    <td>{{ $record->id }}</td>
                                    <td>{{ $record->communication->name }}</td>
                                    <td>{{ $record->record->name }}</td>
                                    <td>
                                        @if($record->is_original)
                                            <span class="badge bg-success">Original</span>
                                        @else
                                            <span class="badge bg-secondary">Copie</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($record->return_date)
                                            <span class="text-monospace">
                                                {{ \Carbon\Carbon::parse($record->return_date)->format('d/m/Y') }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($record->return_effective)
                                            <span class="text-monospace">
                                                {{ \Carbon\Carbon::parse($record->return_effective)->format('d/m/Y') }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('transactions.records.show', [$communication, $record->id]) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye me-1"></i>Voir
                                            </a>

                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-folder2-open display-4 text-muted mb-3"></i>
                        <h4 class="text-muted">Aucun enregistrement trouvé</h4>
                        <p class="text-muted">Commencez par créer un nouvel enregistrement.</p>
                        <a href="{{ route('transactions.records.create', $communication) }}"
                           class="btn btn-primary">
                            <i class="bi bi-plus-lg me-2"></i>Créer un enregistrement
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
