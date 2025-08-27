@extends('layouts.app')
@section('content')
<div class="container">
    <h1 class="mt-5">Parapheur : fiche</h1>
    <table class="table">
        <tbody>
            <tr>
                <td>Reference : {{  $mailBatch->code  }}</td>
            </tr>
            <tr>
                <td>Désignation : {{  $mailBatch->name  }}</td>
            </tr>
        </tbody>
    </table>
    <div class="d-flex flex-wrap gap-2 mt-3">
        <a href="{{ route('batch.index') }}" class="btn btn-secondary">Back</a>
        <a href="{{ route('batch.edit', $mailBatch->id) }}" class="btn btn-warning">Edit</a>

        <form action="{{ route('batch.destroy', $mailBatch->id) }}" method="POST" style="display: inline-block;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this mail batch?')">Delete</button>
        </form>

        <a href="{{ route('batch.mail.create', $mailBatch) }}" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addMailModal">Ajouter des courrier</a>

        <div class="ms-auto d-flex gap-2">
            <a href="{{ route('batch.export.pdf', $mailBatch) }}" class="btn btn-info">Export (pdf)</a>
            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#transferToBoxModal">Tranfer vers boites</a>
            <a href="#" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#transferToDollyModal">Transfert vers un dolly</a>
        </div>
    </div>
</div>
@foreach ( $mailBatch->mails as $mail)

        <div class="card text-start mt-1">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <h4 class="card-title mb-2">
                        @php
                            $showRoute = '#';
                            if ($mail->isIncoming()) {
                                $showRoute = route('mail-received.show', $mail->id);
                            } elseif ($mail->isOutgoing()) {
                                $showRoute = route('mail-send.show', $mail->id);
                            } elseif ($mail->external_sender_id || $mail->external_recipient_id) {
                                // Pour les courriers externes, utiliser les routes externes si disponibles
                                $showRoute = route('mails.incoming.show', $mail->id);
                            } else {
                                // Fallback pour les courriers internes
                                $showRoute = route('mails.incoming.show', $mail->id);
                            }
                        @endphp
                        <a href="{{ $showRoute }}">
                            <strong>{{ $mail->code }} : {{ $mail->name }}
                                @if($mail->attachments->count() > 1 )
                                    ({{ $mail->attachments->count() }} fichiers)
                                @elseif($mail->attachments->count() == 1 )
                                    ({{ $mail->attachments->count() }} fichier)
                                @endif
                            </strong>
                        </a>
                    </h4>
                    <p class="card-text mb-1">
                        Du {{ $mail->date }} Par
                        @foreach($mail->authors as $author)
                            {{ $author->name }}
                        @endforeach
                    </p>
                    <p class="mb-1">{{ $mail->description }}</p>
                    <p class="mb-0">
                        <small>
                            Priority: {{ $mail->priority ? $mail->priority->name : 'N/A' }}
                            | Mail Type: {{ $mail->type ? $mail->type->name : 'N/A' }}
                            | Business Type: {{ $mail->typology ? $mail->typology->name : 'N/A' }}
                            | Nature: {{ $mail->document_type ?: 'N/A' }}
                        </small>
                    </p>
                </div>
                <form action="{{ route('batch.mail.destroy', [$mailBatch, $mail->id]) }}" method="POST" style="display: inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm ms-2" onclick="return confirm('Are you sure you want to delete this batch mail?')">Retirer</button>
                </form>
            </div>
        </div>

@endforeach

{{-- Modals --}}
{{-- Add Mail Modal --}}
<div class="modal fade" id="addMailModal" tabindex="-1" aria-labelledby="addMailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMailModalLabel">Ajouter un courrier au parapheur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addMailForm">
                    @csrf
                    <div class="mb-3">
                        <label for="mail_search" class="form-label">Rechercher un courrier</label>
                        <input type="text" class="form-control" id="mail_search" placeholder="Entrez un code, un nom...">
                        <div id="mail_search_results" class="mt-2"></div>
                    </div>
                    <input type="hidden" name="mail_id" id="selected_mail_id">
                </form>
                <div id="addMailError" class="alert alert-danger d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" id="saveMailButton">Ajouter</button>
            </div>
        </div>
    </div>
