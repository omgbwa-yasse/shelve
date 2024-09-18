@extends('layouts.app')
<style>
        .card {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border: none;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }
        .btn-icon {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-icon i {
            margin-right: 0.5rem;
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.3rem 0.6rem;
        }
    </style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add smooth scrolling to all links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Add tooltips to status badges
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
@section('content')
    <div class="container my-5">
        <h1 class="mb-4">Détails du bordereau du versement</h1>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ $slip->code }} - {{ $slip->name }}</h5>
            </div>
            <div class="card-body">
                <p class="card-text">{{ $slip->description }}</p>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-building me-2"></i>Service versant</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Organisation:</strong> {{ $slip->userOrganisation->name }}</p>
                                <p><strong>Utilisateur:</strong> {{ $slip->user ? $slip->user->name : 'Aucun' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-archive me-2"></i>Service d'archives</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Organisation:</strong> {{ $slip->officerOrganisation->name }}</p>
                                <p><strong>Agent:</strong> {{ $slip->officer->name }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Statut du transfert</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Statut:</strong>
                                    <span class="badge bg-primary status-badge">{{ $slip->slipStatus->name ?? 'N/A' }}</span>
                                </p>
                                <p><strong>Reçu:</strong>
                                    <span class="badge {{ $slip->is_received ? 'bg-success' : 'bg-danger' }} status-badge">
                                    {{ $slip->is_received ? 'Oui' : 'Non' }}
                                </span>
                                </p>
                                <p><strong>Date de réception:</strong> {{ $slip->received_date ?? 'Aucune' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Approuvé:</strong>
                                    <span class="badge {{ $slip->is_approved ? 'bg-success' : 'bg-warning' }} status-badge">
                                    {{ $slip->is_approved ? 'Oui' : 'Non' }}
                                </span>
                                </p>
                                <p><strong>Date d'approbation:</strong> {{ $slip->approved_date ?? 'Aucune' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="{{ route('slips.index') }}" class="btn btn-secondary btn-icon">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                    <a href="{{ route('slips.edit', $slip->id) }}" class="btn btn-warning btn-icon">
                        <i class="fas fa-edit"></i> Modifier
                    </a>
                    <button type="button" class="btn btn-danger btn-icon" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="fas fa-trash-alt"></i> Supprimer
                    </button>
                    <a href="{{ route('slips.records.create', $slip) }}" class="btn btn-primary btn-icon">
                        <i class="fas fa-plus"></i> Ajouter des documents
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmer la suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Êtes-vous sûr de vouloir supprimer ce bordereau ?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form action="{{ route('slips.destroy', $slip->id) }}" method="POST" style="display: inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection



