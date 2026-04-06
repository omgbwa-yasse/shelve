@extends('layouts.app')

@push('styles')
<style>
    /* ── Step builder ─────────────────────────────────────────── */
    .step-builder { list-style: none; padding: 0; margin: 0; }

    .step-item {
        display: flex;
        align-items: stretch;
        gap: 0;
        margin-bottom: 0;
        position: relative;
    }

    /* Vertical connector line */
    .step-item:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 19px;
        top: 52px;
        bottom: -16px;
        width: 2px;
        background: #dee2e6;
        z-index: 0;
    }

    .step-number {
        width: 40px; height: 40px; min-width: 40px;
        border-radius: 50%;
        background: #0d6efd;
        color: #fff;
        font-weight: 700;
        font-size: .9rem;
        display: flex; align-items: center; justify-content: center;
        margin-top: 6px;
        margin-right: 16px;
        position: relative;
        z-index: 1;
        flex-shrink: 0;
    }
    .step-number.step-end-icon {
        background: #198754;
    }

    .step-card {
        flex: 1;
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 16px 18px;
        margin-bottom: 16px;
        box-shadow: 0 1px 6px rgba(0,0,0,.05);
        transition: box-shadow .2s;
    }
    .step-card:hover { box-shadow: 0 3px 14px rgba(0,0,0,.09); }
    .step-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
    .step-card-title { font-weight: 600; color: #1e293b; }

    .step-drag-handle { cursor: grab; color: #adb5bd; font-size: 1.1rem; padding: 4px 8px; }
    .step-drag-handle:active { cursor: grabbing; }

    /* start/end markers */
    .start-marker, .end-marker {
        display: flex; align-items: center; gap: 12px;
        padding: 10px 16px;
        border-radius: 10px;
        font-weight: 600; font-size: .9rem;
        margin-bottom: 8px;
    }
    .start-marker {
        background: #e0f0ff; color: #0553b1;
        border: 1.5px dashed #84b9f4;
    }
    .end-marker {
        background: #d1e7dd; color: #0a3622;
        border: 1.5px dashed #86c9a3;
        margin-top: 8px;
    }

    /* add step btn */
    .add-step-btn {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        width: 100%; padding: 12px;
        border: 2px dashed #0d6efd;
        border-radius: 10px;
        background: transparent; color: #0d6efd;
        font-weight: 600; cursor: pointer;
        transition: background .2s;
        margin-top: 4px;
        margin-bottom: 8px;
    }
    .add-step-btn:hover { background: #e8f0fe; }

    /* form fields inside step */
    .step-fields { display: flex; gap: 10px; flex-wrap: wrap; }
    .step-fields .form-control, .step-fields .form-select {
        border-radius: 8px; font-size: .87rem;
    }
    .field-title    { flex: 2; min-width: 180px; }
    .field-assignee { flex: 1.2; min-width: 150px; }

    /* section card */
    .section-card {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 16px;
        box-shadow: 0 2px 10px rgba(0,0,0,.05);
        overflow: hidden;
    }
    .section-card-header {
        padding: 16px 22px;
        border-bottom: 1px solid #f0f2f5;
        display: flex; align-items: center; gap: 10px;
    }
    .section-card-header h5 { margin: 0; font-size: .95rem; font-weight: 700; }
    .section-card-body { padding: 22px; }

    /* XML advanced collapsible */
    .xml-advanced {
        background: #f8f9fa;
        border: 1px solid #e2e6ea;
        border-radius: 10px;
        overflow: hidden;
    }
    .xml-advanced summary {
        padding: 10px 16px;
        cursor: pointer;
        font-size: .85rem;
        font-weight: 600;
        color: #495057;
        display: flex; align-items: center; gap: 8px;
    }
    .xml-advanced textarea { border: none; border-top: 1px solid #e2e6ea; border-radius: 0; font-size: .82rem; }

    .del-step-btn {
        background: #f8d7da; color: #842029;
        border: none; border-radius: 8px;
        width: 30px; height: 30px;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; font-size: .85rem;
        flex-shrink: 0;
        transition: opacity .15s;
    }
    .del-step-btn:hover { opacity: .8; }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- ── Header ─────────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-pencil-square text-warning"></i> {{ __('Modifier le processus') }}
            </h2>
            <p class="text-muted small mb-0">{{ $definition->name }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('workflows.definitions.show', $definition) }}" class="btn btn-outline-secondary d-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i> {{ __('Retour') }}
            </a>
        </div>
    </div>

    <form action="{{ route('workflows.definitions.update', $definition) }}" method="POST" id="workflowEditForm">
        @csrf
        @method('PUT')
        {{-- bpmn_xml generated from JS --}}
        <input type="hidden" name="bpmn_xml" id="bpmn_xml_hidden">

        <div class="row g-4">

            {{-- ── Col left: Steps Builder ─────────────── --}}
            <div class="col-lg-8">

                {{-- Info card --}}
                <div class="section-card mb-4">
                    <div class="section-card-header">
                        <i class="bi bi-info-circle text-primary"></i>
                        <h5>{{ __('Informations générales') }}</h5>
                    </div>
                    <div class="section-card-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="name" class="form-label fw-semibold small">{{ __('Nom du processus') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name"
                                       value="{{ old('name', $definition->name) }}"
                                       required maxlength="100"
                                       placeholder="{{ __('Ex: Validation de document') }}">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="status" class="form-label fw-semibold small">{{ __('Statut') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="draft"    {{ old('status', $definition->status) == 'draft'    ? 'selected' : '' }}>📝 {{ __('Brouillon') }}</option>
                                    <option value="active"   {{ old('status', $definition->status) == 'active'   ? 'selected' : '' }}>✅ {{ __('Actif') }}</option>
                                    <option value="archived" {{ old('status', $definition->status) == 'archived' ? 'selected' : '' }}>📦 {{ __('Archivé') }}</option>
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label for="description" class="form-label fw-semibold small">{{ __('Description') }}</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="2"
                                          placeholder="{{ __('Décrivez l\'objectif de ce processus…') }}">{{ old('description', $definition->description) }}</textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Steps Builder --}}
                <div class="section-card">
                    <div class="section-card-header">
                        <i class="bi bi-list-ol text-primary"></i>
                        <h5>{{ __('Étapes du processus') }}</h5>
                        <span class="ms-auto text-muted small">{{ __('Faites glisser pour réordonner') }}</span>
                    </div>
                    <div class="section-card-body">

                        {{-- Start --}}
                        <div class="start-marker mb-3">
                            <i class="bi bi-play-circle-fill fs-5"></i>
                            {{ __('Début du processus') }}
                        </div>

                        {{-- Steps list --}}
                        <ul class="step-builder" id="stepsContainer">
                            {{-- Populated by JS from BPMN XML --}}
                        </ul>

                        {{-- Add step --}}
                        <button type="button" class="add-step-btn" id="addStepBtn">
                            <i class="bi bi-plus-circle-fill fs-5"></i>
                            {{ __('Ajouter une étape') }}
                        </button>

                        {{-- End --}}
                        <div class="end-marker mt-2">
                            <i class="bi bi-check-circle-fill fs-5"></i>
                            {{ __('Fin du processus') }}
                        </div>

                        {{-- Advanced XML --}}
                        <details class="xml-advanced mt-4">
                            <summary>
                                <i class="bi bi-code-square"></i> {{ __('Afficher / modifier le XML BPMN (avancé)') }}
                            </summary>
                            <textarea class="form-control font-monospace"
                                      id="bpmn_xml_display"
                                      rows="8"
                                      style="font-size:.78rem; padding:14px;">{{ old('bpmn_xml', $definition->bpmn_xml) }}</textarea>
                            <div class="p-2 ps-3">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="applyXmlToBuilder()">
                                    <i class="bi bi-arrow-left-right"></i> {{ __('Importer ce XML dans le builder') }}
                                </button>
                            </div>
                        </details>

                    </div>
                </div>

            </div>

            {{-- ── Col right: Info & Actions ─────────────── --}}
            <div class="col-lg-4">

                {{-- Actions --}}
                <div class="section-card mb-3">
                    <div class="section-card-header">
                        <i class="bi bi-lightning-charge text-warning"></i>
                        <h5>{{ __('Actions') }}</h5>
                    </div>
                    <div class="section-card-body d-flex flex-column gap-2">
                        <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-save"></i> {{ __('Enregistrer les modifications') }}
                        </button>
                        <a href="{{ route('workflows.definitions.show', $definition) }}" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-x-circle"></i> {{ __('Annuler') }}
                        </a>
                        @if($definition->status === 'active')
                            <hr class="my-1">
                            <a href="{{ route('workflows.instances.create') }}?definition={{ $definition->id }}"
                               class="btn btn-success w-100 d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-play-fill"></i> {{ __('Démarrer une instance') }}
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Metadata --}}
                <div class="section-card">
                    <div class="section-card-header">
                        <i class="bi bi-info-circle text-secondary"></i>
                        <h5>{{ __('Informations') }}</h5>
                    </div>
                    <div class="section-card-body">
                        <dl class="row g-1 small mb-0">
                            <dt class="col-5 text-muted">{{ __('Version') }}</dt>
                            <dd class="col-7"><span class="badge" style="background:#e0f0ff;color:#0553b1;">v{{ $definition->version }}</span></dd>

                            <dt class="col-5 text-muted">{{ __('Créé par') }}</dt>
                            <dd class="col-7 fw-semibold">{{ $definition->creator->name ?? 'N/A' }}</dd>

                            <dt class="col-5 text-muted">{{ __('Créé le') }}</dt>
                            <dd class="col-7">{{ $definition->created_at->format('d/m/Y') }}</dd>

                            <dt class="col-5 text-muted">{{ __('Instances') }}</dt>
                            <dd class="col-7 fw-semibold">{{ $definition->instances->count() }}</dd>
                        </dl>
                    </div>
                </div>

                {{-- Tips --}}
                <div class="mt-3 p-3 rounded-3" style="background:#f0f7ff; border-left:3px solid #0d6efd;">
                    <p class="small fw-semibold text-primary mb-1"><i class="bi bi-lightbulb me-1"></i>{{ __('Conseils') }}</p>
                    <ul class="small mb-0" style="padding-left:16px; color:#444;">
                        <li>{{ __('Chaque étape devient une tâche lors du lancement.') }}</li>
                        <li>{{ __('Assignez un responsable pour chaque étape.') }}</li>
                        <li>{{ __('Passez le statut à "Actif" pour pouvoir démarrer.') }}</li>
                    </ul>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
const BPMN_NS = 'http://www.omg.org/spec/BPMN/20100524/MODEL';
let stepCounter = 0;

// ── Helpers ──────────────────────────────────────────────────
function sanitizeId(str) {
    return str.replace(/[^a-zA-Z0-9_]/g, '_').replace(/^(\d)/, '_$1') || 'step';
}

// ── Parse existing BPMN XML into step objects ──────────────────
function parseBpmnXml(xmlString) {
    try {
        const parser = new DOMParser();
        const doc = parser.parseFromString(xmlString, 'application/xml');
        const steps = [];
        const ns = 'http://www.omg.org/spec/BPMN/20100524/MODEL';
        const tags = ['userTask','task','serviceTask','scriptTask'];
        const seen = new Set();
        tags.forEach(tag => {
            // Try with namespace
            const els1 = doc.getElementsByTagNameNS(ns, tag);
            for(let el of els1) {
                const id = el.getAttribute('id');
                if(id && !seen.has(id)) { seen.add(id); steps.push({ id, name: el.getAttribute('name') || id }); }
            }
            // Also try prefixed (compact XML parsers)
            const els2 = doc.getElementsByTagName('bpmn:' + tag);
            for(let el of els2) {
                const id = el.getAttribute('id');
                if(id && !seen.has(id)) { seen.add(id); steps.push({ id, name: el.getAttribute('name') || id }); }
            }
        });
        return steps;
    } catch(e) { console.error('BPMN parse error:', e); return []; }
}

// ── Generate BPMN XML from steps ───────────────────────────────
function generateBpmnXml() {
    const items = document.querySelectorAll('.step-item[data-step-id]');
    let tasks = '';
    let flows = '';
    let prevId = 'start';

    items.forEach((item, i) => {
        const titleEl = item.querySelector('.step-title-input');
        const name    = titleEl ? titleEl.value.trim() : `Étape ${i+1}`;
        const id      = 'task_' + sanitizeId(name) + '_' + i;

        tasks += `<bpmn:userTask id="${id}" name="${name}"/>`;
        flows += `<bpmn:sequenceFlow id="flow_${prevId}_${id}" sourceRef="${prevId}" targetRef="${id}"/>`;
        prevId = id;
    });
    // final flow to end
    flows += `<bpmn:sequenceFlow id="flow_${prevId}_end" sourceRef="${prevId}" targetRef="end"/>`;

    return `<?xml version="1.0" encoding="UTF-8"?>
<bpmn:definitions xmlns:bpmn="${BPMN_NS}" id="Definitions_1">
  <bpmn:process id="Process_1" isExecutable="true">
    <bpmn:startEvent id="start"/>
    ${tasks}
    <bpmn:endEvent id="end"/>
    ${flows}
  </bpmn:process>
</bpmn:definitions>`;
}

// ── Render a single step item ─────────────────────────────────
function renderStep(id, name) {
    stepCounter++;
    const idx = stepCounter;
    const li = document.createElement('li');
    li.className = 'step-item';
    li.setAttribute('data-step-id', id || ('step_' + idx));
    li.setAttribute('draggable', 'true');

    li.innerHTML = `
        <div class="step-number">${idx}</div>
        <div class="step-card">
            <div class="step-card-header">
                <span class="step-drag-handle" title="{{ __('Glisser pour réordonner') }}">
                    <i class="bi bi-grip-vertical"></i>
                </span>
                <span class="step-card-title">{{ __('Étape') }} ${idx}</span>
                <button type="button" class="del-step-btn ms-auto" title="{{ __('Supprimer cette étape') }}" onclick="removeStep(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <div class="step-fields">
                <div class="field-title">
                    <label class="form-label small fw-semibold mb-1">{{ __('Titre de l\'étape') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control step-title-input"
                           placeholder="{{ __('Ex: Vérification du document') }}"
                           value="${name || ''}" required>
                </div>
            </div>
        </div>
    `;

    // Drag & drop
    setupDragEvents(li);
    return li;
}

function removeStep(btn) {
    const li = btn.closest('.step-item');
    if(document.querySelectorAll('.step-item[data-step-id]').length <= 1) {
        alert('{{ __("Un processus doit avoir au moins une étape.") }}');
        return;
    }
    li.remove();
    reNumberSteps();
}

function reNumberSteps() {
    document.querySelectorAll('.step-item[data-step-id]').forEach((li, i) => {
        const numEl = li.querySelector('.step-number');
        const titleEl = li.querySelector('.step-card-title');
        if(numEl) numEl.textContent = i + 1;
        if(titleEl) titleEl.textContent = '{{ __("Étape") }} ' + (i + 1);
    });
}

// ── Drag & Drop ───────────────────────────────────────────────
let dragSrc = null;
function setupDragEvents(li) {
    li.addEventListener('dragstart', e => {
        dragSrc = li;
        li.style.opacity = '.4';
        e.dataTransfer.effectAllowed = 'move';
    });
    li.addEventListener('dragend', () => {
        li.style.opacity = '1';
        document.querySelectorAll('.step-item').forEach(i => i.classList.remove('drag-over'));
        reNumberSteps();
    });
    li.addEventListener('dragover', e => {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        return false;
    });
    li.addEventListener('dragenter', () => li.style.background = '#f0f7ff');
    li.addEventListener('dragleave', () => li.style.background = '');
    li.addEventListener('drop', e => {
        e.stopPropagation();
        if(dragSrc !== li) {
            const container = document.getElementById('stepsContainer');
            const children  = Array.from(container.children);
            const srcIdx    = children.indexOf(dragSrc);
            const tgtIdx    = children.indexOf(li);
            if(srcIdx < tgtIdx) container.insertBefore(dragSrc, li.nextSibling);
            else                 container.insertBefore(dragSrc, li);
        }
        li.style.background = '';
        return false;
    });
}

// ── Init from existing XML ─────────────────────────────────────
function applyXmlToBuilder() {
    const xml = document.getElementById('bpmn_xml_display').value;
    const steps = parseBpmnXml(xml);
    const container = document.getElementById('stepsContainer');
    container.innerHTML = '';
    stepCounter = 0;
    if(steps.length) {
        steps.forEach(s => container.appendChild(renderStep(s.id, s.name)));
    } else {
        container.appendChild(renderStep(null, ''));
    }
}

// ── Add step button ────────────────────────────────────────────
document.getElementById('addStepBtn').addEventListener('click', () => {
    const container = document.getElementById('stepsContainer');
    container.appendChild(renderStep(null, ''));
});

// ── Form submit: generate XML before submit ─────────────────────
document.getElementById('workflowEditForm').addEventListener('submit', function(e) {
    const xml = generateBpmnXml();
    document.getElementById('bpmn_xml_hidden').value = xml;
    document.getElementById('bpmn_xml_display').value = xml;
});

// ── Bootstrap: parse existing XML ─────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    applyXmlToBuilder();
});
</script>
@endpush
