@extends('layouts.app')

@section('content')
<div class="container shadow-sm p-4 bg-white rounded">
    <h1 class="mb-4 text-primary">Désigner un intérimaire</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('organisation-interims.store') }}" method="POST">
        @csrf

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="organisation_id" class="form-label">Service / Direction</label>
                <select name="organisation_id" id="organisation_id" class="form-select" required>
                    <option value="">Choisir une entité</option>
                    @foreach($organisations as $org)
                        <option value="{{ $org->id }}" {{ old('organisation_id') == $org->id ? 'selected' : '' }}>
                            {{ $org->name }} ({{ $org->code }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="titular_user_id" class="form-label">Responsable titulaire</label>
                <select name="titular_user_id" id="titular_user_id" class="form-select" required>
                    <option value="">Choisir le titulaire</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('titular_user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} {{ $user->surname }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Plusieurs intérimaires possibles : chacun gère un volet des attributions --}}
        @php
            $oldInterims = old('interims', [['interim_user_id' => '', 'scope' => '']]);
            if (count($oldInterims) < 2) {
                $oldInterims[] = ['interim_user_id' => '', 'scope' => ''];
            }
            $oldPrimary = (int) old('primary_index', 0);
        @endphp

        <div class="card border-0 bg-light mb-3">
            <div class="card-body">
                <h2 class="h6 text-uppercase text-muted mb-1">Intérimaires et volets délégués</h2>
                <p class="small text-muted mb-3">
                    Vous pouvez désigner plusieurs intérimaires : par exemple un sous-directeur
                    sur le volet technique et un autre sur le volet administratif. Le volet peut être
                    une <strong>activité du plan de classement</strong> de la direction choisie.
                    L'intérimaire <strong>principal</strong> est celui vers qui le courrier du service
                    est automatiquement routé.
                </p>

                <div id="interims-list">
                    @foreach($oldInterims as $i => $row)
                        <div class="row align-items-end interim-row mb-3 pb-2 border-bottom">
                            <div class="col-md-5 mb-2">
                                <label class="form-label">Intérimaire {{ $i + 1 }}</label>
                                <select name="interims[{{ $i }}][interim_user_id]" class="form-select">
                                    <option value="">— Aucun —</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ ($row['interim_user_id'] ?? '') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} {{ $user->surname }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5 mb-2">
                                <label class="form-label">Activité déléguée <span class="text-muted">(plan de classement)</span></label>
                                <select name="interims[{{ $i }}][activity_id]" class="form-select activity-select"
                                        data-selected="{{ $row['activity_id'] ?? '' }}">
                                    <option value="">Toutes les attributions</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="primary_index"
                                           value="{{ $i }}" id="primary_{{ $i }}"
                                           {{ $oldPrimary === $i ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="primary_{{ $i }}">
                                        Principal
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-10 mb-2">
                                <label class="form-label">Précision du volet <span class="text-muted">(facultatif)</span></label>
                                <input type="text" name="interims[{{ $i }}][scope]" class="form-control"
                                       value="{{ $row['scope'] ?? '' }}"
                                       placeholder="ex. Volet technique / Volet administratif">
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="button" class="btn btn-sm btn-outline-primary" id="add-interim">
                    <i class="bi bi-plus-lg"></i> Ajouter un intérimaire
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="start_date" class="form-label">Début de l'intérim</label>
                <input type="date" name="start_date" id="start_date" class="form-control"
                       value="{{ old('start_date', now()->format('Y-m-d')) }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="end_date" class="form-label">Fin de l'intérim <span class="text-muted">(facultatif)</span></label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ old('end_date') }}">
                <small class="form-text text-muted">Laisser vide pour un intérim sans date de fin définie.</small>
            </div>
        </div>

        <div class="mb-3">
            <label for="reason" class="form-label">Motif <span class="text-muted">(facultatif)</span></label>
            <textarea name="reason" id="reason" class="form-control" rows="3"
                      placeholder="Congé, mission, formation...">{{ old('reason') }}</textarea>
        </div>

        <div class="d-flex justify-content-between">
            <a href="{{ route('organisation-interims.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Retour
            </a>
            <button type="submit" class="btn btn-success">
                <i class="bi bi-check2-circle"></i> Enregistrer l'intérim
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const list = document.getElementById('interims-list');
    const addBtn = document.getElementById('add-interim');
    if (!list || !addBtn) return;

    const MAX = 5;

    // Activités du plan de classement, par direction : le select d'activité
    // ne propose que celles de l'entité sélectionnée.
    const ACTIVITIES = @json($activitiesByOrganisation);
    const orgSelect = document.getElementById('organisation_id');

    function fillActivities(select) {
        const orgId = orgSelect ? orgSelect.value : '';
        const wanted = select.dataset.selected || select.value || '';
        const items = (orgId && ACTIVITIES[orgId]) ? ACTIVITIES[orgId] : [];

        select.innerHTML = '<option value="">Toutes les attributions</option>';
        items.forEach(function (activity) {
            const opt = document.createElement('option');
            opt.value = activity.id;
            opt.textContent = activity.name;
            if (String(activity.id) === String(wanted)) opt.selected = true;
            select.appendChild(opt);
        });

        if (!items.length) {
            select.options[0].textContent = orgId
                ? 'Aucune activité rattachée à cette entité'
                : 'Choisissez d\'abord une entité';
        }
    }

    function refreshAllActivities() {
        list.querySelectorAll('.activity-select').forEach(fillActivities);
    }

    if (orgSelect) {
        orgSelect.addEventListener('change', refreshAllActivities);
    }
    refreshAllActivities();

    addBtn.addEventListener('click', function () {
        const rows = list.querySelectorAll('.interim-row');
        if (rows.length >= MAX) {
            addBtn.disabled = true;
            return;
        }

        const index = rows.length;
        const clone = rows[0].cloneNode(true);

        clone.querySelectorAll('select, input').forEach(function (field) {
            if (field.name === 'primary_index') {
                field.value = index;
                field.id = 'primary_' + index;
                field.checked = false;
                const label = clone.querySelector('label[for^="primary_"]');
                if (label) label.setAttribute('for', 'primary_' + index);
                return;
            }
            field.name = field.name.replace(/interims\[\d+\]/, 'interims[' + index + ']');
            field.value = '';
            if (field.classList.contains('activity-select')) {
                field.dataset.selected = '';
            }
        });

        const title = clone.querySelector('label');
        if (title) title.textContent = 'Intérimaire ' + (index + 1);

        list.appendChild(clone);
        clone.querySelectorAll('.activity-select').forEach(fillActivities);

        if (list.querySelectorAll('.interim-row').length >= MAX) {
            addBtn.disabled = true;
        }
    });
});
</script>
@endpush