</div>

{{-- Transfer to Box Modal --}}
<div class="modal fade" id="transferToBoxModal" tabindex="-1" aria-labelledby="transferToBoxModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transferToBoxModalLabel">Transférer vers une ou plusieurs boîte(s)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                                <ul class="nav nav-tabs" id="boxTab">
                    <li class="nav-item">
                        <button class="nav-link active" id="select-box-tab" data-bs-toggle="tab" data-bs-target="#select-box" type="button" role="tab" aria-controls="select-box" aria-selected="true">Sélectionner</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="create-box-tab" data-bs-toggle="tab" data-bs-target="#create-box" type="button" role="tab" aria-controls="create-box" aria-selected="false">Créer</button>
                    </li>
                </ul>
                <div class="tab-content" id="boxTabContent">
                    <div class="tab-pane fade show active" id="select-box" role="tabpanel" aria-labelledby="select-box-tab">
                        <div class="my-3">
                            <input type="text" id="box-search" class="form-control" placeholder="Rechercher une boîte...">
                        </div>
                        <div id="box-list-container" style="max-height: 300px; overflow-y: auto;">
                            <div class="text-center">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="create-box" role="tabpanel" aria-labelledby="create-box-tab">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" id="saveTransferToBoxButton">Transférer</button>
            </div>
        </div>
    </div>
</div>

