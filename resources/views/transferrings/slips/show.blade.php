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
    <h1 class="">Détails du bordereau du versement</h1>
    <div class="container my-2">
        <div class="card">
          <div  class="table-responsive">
            <table class="table table-light">
                <tbody>
                    <tr class="">
                        <td colspan="2">
                            <h4><strong> {{ $slip->code }} : {{ $slip->name }}</strong>  </h4>
                            Contenu : {{ $slip->description }}
                        </td>
                    </tr>
                    <tr class="" >
                        <td width="50%">
                            Service versant : <strong>{{ $slip->userOrganisation->name }}</strong> <br>
                            Intervenant : <strong>{{ $slip->user ? $slip->user->name : 'Aucun' }}</strong>
                        </td>
                        <td>
                            Service des archives : <strong>{{ $slip->officerOrganisation->name }} </strong> <br>
                            Responsable des archives : <strong>{{ $slip->officer->name }}</strong>
                        </td>
                    </tr>
                    <tr class="" >
                        <td colspan="2">
                            Statut : <strong>{{ $slip->slipStatus->name ?? 'Sans statut' }}</strong><br>
                            Date de réception :<strong> {{ $slip->received_date ?? 'A définir' }}</strong><br>
                            Approuvé : <strong>{{ $slip->is_approved ? 'oui' : 'non' }} </strong><br>
                            Date d'approbation : <strong>{{ $slip->approved_date ?? 'A définir'}}</strong>
                        </td>
                    </tr>
                </tbody>
            </table>

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
@endsection



