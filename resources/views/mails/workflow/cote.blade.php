@extends('layouts.app')

@section('content')
<div class="container shadow-sm p-4 bg-white rounded">
    <h1 class="mb-4 text-primary">Cotation du courrier</h1>

    <div class="mb-4">
        <strong>Objet :</strong> {{ $mail->name }}<br>
        <strong>Code :</strong> {{ $mail->code }}<br>
        <strong>Date :</strong> {{ optional($mail->date)->format('d/m/Y') }}
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('mails.workflow.cote', $mail->id) }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Affecter à une ou plusieurs directions <span class="text-danger">*</span></label>
            <div class="border rounded p-2" style="max-height: 320px; overflow-y: auto;">
                @php
                    $alreadyCoted = $mail->cotations->pluck('organisation_id')->map(fn($i)=>(int)$i)->all();
                    $activitesCotees = $mail->cotations->pluck('activity_id', 'organisation_id');
                    // Re-cotation : on repart de la consigne déjà donnée, sinon le DG
                    // renverrait un formulaire vide et effacerait ce qu'il avait écrit.
                    $cotationCourante = $mail->cotations->first();
                @endphp
                @foreach($organisations as $org)
                    <div class="row align-items-center py-1 border-bottom">
                        <div class="col-md-6">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox"
                                       name="assigned_organisation_ids[]"
                                       value="{{ $org->id }}"
                                       id="org_{{ $org->id }}"
                                       {{ in_array($org->id, old('assigned_organisation_ids', $alreadyCoted)) ? 'checked' : '' }}>
                                <label class="form-check-label" for="org_{{ $org->id }}">
                                    {{ $org->name }} <span class="text-muted small">({{ $org->code }})</span>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            @if($org->activities->count())
                                {{-- Activité du plan de classement au titre de laquelle cette direction traitera le courrier --}}
                                <select name="activity_ids[{{ $org->id }}]" class="form-select form-select-sm">
                                    <option value="">Activité (plan de classement)…</option>
                                    @foreach($org->activities as $activity)
                                        <option value="{{ $activity->id }}"
                                            {{ (int) old('activity_ids.' . $org->id, $activitesCotees[$org->id] ?? null) === (int) $activity->id ? 'selected' : '' }}>
                                            {{ $activity->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <span class="text-muted small">Aucune activité rattachée à cette entité</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            <small class="form-text text-muted">
                Chaque direction cochée traitera le courrier et validera sa propre réception ; vous suivez chaque
                réponse individuellement. L'<strong>activité</strong> rattache le courrier au <strong>plan de
                classement</strong> de la direction (utile pour le classement et la durée de conservation).
            </small>
        </div>

        <div class="mb-3">
            <label for="action_id" class="form-label">Instruction du DG</label>
            <select name="action_id" id="action_id" class="form-select">
                <option value="">Aucune instruction</option>
                @foreach($instructions as $instruction)
                    <option value="{{ $instruction->id }}"
                        {{ (int) old('action_id', $cotationCourante->action_id ?? null) === (int) $instruction->id ? 'selected' : '' }}>
                        {{ $instruction->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="instruction" class="form-label">Précisions (facultatif)</label>
            <textarea name="instruction" id="instruction" class="form-control" rows="3">{{ old('instruction', $cotationCourante->instruction ?? '') }}</textarea>
        </div>

        <div class="d-flex justify-content-between">
            <a href="{{ route('mails.incoming.show', $mail->id) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Retour
            </a>
            <button type="submit" class="btn btn-success">
                <i class="bi bi-check2-circle"></i> Coter le courrier
            </button>
        </div>
    </form>
</div>
@endsection
