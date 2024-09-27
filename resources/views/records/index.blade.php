@extends('layouts.app')

<style>
    a {
        text-decoration: none;
        color: #0178d4;
    }
</style>

@section('content')
    <div class="container-fluid mt-4">
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
                <a href="" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-list-task me-1"></i>
                    Tâches
                </a>
                <a href="" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-calendar-check me-1"></i>
                    Réservations
                </a>
            </div>
            <div class="d-flex align-items-center">
                <a href="#" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-arrow-repeat me-1"></i>
                    Transférer
                </a>
                <a href="#" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-envelope me-1"></i>
                    Communiqué
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
                            <span style="font-size: 1.6em; font-weight: bold;">{{ $record->code }}  : {{ $record->name }}</span>
                        </a>
                    </label>
                </div>
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-12">
                                <p class="card-text">
                                    <i class="bi bi-card-text me-2"></i> Content : {{ $record->content }}<br>
                                    <i class="bi bi-bar-chart-fill me-2"></i>Niveau de description :  <a href="{{ route('records.sort')}}?categ=level&id={{ $record->level->id ?? ''}}">{{ $record->level->name ?? 'N/A' }}</a>
                                    <i class="bi bi-flag-fill me-2"></i>Statut : <a href=" {{route('records.sort')}}?categ=status&id={{ $record->status->id ?? 'N/A' }}">{{ $record->status->name ?? 'N/A' }}</a>
                                    <i class="bi bi-hdd-fill me-2"></i>Support : <a href="{{ route('records.sort')}}?categ=support&id={{ $record->support->id ?? 'N/A' }}">{{ $record->support->name ?? 'N/A' }}</a>
                                    <i class="bi bi-activity me-2"></i>Activité : <a href="{{ route('records.sort')}}?categ=activity&id={{ $record->activity->id ?? 'N/A' }}">{{ $record->activity->name ?? 'N/A' }}</a>
                                    <i class="bi bi-calendar-event me-2"></i>Dates : <a href="{{ route('records.sort')}}?categ=dates&id=">{{ $record->date_start ?? 'N/A' }} - {{ $record->date_end ?? 'N/A' }}</a>
                                    <i class="bi bi-geo-alt-fill me-2"></i>Contenant : <a href="{{ route('records.sort')}}?categ=container&id={{ $record->container->id ?? 'none' }}">{{ $record->container->name ?? 'Non conditionné' }}</a>
                                    <i class="bi bi-people-fill me-2"></i>Producteur : <a href="{{ route('records.sort')}}?categ=authors&id={{ $record->authors->pluck('id')->join('') }}">{{ $record->authors->pluck('name')->join(', ') ?? 'N/A' }}</a>
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
                    {{ $records->links() }}
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

            window.location.href = '{{ route("records.exportButton") }}?records=' + checkedRecords.join(',');
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

        document.getElementById('confirmExport').addEventListener('click', function() {
            let checkedRecords = Array.from(document.querySelectorAll('input[type="checkbox"]:checked'))
                .map(checkbox => checkbox.value);
            let format = document.querySelector('input[name="exportFormat"]:checked').value;

            // Au lieu de rediriger, faisons une requête fetch
            fetch(`{{ route("records.exportButton") }}?records=${checkedRecords.join(',')}&format=${format}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => {
                    if (response.ok) return response.blob();
                    throw new Error('Erreur réseau');
                })
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = `records_export.${format === 'excel' ? 'xlsx' : format}`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Une erreur est survenue lors de l\'exportation');
                });

            // Fermer le modal
            var exportModal = bootstrap.Modal.getInstance(document.getElementById('exportModal'));
            exportModal.hide();
        });
    </script>
@endpush
