@extends('layouts.app')

<style>
    a {
        text-decoration: none;
        color: #0178d4;
    }
</style>

@section('content')
    <div class="container-fluid ">
        <h1 class="mb-4"><i class="bi bi-list-ul me-2"></i>Inventaire des archives {{ $title ?? ''}}</h1>
        <div class="d-flex justify-content-between align-items-center bg-light p-3 mb-3">
            <div class="d-flex align-items-center">
                <a href="#" id="cartBtn" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-cart me-1"></i>
                    Chariot
                </a>
                <a href="#" id="exportBtn" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-download me-1"></i>
                    Exporter
                </a>
                <a href="#" id="printBtn" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-printer me-1"></i>
                    Imprimer
                </a>
            </div>
            <div class="d-flex align-items-center">
                <a href="#" id="transferBtn" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-arrow-repeat me-1"></i>
                    Transférer
                </a>

                <a href="#" id="communicateBtn" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-envelope me-1"></i>
                    Communiquer
                </a>
                <a href="#" id="checkAllBtn" class="btn btn-light btn-sm">
                    <i class="bi bi-check-square me-1"></i>
                    Tout cocher
                </a>
            </div>
        </div>

        <div id="recordList">
            @foreach ($records as $record)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="{{$record->id}}" id="record-{{$record->id}}" />

                    <label class="form-check-label" for="record-{{$record->id}}">
                        <a href="{{ route('records.show', $record) }}">
                            <span style="font-size: 1.6em; font-weight: bold;">{{ $record->code }}  : {{ $record->name }} [{{ $record->level->name }}]</span>
                        </a>
                    </label>
                </div>
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-12">
                                <p class="card-text">
                                    <i class="bi bi-card-text me-2"></i> Content : {{ $record->content }}<br>
                                    <i class="bi bi-bar-chart-fill me-2"></i>Niveau de description :<a href="{{ route('records.sort')}}?categ=level&id={{ $record->level->id ?? ''}}">{{ $record->level->name ?? 'N/A' }}</a>
                                    <i class="bi bi-flag-fill me-2"></i>Statut :<a href=" {{route('records.sort')}}?categ=status&id={{ $record->status->id ?? 'N/A' }}">{{ $record->status->name ?? 'N/A' }}</a>
                                    <i class="bi bi-hdd-fill me-2"></i>Support :<a href="{{ route('records.sort')}}?categ=support&id={{ $record->support->id ?? 'N/A' }}">{{ $record->support->name ?? 'N/A' }}</a>
                                    <i class="bi bi-activity me-2"></i>Activité :<a href="{{ route('records.sort')}}?categ=activity&id={{ $record->activity->id ?? 'N/A' }}">{{ $record->activity->name ?? 'N/A' }}</a>
                                    <i class="bi bi-calendar-event me-2"></i>Dates :<a href="{{ route('records.sort')}}?categ=dates&id=">{{ $record->date_start ?? 'N/A' }} - {{ $record->date_end ?? 'N/A' }}</a>
                                    <i class="bi bi-geo-alt-fill me-2"></i>Contenant :<a href="{{ route('records.sort')}}?categ=container&id={{ $record->container->id ?? 'none' }}">{{ $record->container->name ?? 'Non conditionné' }}</a>
                                    <i class="bi bi-people-fill me-2"></i>Producteur :<a href="{{ route('records.sort')}}?categ=authors&id={{ $record->authors->pluck('id')->join('') }}">{{ $record->authors->pluck('name')->join(', ') ?? 'N/A' }}</a>
                                </p>
                                <strong>Vedettes : </strong>
                                <p class="card-text">
                                    @foreach($record->terms as $index => $term)
                                        <a href="{{ route('records.sort')}}?categ=term&id={{ $term->id ?? 'N/A' }}"> {{ $term->name ?? 'N/A' }} </a>
                                        @if(!$loop->last)
                                            {{ " ; " }}
                                        @endif
                                    @endforeach
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <footer class="bg-light py-3">
        <div class="container">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <li class="page-item {{ $records->currentPage() == 1 ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $records->previousPageUrl() }}" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    @for ($i = 1; $i <= $records->lastPage(); $i++)
                        <li class="page-item {{ $records->currentPage() == $i ? 'active' : '' }}">
                            <a class="page-link" href="{{ $records->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor
                    <li class="page-item {{ $records->currentPage() == $records->lastPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $records->nextPageUrl() }}" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </footer>




    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportModalLabel">Choisir le format d'exportation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="exportFormat" id="formatExcel" value="excel" checked>
                        <label class="form-check-label" for="formatExcel">
                            Excel
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="exportFormat" id="formatEAD" value="ead">
                        <label class="form-check-label" for="formatEAD">
                            EAD
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="exportFormat" id="formatSEDA" value="seda">
                        <label class="form-check-label" for="formatSEDA">
                            SEDA
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="confirmExport">Exporter</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="communicationModal" tabindex="-1" aria-labelledby="communicationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="communicationModalLabel">Créer une nouvelle communication</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('transactions.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="code" class="form-label">Code</label>
                            <input type="text" class="form-control" id="code" name="code" required>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="gcontent" class="form-label">Contenu</label>
                            <textarea class="form-control" id="gcontent" name="gcontent" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="user_id" class="form-label">Utilisateur</label>
                            <select class="form-select" id="user_id" name="user_id" required>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="return_date" class="form-label">Date de retour</label>
                            <input type="date" class="form-control" id="return_date" name="return_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="user_organisation_id" class="form-label">Organisation de l'utilisateur</label>
                            <select class="form-select" id="user_organisation_id" name="user_organisation_id" required>
                                @foreach($organisations as $organisation)
                                    <option value="{{ $organisation->id }}">{{ $organisation->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="selectedRecords"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="transferModal" tabindex="-1" aria-labelledby="transferModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="transferModalLabel">Créer un nouveau slip</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('slips.storetransfert') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="code" class="form-label">Code</label>
                            <input type="text" class="form-control" id="code" name="code" required>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="officer_organisation_id" class="form-label">Organisation de l'officier</label>
                            <select class="form-select" id="officer_organisation_id" name="officer_organisation_id" required>
                                @foreach($organisations as $organisation)
                                    <option value="{{ $organisation->id }}">{{ $organisation->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="user_organisation_id" class="form-label">Organisation de l'utilisateur</label>
                            <select class="form-select" id="user_organisation_id" name="user_organisation_id" required>
                                @foreach($organisations as $organisation)
                                    <option value="{{ $organisation->id }}">{{ $organisation->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="user_id" class="form-label">Utilisateur</label>
                            <select class="form-select" id="user_id" name="user_id" required>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="slip_status_id" class="form-label">Statut du slip</label>
                            <select class="form-select" id="slip_status_id" name="slip_status_id" required>
                                @foreach($slipStatuses as $status)
                                    <option value="{{ $status->id }}">{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="selectedRecords"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.getElementById('cartBtn').addEventListener('click', function(e) {
            e.preventDefault();
            let checkedRecords = Array.from(document.querySelectorAll('input[type="checkbox"]:checked'))
                .map(checkbox => checkbox.value);

            if (checkedRecords.length === 0) {
                alert('Veuillez sélectionner au moins un enregistrement.');
                return;
            }

            fetch('{{ route("dolly.createWithRecords") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ records: checkedRecords })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Un nouveau chariot a été créé avec les enregistrements sélectionnés.');
                    } else {
                        alert('Une erreur est survenue lors de la création du chariot.');
                    }
                });
        });

        document.getElementById('exportBtn').addEventListener('click', function(e) {
            e.preventDefault();
            let checkedRecords = Array.from(document.querySelectorAll('input[type="checkbox"]:checked'))
                .map(checkbox => checkbox.value);

            if (checkedRecords.length === 0) {
                alert('Veuillez sélectionner au moins un enregistrement à exporter.');
                return;
            }

            // Ouvrir le modal de sélection de format
            var exportModal = new bootstrap.Modal(document.getElementById('exportModal'));
            exportModal.show();
        });
        document.getElementById('printBtn').addEventListener('click', function(e) {
            e.preventDefault();
            let checkedRecords = Array.from(document.querySelectorAll('input[type="checkbox"]:checked'))
                .map(checkbox => checkbox.value);

            if (checkedRecords.length === 0) {
                alert('Veuillez sélectionner au moins un enregistrement à imprimer.');
                return;
            }

            fetch('{{ route("records.print") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ records: checkedRecords })
            })
                .then(response => response.blob())
                .then(blob => {
                    let url = window.URL.createObjectURL(blob);
                    let a = document.createElement('a');
                    a.href = url;
                    a.download = 'records_print.pdf';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                });
        });

        let checkAllBtn = document.getElementById('checkAllBtn');
        checkAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            let checkboxes = document.querySelectorAll('input[type="checkbox"]');
            let allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);

            checkboxes.forEach(function(checkbox) {
                checkbox.checked = !allChecked;
            });

            this.innerHTML = allChecked ? '<i class="bi bi-check-square me-1"></i>Tout cocher' : '<i class="bi bi-square me-1"></i>Tout décocher';
        });
        document.getElementById('confirmExport').addEventListener('click', function() {
            let checkedRecords = Array.from(document.querySelectorAll('input[type="checkbox"]:checked'))
                .map(checkbox => checkbox.value);
            let format = document.querySelector('input[name="exportFormat"]:checked').value;

            fetch(`{{ route("records.exportButton") }}?records=${checkedRecords.join(',')}&format=${format}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => {
                    if (!response.ok) throw new Error('Erreur réseau');

                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return response.json().then(data => {
                            throw new Error(data.error || 'Une erreur est survenue');
                        });
                    }

                    return response.blob();
                })
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;

                    // Déterminer l'extension du fichier en fonction du format
                    let extension;
                    switch (format) {
                        case 'excel':
                            extension = 'xlsx';
                            break;
                        case 'ead':
                            extension = 'xml';
                            break;
                        case 'seda':
                            extension = 'zip';
                            break;
                        default:
                            extension = 'txt';
                    }

                    a.download = `records_export.${extension}`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert(error.message || 'Une erreur est survenue lors de l\'exportation');
                });

            // Fermer le modal
            var exportModal = bootstrap.Modal.getInstance(document.getElementById('exportModal'));
            exportModal.hide();
        });

        document.getElementById('transferBtn').addEventListener('click', function(e) {
            e.preventDefault();
            let checkedRecords = Array.from(document.querySelectorAll('input[type="checkbox"]:checked'))
                .map(checkbox => checkbox.value);

            if (checkedRecords.length === 0) {
                alert('Veuillez sélectionner au moins un enregistrement.');
                return;
            }

            // Remplir le conteneur des records sélectionnés
            let selectedRecordsContainer = document.getElementById('selectedRecords');
            selectedRecordsContainer.innerHTML = '';
            checkedRecords.forEach(recordId => {
                const recordName = document.querySelector(`label[for="record-${recordId}"]`).textContent.trim();
                selectedRecordsContainer.innerHTML += `
                <div class="mb-3">
                    <h6>${recordName}</h6>
                    <input type="hidden" name="selected_records[]" value="${recordId}">
                </div>
            `;
            });

            // Afficher le modal
            var transferModal = new bootstrap.Modal(document.getElementById('transferModal'));
            transferModal.show();
        });
        document.addEventListener('DOMContentLoaded', function() {
            const communicateBtn = document.getElementById('communicateBtn');
            const modal = new bootstrap.Modal(document.getElementById('communicationModal'));
            const selectedRecordsContainer = document.getElementById('selectedRecords');

            communicateBtn.addEventListener('click', function(e) {
                e.preventDefault();
                let checkedRecords = Array.from(document.querySelectorAll('input[type="checkbox"]:checked'))
                    .map(checkbox => checkbox.value);

                if (checkedRecords.length === 0) {
                    alert('Veuillez sélectionner au moins un enregistrement.');
                    return;
                }

                // Remplir le conteneur des records sélectionnés
                selectedRecordsContainer.innerHTML = '';
                checkedRecords.forEach(recordId => {
                    const recordName = document.querySelector(`label[for="record-${recordId}"]`).textContent.trim();
                    selectedRecordsContainer.innerHTML += `
            <div class="mb-3">
                <h6>${recordName}</h6>
                <input type="hidden" name="selected_records[]" value="${recordId}">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="original-${recordId}" name="original[${recordId}]">
                    <label class="form-check-label" for="original-${recordId}">
                        Original
                    </label>
                </div>
                <div class="mb-2">
                    <label for="content-${recordId}" class="form-label">Contenu</label>
                    <textarea class="form-control" id="content-${recordId}" name="content[${recordId}]" rows="2"></textarea>
                </div>
            </div>
            `;
                });

                modal.show();
            });
        });
    </script>
@endpush
