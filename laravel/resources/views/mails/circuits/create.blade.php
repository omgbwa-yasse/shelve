@extends('layouts.app')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase text-primary small fw-semibold mb-1">Gestion du courrier</div>
            <h3 class="mb-1"><i class="bi bi-diagram-3 me-2"></i>Faire circuler le courrier</h3>
            <div class="text-muted">{{ $mail->code }} - {{ $mail->name }}</div>
        </div>
        <a href="{{ $mail->mail_type === 'incoming' ? route('mails.incoming.show', $mail) : ($mail->mail_type === 'outgoing' ? route('mails.outgoing.show', $mail) : route('mail-send.show', $mail)) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour à la fiche
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle me-1"></i>Le circuit ne peut pas démarrer.</div><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="alert alert-info d-flex gap-3 align-items-start">
        <i class="bi bi-info-circle fs-4"></i>
        <div><strong>Ce choix porte sur le circuit métier du courrier.</strong> Les contrôles SMTP, antivirus et antispam relèvent de la messagerie technique et ne remplacent pas les visas administratifs.</div>
    </div>

    <form method="POST" action="{{ route('mails.circuits.store', $mail) }}" id="circuitForm">
        @csrf
        <div class="row g-4">
            <div class="col-xl-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3"><h5 class="mb-0"><span class="badge rounded-pill bg-primary me-2">1</span>Choisir le modèle</h5></div>
                    <div class="card-body">
                        @foreach(collect($templates)->groupBy('category') as $category => $group)
                            <div class="text-uppercase text-muted small fw-semibold mb-2 {{ !$loop->first ? 'mt-4' : '' }}">{{ $category }}</div>
                            <div class="row g-2">
                                @foreach($group as $code => $template)
                                    <div class="col-md-6">
                                        <input class="btn-check circuit-choice" type="radio" name="template_code" id="template_{{ $code }}" value="{{ $code }}" @checked(old('template_code') === $code) required>
                                        <label class="btn btn-outline-primary text-start w-100 h-100 p-3" for="template_{{ $code }}">
                                            <span class="d-block fw-semibold mb-1">{{ $template['name'] }}</span>
                                            <span class="d-block text-muted small">{{ $template['description'] }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white py-3"><h5 class="mb-0"><span class="badge rounded-pill bg-primary me-2">2</span>Préciser les règles conditionnelles</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Montant concerné (FCFA)</label>
                                <input type="number" min="0" step="1" name="amount" value="{{ old('amount') }}" class="form-control" placeholder="Ex. 7500000">
                                <div class="form-text">Seuil DG configuré : {{ number_format($threshold, 0, ',', ' ') }} FCFA.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Note à l’attention des valideurs</label>
                                <textarea name="note" class="form-control" rows="3" placeholder="Contexte, urgence ou résultat attendu">{{ old('note') }}</textarea>
                            </div>
                        </div>
                        <div class="row g-2 mt-2">
                            @foreach([
                                'finance_required' => ['Finance requise', 'bi-cash-coin'], 'legal_required' => ['Juridique requis', 'bi-shield-check'],
                                'dsi_required' => ['DSI requise', 'bi-pc-display'], 'dg_required' => ['DG requise', 'bi-building'],
                                'official_response' => ['Réponse officielle', 'bi-patch-check'], 'sensitive' => ['Accès confidentiel', 'bi-lock'],
                                'urgent' => ['Traitement urgent', 'bi-lightning-charge'],
                            ] as $field => [$label, $icon])
                                <div class="col-md-6 col-lg-4"><div class="form-check border rounded p-3 ps-5 h-100">
                                    <input type="hidden" name="{{ $field }}" value="0">
                                    <input class="form-check-input" type="checkbox" name="{{ $field }}" value="1" id="{{ $field }}" @checked(old($field))>
                                    <label class="form-check-label" for="{{ $field }}"><i class="bi {{ $icon }} me-1"></i>{{ $label }}</label>
                                </div></div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-5">
                <div class="card border-0 shadow-sm sticky-xl-top" style="top: 1rem;">
                    <div class="card-header bg-white py-3"><h5 class="mb-0"><span class="badge rounded-pill bg-primary me-2">3</span>Confirmer les valideurs</h5></div>
                    <div class="card-body">
                        <p class="text-muted small">Laissez « automatique » lorsque l’organigramme connaît déjà le responsable. Choisissez une personne pour remplacer ou compléter l’affectation.</p>
                        @foreach([
                            'n1' => 'N+1', 'n2' => 'N+2', 'service_manager' => 'Responsable métier', 'finance' => 'Finance',
                            'purchase' => 'Achats', 'legal' => 'Juridique / DPD', 'dsi' => 'DSI / Sécurité', 'dg' => 'Direction générale',
                        ] as $token => $label)
                            <div class="mb-3">
                                <label class="form-label mb-1">{{ $label }}</label>
                                <select name="validators[{{ $token }}]" class="form-select">
                                    <option value="">Automatique selon l’organigramme</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" @selected((string) old("validators.$token") === (string) $user->id)>{{ trim($user->name.' '.($user->surname ?? '')) }}{{ $user->currentOrganisation ? ' - '.$user->currentOrganisation->name : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach

                        <div id="customFields" class="border rounded p-3 bg-light mt-3 d-none">
                            <div class="fw-semibold mb-2">Circuit personnalisé</div>
                            <div class="mb-3"><label class="form-label">Organisation des étapes</label><select name="custom_mode" class="form-select"><option value="sequential">Séquentiel - l’un après l’autre</option><option value="parallel">Parallèle - tous en même temps</option></select></div>
                            @for($i = 0; $i < 4; $i++)
                                <div class="row g-2 mb-2">
                                    <div class="col-5"><input name="custom_labels[]" class="form-control" placeholder="Libellé {{ $i + 1 }}"></div>
                                    <div class="col-7"><select name="custom_validators[]" class="form-select"><option value="">Valideur {{ $i + 1 }}</option>@foreach($users as $user)<option value="{{ $user->id }}">{{ trim($user->name.' '.($user->surname ?? '')) }}</option>@endforeach</select></div>
                                </div>
                            @endfor
                        </div>
                    </div>
                    <div class="card-footer bg-white d-flex gap-2 justify-content-end py-3">
                        <a href="{{ route('mails.circulations.index') }}" class="btn btn-light">Annuler</a>
                        <button class="btn btn-primary px-4" type="submit"><i class="bi bi-play-circle me-1"></i>Démarrer le circuit</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const customFields = document.getElementById('customFields');
    const refresh = () => {
        const selected = document.querySelector('input[name="template_code"]:checked');
        customFields.classList.toggle('d-none', !selected || selected.value !== 'custom');
    };
    document.querySelectorAll('.circuit-choice').forEach(choice => choice.addEventListener('change', refresh));
    refresh();
});
</script>
@endpush
@endsection
