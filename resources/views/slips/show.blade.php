@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="">
                <div class="">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0">Détails du bordereau du versement</h2>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h3 class="border-bottom pb-2">{{ $slip->code }}: {{ $slip->name }}</h3>
                                <p class="lead">{{ $slip->description }}</p>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h4 class="card-title text-primary">Service versant</h4>
                                        <p class="card-text"><strong><a href="{{ route('organisations.show', $slip->userOrganisation->id) }}">{{ $slip->userOrganisation->name }}</a></strong></p>
                                        <p class="card-text">Intervenant: <strong>{!!  $slip->user ? '<a href="' . route('users.show', $slip->user->id) . '">' . $slip->user->name . '</a>' : 'Aucun' !!}</strong></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h4 class="card-title text-primary">Service des archives</h4>
                                        <p class="card-text"><strong><a href="{{ route('organisations.show', $slip->officerOrganisation->id) }}">{{ $slip->officerOrganisation->name }}</a></strong></p>
                                        <p class="card-text">Responsable: <strong><a href="{{ route('users.show', $slip->officer->id) }}">{{ $slip->officer->name }}</a></strong></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h4 class="text-primary mb-3">Informations supplémentaires</h4>
                                <div class="table-responsive">
                                    <table class="table table-borderless d-flex">
                                        <tbody class="d-flex flex-wrap align-items-center justify-content-between w-100">
                                            <tr class="d-flex">
                                                <th scope="row" class="">Statut</th>
                                                <td>
                                                    <span class="badge bg-info">
                                                        {{ $slip->slipStatus ? $slip->slipStatus->name : 'Sans statut' }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr class="d-flex">
                                                <th scope="row">Date de réception</th>
                                                <td>{{ $slip->received_date ?? 'A définir' }}</td>
                                            </tr>
                                            <tr class="d-flex">
                                                <th scope="row">Approuvé</th>
                                                <td>
                                                    <span class="badge bg-{{ $slip->is_approved ? 'success' : 'danger' }}">
                                                        {{ $slip->is_approved ? 'Oui' : 'Non' }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr class="d-flex">
                                                <th scope="row">Date d'approbation</th>
                                                <td>{{ $slip->approved_date ?? 'A définir' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>


                        <div class="mt-5">
                            <h3 class="text-primary border-bottom pb-2 mb-3">Documents associés</h3>
                            @if($slip->records->isNotEmpty())
                                    @foreach($slip->records as $record)
                                        <div class="d-flex align-items-center justify-content-between border p-3 mb-3">

                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="record-{{ $record->id }}">
                                                <label class="form-check-label" for="record-{{ $record->id }}">
                                                    <a href="{{  route('slips.records.show', [$slip,$record] ) }}">
                                                    <strong>{{ $record->code }} : {{ $record->name }} de {{ $record->author->name?? 'Nan' }}</strong>
                                                        @if (is_null($record->date_exact) && is_null($record->date_end))
                                                             du {{ $record->date_start }}
                                                        @elseif (is_null($record->date_exact) && !is_null($record->date_end))
                                                            de {{ $record->date_start }} - {{ $record->date_end }}
                                                        @else
                                                            du {{ $record->date_exact }}
                                                        @endif
                                                    </a>
                                                </label>
                                            </div>
                                            <div class="ms-3">
                                                {{ $record->content }}
                                            </div>

                                        </div>
                                    @endforeach
                            @else
                                <p class="text-muted">Aucun document associé à ce bordereau.</p>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('slips.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Retour
                            </a>
                            <div>

                                @if($slip->is_received  == FALSE && $slip->is_approved  == FALSE && $slip->is_integrated  == FALSE)
                                    <a href="{{ route('slips.records.create', $slip) }}" class="btn btn-success">
                                        <i class="fas bi-file-upload me-2"></i>Ajouter des documents
                                    </a>
                                    <a href="{{ route('slips.reception') }}?id={{ $slip->id }}" class="btn btn-success">
                                            <i class="fas bi-inbox me-2"></i> Receptionner
                                    </a>
                                    <a href="{{ route('slips.edit', $slip->id) }}" class="btn btn-warning me-2">
                                        <i class="fas fa-edit me-2"></i>Modifier
                                    </a>
                                    <button type="button" class="btn btn-danger me-2" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                        <i class="fas bi-trash-alt me-2"></i>Supprimer
                                    </button>

                                @elseif($slip->is_received  == TRUE && $slip->is_approved  == FALSE && $slip->is_integrated  == FALSE)
                                    <a href="{{ route('slips.approve') }}?id={{ $slip->id }}" class="btn btn-success">
                                        <i class="fas bi-check-circle me-2"></i> Approuver
                                    </a>
                                @elseif ($slip->is_received  == TRUE && $slip->is_approved  == TRUE && $slip->is_integrated  == FALSE )
                                    <a href="{{ route('slips.integrate') }}?id={{ $slip->id }}" class="btn btn-success">
                                        <i class="fas bi-folder-plus me-2"></i>Intégrer dans le repertoire
                                    </a>
                                @elseif ($slip->is_received  == TRUE && $slip->is_approved  == TRUE && $slip->is_integrated  == FALSE)
                                    <a href="" class="btn btn-success">
                                        <i class="fas bi-folder-plus me-2"></i>Imprimer le bordereau ***
                                    </a>
                                @endif

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmer la suppression</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer ce bordereau ?</p>
                    <p class="text-danger"><strong>Cette action est irréversible.</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form action="{{ route('slips.destroy', $slip->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .card {
            transition: box-shadow 0.3s ease-in-out;
        }
        .card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }
        .table th {
            font-weight: 600;
        }
        .badge {
            font-size: 0.9em;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
        });
    </script>
@endpush
