@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h1><i class="bi bi-archive"></i> {{ $title ?? 'Archives' }}</h1>
            </div>
            <div class="col-auto">
                @can('records_create')
                    <a href="{{ route('records.create') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-plus-circle"></i> Nouvelle notice
                    </a>
                @endcan
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Barre d'actions de masse --}}
        <div class="card mb-3">
            <div class="card-body d-flex align-items-center gap-2 flex-wrap">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="checkAll">
                    <label class="form-check-label" for="checkAll">Tout sélectionner</label>
                </div>
                <span class="badge bg-primary" id="selectionCount">0 sélectionné(s)</span>
                <div class="ms-auto d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-outline-primary btn-sm action-btn" data-action="dolly" disabled>
                        <i class="bi bi-cart"></i> Chariot
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm action-btn" data-action="export" disabled>
                        <i class="bi bi-download"></i> Exporter
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm action-btn" data-action="print" disabled>
                        <i class="bi bi-printer"></i> Imprimer
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm action-btn" data-action="communicate" disabled>
                        <i class="bi bi-chat-dots"></i> Communiquer
                    </button>
                    <button type="button" class="btn btn-outline-warning btn-sm action-btn" data-action="transfer" disabled>
                        <i class="bi bi-arrow-left-right"></i> Transférer
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th style="width:40px"></th>
                        <th>Code</th>
                        <th>Nom</th>
                        <th>Type</th>
                        <th>Niveau</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        <tr class="clickable-row" data-href="{{ route('records.show', $record) }}">
                            <td class="no-click">
                                <input type="checkbox" class="record-select" value="{{ $record->id }}">
                            </td>
                            <td><span class="badge bg-secondary">{{ $record->code }}</span></td>
                            <td><a href="{{ route('records.show', $record) }}">{{ $record->name }}</a></td>
                            <td>
                                <span class="badge {{ $record->isContainer() ? 'bg-success' : 'bg-info' }}">
                                    {{ $record->type?->name ?? '—' }}
                                </span>
                            </td>
                            <td>{{ $record->level?->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Aucune notice trouvée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-3">
            {{ $records->links() }}
        </div>
    </div>

    {{-- Formulaires cachés Export / Impression --}}
    <form id="bulkExportForm" method="POST" action="{{ route('records.bulk-export') }}" class="d-none">@csrf</form>
    <form id="bulkPrintForm" method="POST" action="{{ route('records.bulk-print') }}" class="d-none">@csrf</form>

    {{-- Modale Transfert (bordereau) --}}
    <div class="modal fade" id="transferModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('records.bulk-transfer') }}" class="modal-content">
                @csrf
                <div id="transferIds"></div>
                <div class="modal-header">
                    <h5 class="modal-title">Transférer vers un bordereau</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Organisation destinataire</label>
                        <select name="user_organisation_id" class="form-select">
                            @foreach(\App\Models\Organisation::orderBy('name')->get() as $org)
                                <option value="{{ $org->id }}" {{ $org->id == auth()->user()->current_organisation_id ? 'selected' : '' }}>{{ $org->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button class="btn btn-warning"><i class="bi bi-arrow-left-right"></i> Transférer</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modale Communication --}}
    <div class="modal fade" id="communicateModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('records.bulk-communicate') }}" class="modal-content">
                @csrf
                <div id="communicateIds"></div>
                <div class="modal-header">
                    <h5 class="modal-title">Créer une communication</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contenu</label>
                        <textarea name="content" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date de retour</label>
                        <input type="date" name="return_date" class="form-control" value="{{ now()->addDays(30)->format('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Utilisateur (bénéficiaire) <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select" required>
                            @foreach(\App\Models\User::orderBy('name')->get() as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Organisation du bénéficiaire <span class="text-danger">*</span></label>
                        <select name="user_organisation_id" class="form-select" required>
                            @foreach(\App\Models\Organisation::orderBy('name')->get() as $org)
                                <option value="{{ $org->id }}">{{ $org->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button class="btn btn-info"><i class="bi bi-chat-dots"></i> Créer</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modale Chariot (Dolly) --}}
    <div class="modal fade" id="dollyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter au chariot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Chariot existant</label>
                        <select id="dollySelect" class="form-select">
                            <option value="">— Choisir —</option>
                        </select>
                    </div>
                    <div class="text-center my-1 text-muted">— ou —</div>
                    <div class="mb-3">
                        <label class="form-label">Nom du nouveau chariot</label>
                        <input type="text" id="newDollyName" class="form-control">
                    </div>
                    <div id="dollyResult" class="mt-2"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="dollyAddBtn"><i class="bi bi-cart"></i> Ajouter</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const checkAll = document.getElementById('checkAll');
            const selectionCount = document.getElementById('selectionCount');
            const actionBtns = document.querySelectorAll('.action-btn');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            function getSelected() {
                return Array.from(document.querySelectorAll('.record-select:checked')).map(c => c.value);
            }

            function updateUI() {
                const count = getSelected().length;
                selectionCount.textContent = count + ' sélectionné(s)';
                actionBtns.forEach(b => b.disabled = count === 0);
            }

            checkAll.addEventListener('change', function () {
                document.querySelectorAll('.record-select').forEach(c => c.checked = this.checked);
                updateUI();
            });

            document.addEventListener('change', function (e) {
                if (e.target.classList.contains('record-select')) updateUI();
            });

            function submitBulk(formId) {
                const form = document.getElementById(formId);
                form.querySelectorAll('input[name="record_ids[]"]').forEach(i => i.remove());
                getSelected().forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'record_ids[]';
                    input.value = id;
                    form.appendChild(input);
                });
                form.submit();
            }

            function fillIds(containerId) {
                document.getElementById(containerId).innerHTML = getSelected()
                    .map(id => '<input type="hidden" name="record_ids[]" value="' + id + '">').join('');
            }

            actionBtns.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (getSelected().length === 0) return;
                    const action = btn.dataset.action;

                    if (action === 'export') submitBulk('bulkExportForm');
                    else if (action === 'print') submitBulk('bulkPrintForm');
                    else if (action === 'transfer') {
                        fillIds('transferIds');
                        new bootstrap.Modal(document.getElementById('transferModal')).show();
                    } else if (action === 'communicate') {
                        fillIds('communicateIds');
                        new bootstrap.Modal(document.getElementById('communicateModal')).show();
                    } else if (action === 'dolly') {
                        loadDollies();
                        new bootstrap.Modal(document.getElementById('dollyModal')).show();
                    }
                });
            });

            // Chariot
            function loadDollies() {
                fetch('/dolly-handler/list?category=record', { headers: { 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(data => {
                        const select = document.getElementById('dollySelect');
                        select.innerHTML = '<option value="">— Choisir —</option>';
                        (Array.isArray(data) ? data : (data.dollies || [])).forEach(function (d) {
                            select.innerHTML += '<option value="' + d.id + '">' + d.name + '</option>';
                        });
                    })
                    .catch(() => {});
            }

            document.getElementById('dollyAddBtn').addEventListener('click', function () {
                const ids = getSelected();
                const dollyId = document.getElementById('dollySelect').value;
                const newName = document.getElementById('newDollyName').value.trim();
                const result = document.getElementById('dollyResult');

                if (!dollyId && !newName) {
                    result.innerHTML = '<div class="alert alert-warning py-2">Choisissez un chariot ou saisissez un nom.</div>';
                    return;
                }

                function addItems(id) {
                    fetch('/dolly-handler/add-items', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ dolly_id: id, category: 'record', items: ids })
                    })
                    .then(r => r.json())
                    .then(res => {
                        result.innerHTML = '<div class="alert alert-success py-2">' + (res.message || 'Ajouté au chariot.') + '</div>';
                        setTimeout(() => location.reload(), 800);
                    })
                    .catch(() => {
                        result.innerHTML = '<div class="alert alert-danger py-2">Erreur lors de l\'ajout.</div>';
                    });
                }

                if (dollyId) {
                    addItems(dollyId);
                } else {
                    fetch('/dolly-handler/create', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ name: newName })
                    })
                    .then(r => r.json())
                    .then(res => {
                        const newId = (res.id) || (res.data && res.data.id) || (res.dolly && res.dolly.id);
                        if (newId) addItems(newId);
                        else result.innerHTML = '<div class="alert alert-danger py-2">Impossible de créer le chariot.</div>';
                    })
                    .catch(() => {
                        result.innerHTML = '<div class="alert alert-danger py-2">Erreur lors de la création.</div>';
                    });
                }
            });
        })();
    </script>
@endpush
