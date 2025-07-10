@extends('layouts.app')

@push('styles')
<link href="{{ asset('css/mail-actions.css') }}" rel="stylesheet">
<!-- CSS pour l'export PDF -->
<style>
    @media print {
        .mail-actions-bar,
        .btn-group,
        .no-print {
            display: none !important;
        }

        .container {
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
        }

        .badge {
            border: 1px solid #6c757d !important;
        }
    }

    .pdf-content {
        background: white;
        padding: 20px;
    }
</style>
@endpush

@push('scripts')
<!-- Script pour l'export PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
@endpush

@section('content')
    <div class="container pdf-content" id="mailContent">
        <!-- En-tête avec navigation -->
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <h1 class="h3">Détails du courrier entrant</h1>
            <div class="btn-group">
                <a href="{{ route('mails.incoming.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Retour à la liste
                </a>
            </div>
        </div>

        <!-- En-tête pour PDF -->
        <div class="d-print-block d-none mb-4">
            <div class="text-center">
                <h2>Détails du courrier entrant</h2>
                <p class="text-muted">Généré le {{ now()->format('d/m/Y à H:i') }}</p>
            </div>
        </div>

        <!-- Actions rapides - Barre horizontale compacte -->
        <div class="mail-actions-bar">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <!-- Actions principales -->
                <div class="d-flex flex-wrap gap-2">
                    <span class="mail-actions-label">
                        <i class="bi bi-lightning-fill text-warning"></i> Actions :
                    </span>

                    <a href="{{ route('mails.incoming.edit', $mail->id) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil"></i> Modifier
                    </a>

                    @if($mail->attachments->count() > 0)
                        <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#attachmentsModal">
                            <i class="bi bi-paperclip"></i> Pièces jointes
                            <span class="badge bg-white text-info">{{ $mail->attachments->count() }}</span>
                        </button>
                    @endif

                    <button class="btn btn-success btn-sm" onclick="printMail()">
                        <i class="bi bi-printer"></i> Imprimer
                    </button>

                    <button class="btn btn-secondary btn-sm" onclick="downloadPDF()">
                        <i class="bi bi-file-pdf"></i> PDF
                    </button>
                </div>

                <!-- Action de suppression alignée à droite -->
                <div class="ms-auto">
                    <form action="{{ route('mails.incoming.destroy', $mail->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce courrier ?')">
                            <i class="bi bi-trash"></i> Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Informations principales -->
        <div class="row">
            <!-- Colonne de gauche - Informations générales -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="bi bi-info-circle"></i> Informations générales</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>Code :</strong>
                                <span class="badge bg-secondary ms-2">{{ $mail->code }}</span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Date :</strong>
                                <span class="ms-2">{{ $mail->date->format('d/m/Y') }}</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <strong>Nom :</strong>
                            <div class="mt-1">{{ $mail->name }}</div>
                        </div>

                        @if($mail->description)
                            <div class="mb-3">
                                <strong>Description :</strong>
                                <div class="mt-1">{{ $mail->description }}</div>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <strong>Type de document :</strong>
                                <div class="mt-1">
                                    <span class="badge bg-info">{{ ucfirst($mail->document_type) }}</span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <strong>Typologie :</strong>
                                <div class="mt-1">
                                    @if($mail->typology)
                                        <span class="badge bg-primary">{{ $mail->typology->name }}</span>
                                    @else
                                        <span class="text-muted">Non définie</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <strong>Statut :</strong>
                                <div class="mt-1">
                                    @if($mail->status)
                                        <span class="badge bg-success">{{ ucfirst($mail->status->value) }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($mail->priority || $mail->action)
                            <div class="row">
                                @if($mail->priority)
                                    <div class="col-md-6 mb-3">
                                        <strong>Priorité :</strong>
                                        <div class="mt-1">
                                            <span class="badge bg-warning">{{ $mail->priority->name }}</span>
                                            <small class="text-muted">({{ $mail->priority->duration }} jours)</small>
                                        </div>
                                    </div>
                                @endif
                                @if($mail->action)
                                    <div class="col-md-6 mb-3">
                                        <strong>Action :</strong>
                                        <div class="mt-1">
                                            <span class="badge bg-success">{{ $mail->action->name }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Colonne de droite - Informations expéditeur et livraison -->
            <div class="col-md-4">
                <!-- Expéditeur -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-person"></i> Expéditeur</h6>
                    </div>
                    <div class="card-body">
                        @if($mail->externalSender)
                            <div class="mb-2">
                                <strong>{{ $mail->externalSender->full_name }}</strong>
                            </div>
                            @if($mail->externalSender->position)
                                <div class="text-muted mb-1">{{ $mail->externalSender->position }}</div>
                            @endif
                            @if($mail->externalSender->email)
                                <div><i class="bi bi-envelope"></i> {{ $mail->externalSender->email }}</div>
                            @endif
                            @if($mail->externalSender->phone)
                                <div><i class="bi bi-telephone"></i> {{ $mail->externalSender->phone }}</div>
                            @endif
                            @if($mail->externalSender->organization)
                                <div class="mt-2">
                                    <small class="text-muted">Organisation :</small><br>
                                    <strong>{{ $mail->externalSender->organization->name }}</strong>
                                </div>
                            @endif
                        @elseif($mail->externalSenderOrganization)
                            <div class="mb-2">
                                <strong>{{ $mail->externalSenderOrganization->name }}</strong>
                            </div>
                            @if($mail->externalSenderOrganization->email)
                                <div><i class="bi bi-envelope"></i> {{ $mail->externalSenderOrganization->email }}</div>
                            @endif
                            @if($mail->externalSenderOrganization->phone)
                                <div><i class="bi bi-telephone"></i> {{ $mail->externalSenderOrganization->phone }}</div>
                            @endif
                            @if($mail->externalSenderOrganization->city)
                                <div><i class="bi bi-geo-alt"></i> {{ $mail->externalSenderOrganization->city }}</div>
                            @endif
                        @elseif($mail->senderOrganisation)
                            <div class="mb-2">
                                <strong>{{ $mail->senderOrganisation->name }}</strong>
                            </div>
                            <div class="text-muted">Organisation interne</div>
                        @else
                            <div class="text-muted">Expéditeur non défini</div>
                        @endif
                    </div>
                </div>

                <!-- Informations de livraison -->
                @if($mail->delivery_method || $mail->tracking_number || $mail->received_at)
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0"><i class="bi bi-truck"></i> Livraison</h6>
                        </div>
                        <div class="card-body">
                            @if($mail->delivery_method)
                                <div class="mb-2">
                                    <strong>Méthode :</strong> {{ $mail->delivery_method }}
                                </div>
                            @endif
                            @if($mail->tracking_number)
                                <div class="mb-2">
                                    <strong>N° de suivi :</strong> {{ $mail->tracking_number }}
                                </div>
                            @endif
                            @if($mail->received_at)
                                <div class="mb-2">
                                    <strong>Reçu le :</strong> {{ $mail->received_at->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Pièces jointes -->
        @if($mail->attachments->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-paperclip"></i> Pièces jointes ({{ $mail->attachments->count() }})</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($mail->attachments as $attachment)
                            <div class="col-md-3 mb-3">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        @if($attachment->thumbnail_path)
                                            <img src="{{ Storage::url($attachment->thumbnail_path) }}" alt="Aperçu de {{ $attachment->name }}" class="img-fluid mb-2" style="max-height: 100px;">
                                        @else
                                            <i class="bi bi-file-earmark fs-1 text-muted"></i>
                                        @endif
                                        <h6 class="card-title">{{ $attachment->name }}</h6>
                                        <p class="card-text">
                                            <small class="text-muted">{{ number_format($attachment->size / 1024, 1) }} KB</small>
                                        </p>
                                        <a href="{{ Storage::url($attachment->path) }}" class="btn btn-sm btn-primary" target="_blank">
                                            <i class="bi bi-download"></i> Télécharger
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Modal pour les pièces jointes -->
    @if($mail->attachments->count() > 0)
        <div class="modal fade" id="attachmentsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Pièces jointes</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="list-group">
                            @foreach($mail->attachments as $attachment)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $attachment->name }}</h6>
                                        <small class="text-muted">{{ number_format($attachment->size / 1024, 1) }} KB - {{ $attachment->mime_type }}</small>
                                    </div>
                                    <a href="{{ Storage::url($attachment->path) }}" class="btn btn-sm btn-primary" target="_blank">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        function downloadPDF() {
            // Configuration pour l'export PDF
            const element = document.getElementById('mailContent');
            const options = {
                margin: [10, 10, 10, 10],
                filename: `courrier-entrant-${@json($mail->code ?? 'sans-code')}-${new Date().toISOString().split('T')[0]}.pdf`,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: {
                    scale: 2,
                    useCORS: true,
                    letterRendering: true
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'portrait'
                }
            };

            // Afficher un indicateur de chargement
            const loadingIndicator = document.createElement('div');
            loadingIndicator.innerHTML = `
                <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
                     style="background: rgba(0,0,0,0.5); z-index: 9999;">
                    <div class="bg-white p-4 rounded shadow">
                        <div class="d-flex align-items-center">
                            <div class="spinner-border spinner-border-sm me-3" role="status"></div>
                            <span>Génération du PDF en cours...</span>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(loadingIndicator);

            // Générer et télécharger le PDF
            html2pdf().set(options).from(element).save().then(() => {
                // Supprimer l'indicateur de chargement
                document.body.removeChild(loadingIndicator);
            }).catch(error => {
                console.error('Erreur lors de la génération du PDF:', error);
                document.body.removeChild(loadingIndicator);
                alert('Erreur lors de la génération du PDF. Veuillez réessayer.');
            });
        }

        // Fonction alternative d'export via le serveur (si besoin)
        function downloadServerPDF() {
            const mailId = @json($mail->id);
            window.open(`/mails/incoming/${mailId}/pdf`, '_blank');
        }

        // Fonction pour l'impression optimisée
        function printMail() {
            // Masquer temporairement les éléments non imprimables
            const norintElements = document.querySelectorAll('.no-print');
            const originalDisplay = [];

            norintElements.forEach((el, index) => {
                originalDisplay[index] = el.style.display;
                el.style.display = 'none';
            });

            // Lancer l'impression
            window.print();

            // Restaurer l'affichage des éléments
            norintElements.forEach((el, index) => {
                el.style.display = originalDisplay[index];
            });
        }
    </script>
@endsection