{{-- Transfer to Dolly Modal --}}
<div class="modal fade" id="transferToDollyModal" tabindex="-1" aria-labelledby="transferToDollyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transferToDollyModalLabel">Transférer vers un ou plusieurs chariot(s)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                 <ul class="nav nav-tabs" id="dollyTab">
                    <li class="nav-item">
                        <button class="nav-link active" id="select-dolly-tab" data-bs-toggle="tab" data-bs-target="#select-dolly" type="button" role="tab" aria-controls="select-dolly" aria-selected="true">Sélectionner</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="create-dolly-tab" data-bs-toggle="tab" data-bs-target="#create-dolly" type="button" role="tab" aria-controls="create-dolly" aria-selected="false">Créer</button>
                    </li>
                </ul>
                <div class="tab-content" id="dollyTabContent">
                    <div class="tab-pane fade show active" id="select-dolly" role="tabpanel" aria-labelledby="select-dolly-tab">
                        <div class="my-3">
                            <input type="text" id="dolly-search" class="form-control" placeholder="Rechercher un chariot...">
                        </div>
                        <div id="dolly-list-container" style="max-height: 300px; overflow-y: auto;">
                            <div class="text-center">
                                <output class="spinner-border">
                                    <span class="visually-hidden">Loading...</span>
                                </output>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="create-dolly" role="tabpanel" aria-labelledby="create-dolly-tab">
                        <form id="createDollyForm" class="mt-3">
                            <div class="mb-3">
                                <label for="new_dolly_code" class="form-label">Code</label>
                                <input type="text" class="form-control" id="new_dolly_code" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_dolly_name" class="form-label">Nom</label>
                                <input type="text" class="form-control" id="new_dolly_name" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Créer et sélectionner</button>
                        </form>
                    </div>
                </div>
                <div id="transferDollyError" class="alert alert-danger d-none mt-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" id="saveTransferToDollyButton">Transférer</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const batchId = {{ $mailBatch->id }};

    // Helper to get CSRF token
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    }

    // Modal Handlers
    const addMailModal = new bootstrap.Modal(document.getElementById('addMailModal'));
    const transferToBoxModal = new bootstrap.Modal(document.getElementById('transferToBoxModal'));
    const transferToDollyModal = new bootstrap.Modal(document.getElementById('transferToDollyModal'));

    // --- Add Mail Logic ---
    // Add search functionality for mails
    const mailSearchInput = document.getElementById('mail_search');
    const mailSearchResults = document.getElementById('mail_search_results');
    const selectedMailIdInput = document.getElementById('selected_mail_id');

    if (mailSearchInput) {
        mailSearchInput.addEventListener('input', function() {
            const query = this.value.trim();

            if (query.length < 2) {
                mailSearchResults.innerHTML = '';
                selectedMailIdInput.value = '';
                return;
            }

            // Show loading indicator
            mailSearchResults.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Recherche...</span></div></div>';

            fetch(`/mails/batch/${batchId}/available-mails?q=${encodeURIComponent(query)}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.mails.length > 0) {
                    let html = '<div class="list-group">';
                    data.mails.forEach(mail => {
                        html += `
                            <button type="button" class="list-group-item list-group-item-action mail-result-item"
                                    data-mail-id="${mail.id}"
                                    data-mail-code="${mail.code}"
                                    data-mail-name="${mail.name}">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">${mail.code} - ${mail.name}</h6>
                                    <small class="text-muted">${mail.date}</small>
                                </div>
                                <p class="mb-1">${mail.description || ''}</p>
                                <small class="text-muted">
                                    ${mail.direction ? `<span class="badge ${mail.direction === 'Émis' ? 'bg-success' : 'bg-primary'}">${mail.direction}</span> | ` : ''}
                                    Type: ${mail.type?.name || 'N/A'} |
                                    Priorité: ${mail.priority?.name || 'N/A'}
                                </small>
                            </button>`;
                    });
                    html += '</div>';
                    mailSearchResults.innerHTML = html;

                    // Add click handlers for mail selection
                    document.querySelectorAll('.mail-result-item').forEach(item => {
                        item.addEventListener('click', function() {
                            const mailId = this.dataset.mailId;
                            const mailCode = this.dataset.mailCode;
                            const mailName = this.dataset.mailName;

                            // Update the input field and hidden field
                            mailSearchInput.value = `${mailCode} - ${mailName}`;
                            selectedMailIdInput.value = mailId;

                            // Clear results
                            mailSearchResults.innerHTML = '';

                            // Update visual feedback
                            document.querySelectorAll('.mail-result-item').forEach(el => el.classList.remove('active'));
                            this.classList.add('active');
                        });
                    });
                } else {
                    mailSearchResults.innerHTML = '<div class="text-muted p-2">Aucun courrier trouvé ou tous les courriers sont déjà dans ce parapheur.</div>';
                }
            })
            .catch(error => {
                console.error('Error searching mails:', error);
                mailSearchResults.innerHTML = '<div class="text-danger p-2">Erreur lors de la recherche.</div>';
            });
        });
    }

    const saveMailButton = document.getElementById('saveMailButton');
    if(saveMailButton) {
        saveMailButton.addEventListener('click', function() {
            const mailId = document.getElementById('selected_mail_id').value;
            if (!mailId) {
                document.getElementById('addMailError').textContent = 'Veuillez sélectionner un courrier.';
                document.getElementById('addMailError').classList.remove('d-none');
                return;
            }
            document.getElementById('addMailError').classList.add('d-none');

            fetch(`/mails/batches/${batchId}/mail`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ mail_id: mailId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    addMailModal.hide();
                    location.reload(); // Reload to see the new mail
                } else {
                    document.getElementById('addMailError').textContent = data.message || 'Une erreur est survenue.';
                    document.getElementById('addMailError').classList.remove('d-none');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('addMailError').textContent = 'Une erreur réseau est survenue.';
                document.getElementById('addMailError').classList.remove('d-none');
            });
        });
    }

    // Clear search when modal is hidden
    document.getElementById('addMailModal').addEventListener('hidden.bs.modal', function() {
        mailSearchInput.value = '';
        selectedMailIdInput.value = '';
        mailSearchResults.innerHTML = '';
        document.getElementById('addMailError').classList.add('d-none');
    });

    // --- Transfer to Box Logic ---
    const transferToBoxModalEl = document.getElementById('transferToBoxModal');
    if(transferToBoxModalEl) {
        transferToBoxModalEl.addEventListener('show.bs.modal', function () {
            loadSelectableList("{{ route('mail-container.list') }}", '#box-list-container', 'boxes');
        });

        document.getElementById('box-search').addEventListener('keyup', function() {
            loadSelectableList("{{ route('mail-container.list') }}?q=" + this.value, '#box-list-container', 'boxes');
        });

        document.getElementById('createBoxForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const code = document.getElementById('new_box_code').value;
            const name = document.getElementById('new_box_name').value;
            fetch("{{ route('mail-container.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ code: code, name: name })
            })
            .then(response => response.json())
            .then(data => {
                if (data.id) {
                    // Creation successful, reload the list and switch back to the select tab
                    loadSelectableList("{{ route('mail-container.list') }}", '#box-list-container', 'boxes');
                    const tab = new bootstrap.Tab(document.getElementById('select-box-tab'));
                    tab.show();
                } else {
                    // Handle error
                    const errorDiv = document.getElementById('transferBoxError');
                    errorDiv.textContent = data.message || 'Erreur lors de la création.';
                    errorDiv.classList.remove('d-none');
                }
            });
        });

        document.getElementById('saveTransferToBoxButton').addEventListener('click', function() {
            const selected = Array.from(document.querySelectorAll('#box-list-container input:checked')).map(el => el.value);
            if (selected.length === 0) {
                document.getElementById('transferBoxError').textContent = 'Veuillez sélectionner au moins une boîte.';
                document.getElementById('transferBoxError').classList.remove('d-none');
                return;
            }
            // Implement your AJAX logic for transfer here
            alert(`Logique de transfert vers les boîtes ${selected.join(', ')} à implémenter.`);
            transferToBoxModal.hide();
        });
    }


    // --- Transfer to Dolly Logic ---
    const transferToDollyModalEl = document.getElementById('transferToDollyModal');
    if(transferToDollyModalEl) {
        transferToDollyModalEl.addEventListener('show.bs.modal', function () {
            loadSelectableList('/api/dollies', '#dolly-list-container', 'dollies');
        });

        document.getElementById('dolly-search').addEventListener('keyup', function() {
            loadSelectableList('/api/dollies?q=' + this.value, '#dolly-list-container', 'dollies');
        });

        document.getElementById('createDollyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const name = document.getElementById('new_dolly_name').value;
            const description = document.getElementById('new_dolly_description').value;
            fetch("/api/dollies", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ name: name, description: description })
            })
            .then(response => response.json())
            .then(data => {
                if (data.id) {
                    // Creation successful, reload the list and switch back to the select tab
                    loadSelectableList('/api/dollies', '#dolly-list-container', 'dollies');
                    const tab = new bootstrap.Tab(document.getElementById('select-dolly-tab'));
                    tab.show();
                    document.getElementById('createDollyForm').reset();
                } else {
                    // Handle error
                    const errorDiv = document.getElementById('transferDollyError');
                    errorDiv.textContent = data.message || 'Erreur lors de la création.';
                    errorDiv.classList.remove('d-none');
                }
            });
        });

        document.getElementById('saveTransferToDollyButton').addEventListener('click', function() {
            const selected = Array.from(document.querySelectorAll('#dolly-list-container input:checked')).map(el => el.value);
            if (selected.length === 0) {
                document.getElementById('transferDollyError').textContent = 'Veuillez sélectionner au moins un chariot.';
                document.getElementById('transferDollyError').classList.remove('d-none');
                return;
            }
            // Implement your AJAX logic for transfer here
            alert(`Logique de transfert vers les chariots ${selected.join(', ')} à implémenter.`);
            transferToDollyModal.hide();
        });
    }

    function loadSelectableList(url, containerSelector, type) {
        const container = document.querySelector(containerSelector);
        container.innerHTML = `<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>`;

        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        })
        .then(response => response.json())
        .then(data => {
            let items = data; // API returns an array directly
            let html = '<ul class="list-group">';
            if (items.length > 0) {
                items.forEach(item => {
                    html += `
                        <li class="list-group-item">
                            <input class="form-check-input me-1" type="checkbox" value="${item.id}" id="${type}-${item.id}">
                            <label class="form-check-label stretched-link" for="${type}-${item.id}">${item.code} - ${item.name || ''}</label>
                        </li>`;
                });
            } else {
                html += '<li class="list-group-item text-muted">Aucun élément trouvé.</li>';
            }
            html += '</ul>';
            container.innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading list:', error);
            container.innerHTML = '<div class="alert alert-danger">Erreur de chargement de la liste.</div>';
        });
    }
});
</script>
@endpush
@endsection
