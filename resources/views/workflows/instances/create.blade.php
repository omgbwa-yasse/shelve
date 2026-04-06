@extends('layouts.app')

@push('styles')
<style>
    .launch-card {
        max-width: 680px; margin: 0 auto;
        background: #fff; border: 1px solid #e9ecef;
        border-radius: 18px; box-shadow: 0 4px 20px rgba(0,0,0,.07);
        overflow: hidden;
    }
    .launch-header {
        background: linear-gradient(135deg, #198754 0%, #0b5e31 100%);
        padding: 24px 28px; color: #fff;
    }
    .launch-header h2 { font-weight: 700; margin: 0; font-size: 1.3rem; }
    .launch-header p  { margin: 4px 0 0; opacity: .85; font-size: .88rem; }
    .launch-body { padding: 28px; }

    .field-label { font-weight: 600; font-size: .88rem; color: #374151; margin-bottom: 5px; }
    .field-hint  { font-size: .75rem; color: #9ca3af; margin-top: 3px; }

    /* Definition preview card */
    .def-preview {
        background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 12px;
        padding: 14px 16px; margin-top: 10px; display: none;
    }
    .def-preview.show { display: block; }
    .def-step-line {
        display: flex; align-items: center; gap: 8px;
        font-size: .82rem; color: #374151; margin-bottom: 6px;
    }
    .def-step-num {
        width: 22px; height: 22px; border-radius: 50%;
        background: #0d6efd; color: #fff;
        font-size: .7rem; font-weight: 700;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }

    .tip-box {
        background: #f0f7ff; border-left: 3px solid #0d6efd;
        border-radius: 0 8px 8px 0; padding: 12px 16px;
        font-size: .83rem; color: #444;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <a href="{{ route('workflows.instances.index') }}"
       class="text-muted text-decoration-none small d-inline-flex align-items-center gap-1 mb-3">
        <i class="bi bi-arrow-left"></i> {{ __('Retour aux instances') }}
    </a>

    <div class="launch-card">
        {{-- Header --}}
        <div class="launch-header">
            <div class="d-flex align-items-center gap-3">
                <div style="width:48px;height:48px;border-radius:12px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:1.5rem;">
                    <i class="bi bi-play-fill"></i>
                </div>
                <div>
                    <h2>{{ __('Démarrer un processus') }}</h2>
                    <p>{{ __('Lancer une nouvelle instance d\'exécution') }}</p>
                </div>
            </div>
        </div>

        <div class="launch-body">
            <form action="{{ route('workflows.instances.store') }}" method="POST">
                @csrf

                {{-- Definition selector --}}
                <div class="mb-4">
                    <label for="definition_id" class="field-label">
                        {{ __('Modèle de processus') }} <span class="text-danger">*</span>
                    </label>
                    <select class="form-select @error('definition_id') is-invalid @enderror"
                            id="definition_id" name="definition_id" required
                            style="border-radius:10px;">
                        <option value="">{{ __('Sélectionnez un processus…') }}</option>
                        @foreach($definitions as $definition)
                            <option value="{{ $definition->id }}"
                                    data-name="{{ $definition->name }}"
                                    data-desc="{{ $definition->description }}"
                                    data-status="{{ $definition->status }}"
                                    {{ old('definition_id', request('definition')) == $definition->id ? 'selected' : '' }}>
                                {{ $definition->name }}
                                @if($definition->status === 'draft') — {{ __('Brouillon') }} @endif
                            </option>
                        @endforeach
                    </select>
                    @error('definition_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="field-hint">{{ __('Seuls les processus actifs sont recommandés.') }}</div>

                    {{-- Preview panel --}}
                    <div id="defPreview" class="def-preview">
                        <div class="fw-semibold small mb-2" id="prevName"></div>
                        <p class="text-muted small mb-2" id="prevDesc" style="line-height:1.5;"></p>
                        <div id="prevSteps"></div>
                    </div>
                </div>

                {{-- Instance name --}}
                <div class="mb-4">
                    <label for="name" class="field-label">
                        {{ __('Nom de cette exécution') }} <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name') }}"
                           required maxlength="190"
                           placeholder="{{ __('Ex: Validation contrat prestataire – Avril 2026') }}"
                           style="border-radius:10px;">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="field-hint">{{ __('Donnez un nom unique pour retrouver cette instance facilement.') }}</div>
                </div>

                {{-- Info tip --}}
                <div class="tip-box mb-4">
                    <i class="bi bi-info-circle me-1 text-primary"></i>
                    {{ __('Une fois démarré, le processus créera automatiquement les tâches définies dans le modèle et les assignera aux responsables désignés.') }}
                </div>

                {{-- Buttons --}}
                <div class="d-flex justify-content-between">
                    <a href="{{ route('workflows.instances.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-1">
                        <i class="bi bi-x"></i> {{ __('Annuler') }}
                    </a>
                    <button type="submit" class="btn btn-success btn-lg d-flex align-items-center gap-2" style="border-radius:10px;">
                        <i class="bi bi-play-fill"></i> {{ __('Lancer le processus') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const defSelect = document.getElementById('definition_id');
const preview   = document.getElementById('defPreview');

defSelect.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    if (!this.value) { preview.classList.remove('show'); return; }
    document.getElementById('prevName').textContent = opt.dataset.name || '';
    document.getElementById('prevDesc').textContent = opt.dataset.desc || '';
    // Auto-suggest instance name
    const nameInput = document.getElementById('name');
    if (!nameInput.value) {
        const now = new Date();
        const mon = now.toLocaleString('fr-FR', { month: 'long', year: 'numeric' });
        nameInput.value = (opt.dataset.name || '') + ' – ' + mon.charAt(0).toUpperCase() + mon.slice(1);
    }
    preview.classList.add('show');
});

// Trigger on page load if value is pre-selected
if (defSelect.value) defSelect.dispatchEvent(new Event('change'));
</script>
@endpush
