@extends('layouts.app')

@push('styles')
<style>
    .create-card {
        max-width: 720px;
        margin: 0 auto;
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 18px;
        box-shadow: 0 4px 20px rgba(0,0,0,.07);
        overflow: hidden;
    }
    .create-header {
        background: linear-gradient(135deg, #0d6efd 0%, #0a4fbf 100%);
        padding: 28px 32px;
        color: #fff;
    }
    .create-header h2 { font-weight: 700; margin: 0; }
    .create-header p  { margin: 4px 0 0; opacity: .85; font-size: .9rem; }

    .create-body { padding: 28px 32px; }

    /* Steps steps indicator */
    .wizard-steps {
        display: flex;
        gap: 0;
        margin-bottom: 28px;
    }
    .wizard-step {
        flex: 1;
        text-align: center;
        position: relative;
    }
    .wizard-step:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 16px; left: 50%; right: -50%;
        height: 2px; background: #dee2e6; z-index: 0;
    }
    .ws-circle {
        width: 32px; height: 32px;
        border-radius: 50%;
        background: #dee2e6; color: #6c757d;
        font-weight: 700; font-size: .85rem;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 6px;
        position: relative; z-index: 1;
    }
    .ws-active .ws-circle  { background: #0d6efd; color: #fff; }
    .ws-done .ws-circle    { background: #198754; color: #fff; }
    .ws-label { font-size: .72rem; color: #6c757d; }
    .ws-active .ws-label { color: #0d6efd; font-weight: 600; }

    .form-label-lg { font-weight: 600; font-size: .92rem; }

    .tip-box {
        background: #f0f7ff;
        border-left: 3px solid #0d6efd;
        border-radius: 0 8px 8px 0;
        padding: 12px 16px;
        font-size: .83rem;
        color: #444;
    }
    .tip-box strong { color: #0d6efd; }
</style>
@endpush

@section('content')
<div class="container">
    <div class="py-4">

        {{-- Back link --}}
        <a href="{{ route('workflows.definitions.index') }}"
           class="text-muted text-decoration-none small d-inline-flex align-items-center gap-1 mb-3">
            <i class="bi bi-arrow-left"></i> {{ __('Retour aux processus') }}
        </a>

        <div class="create-card">

            {{-- Header --}}
            <div class="create-header">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:52px;height:52px;border-radius:14px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:1.6rem;">
                        <i class="bi bi-diagram-3"></i>
                    </div>
                    <div>
                        <h2>{{ __('Nouveau processus') }}</h2>
                        <p>{{ __('Définissez un modèle de processus réutilisable') }}</p>
                    </div>
                </div>
            </div>

            {{-- Wizard steps indicator --}}
            <div class="create-body pb-0 pt-4">
                <div class="wizard-steps mb-4">
                    <div class="wizard-step ws-active">
                        <div class="ws-circle">1</div>
                        <div class="ws-label">{{ __('Informations') }}</div>
                    </div>
                    <div class="wizard-step">
                        <div class="ws-circle">2</div>
                        <div class="ws-label">{{ __('Étapes') }}</div>
                    </div>
                    <div class="wizard-step">
                        <div class="ws-circle">3</div>
                        <div class="ws-label">{{ __('Activer') }}</div>
                    </div>
                </div>
            </div>

            {{-- Form --}}
            <div class="create-body pt-0">
                <form action="{{ route('workflows.definitions.store') }}" method="POST">
                    @csrf
                    {{-- hidden default BPMN --}}
                    <input type="hidden" name="bpmn_xml" value='<?xml version="1.0" encoding="UTF-8"?>
<bpmn:definitions xmlns:bpmn="http://www.omg.org/spec/BPMN/20100524/MODEL" id="Definitions_empty">
  <bpmn:process id="Process_1" isExecutable="true">
    <bpmn:startEvent id="start"/>
    <bpmn:endEvent id="end"/>
    <bpmn:sequenceFlow id="flow_start_end" sourceRef="start" targetRef="end"/>
  </bpmn:process>
</bpmn:definitions>'>

                    {{-- Name --}}
                    <div class="mb-4">
                        <label for="name" class="form-label form-label-lg">
                            {{ __('Nom du processus') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control form-control-lg @error('name') is-invalid @enderror"
                               id="name" name="name"
                               value="{{ old('name') }}"
                               required maxlength="100"
                               placeholder="{{ __('Ex: Validation de documents entrants') }}"
                               style="border-radius:10px;">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">{{ __('Choisissez un nom clair qui décrit l\'objectif du processus.') }}</div>
                    </div>

                    {{-- Description --}}
                    <div class="mb-4">
                        <label for="description" class="form-label form-label-lg">
                            {{ __('Description') }}
                            <span class="text-muted fw-normal small">{{ __('(optionnel)') }}</span>
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="3"
                                  placeholder="{{ __('Décrivez le déroulement général et l\'objectif de ce processus…') }}"
                                  style="border-radius:10px;">{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Status --}}
                    <div class="mb-4">
                        <label class="form-label form-label-lg">{{ __('Statut initial') }} <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3">
                            <label class="flex-fill" style="cursor:pointer;">
                                <input type="radio" name="status" value="draft"
                                       {{ old('status', 'draft') == 'draft' ? 'checked' : '' }}
                                       class="d-none status-radio" id="status_draft">
                                <div class="p-3 border rounded-3 text-center status-option" for="status_draft"
                                     style="transition:all .2s;">
                                    <div class="fs-4">📝</div>
                                    <div class="fw-semibold small mt-1">{{ __('Brouillon') }}</div>
                                    <div class="text-muted" style="font-size:.72rem;">{{ __('En cours de conception') }}</div>
                                </div>
                            </label>
                            <label class="flex-fill" style="cursor:pointer;">
                                <input type="radio" name="status" value="active"
                                       {{ old('status') == 'active' ? 'checked' : '' }}
                                       class="d-none status-radio" id="status_active">
                                <div class="p-3 border rounded-3 text-center status-option" for="status_active"
                                     style="transition:all .2s;">
                                    <div class="fs-4">✅</div>
                                    <div class="fw-semibold small mt-1">{{ __('Actif') }}</div>
                                    <div class="text-muted" style="font-size:.72rem;">{{ __('Prêt à être lancé') }}</div>
                                </div>
                            </label>
                        </div>
                        @error('status') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    {{-- Tip --}}
                    <div class="tip-box mb-4">
                        <strong><i class="bi bi-lightbulb me-1"></i>{{ __('Étape suivante :') }}</strong>
                        {{ __('Après création, vous serez redirigé vers l\'éditeur d\'étapes pour définir les tâches du processus.') }}
                    </div>

                    {{-- Buttons --}}
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('workflows.definitions.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-1">
                            <i class="bi bi-x"></i> {{ __('Annuler') }}
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg d-flex align-items-center gap-2" style="border-radius:10px;">
                            <i class="bi bi-arrow-right-circle"></i> {{ __('Créer et configurer les étapes') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Visual radio state
document.querySelectorAll('.status-radio').forEach(radio => {
    radio.addEventListener('change', updateStatusOptions);
});
function updateStatusOptions() {
    document.querySelectorAll('.status-option').forEach(opt => {
        opt.style.background   = '';
        opt.style.borderColor  = '#dee2e6';
        opt.style.color        = '';
    });
    const checked = document.querySelector('.status-radio:checked');
    if(checked) {
        const opt = checked.nextElementSibling;
        opt.style.background   = '#e8f4fe';
        opt.style.borderColor  = '#0d6efd';
    }
}
updateStatusOptions();
</script>
@endpush
