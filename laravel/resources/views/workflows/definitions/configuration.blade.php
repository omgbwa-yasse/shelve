@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1><i class="bi bi-diagram-3"></i> {{ __('Configuration BPMN') }}</h1>
            <p class="text-muted mb-0">{{ __('Workflow:') }} <strong>{{ $definition->name }}</strong></p>
        </div>
        <a href="{{ route('workflows.definitions.show', $definition) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Retour au Workflow') }}
        </a>
    </div>

    <form action="{{ isset($isEdit) && $isEdit ? route('workflows.definitions.configuration.update', $definition) : route('workflows.definitions.configuration.store', $definition) }}" method="POST">
        @csrf
        @if(isset($isEdit) && $isEdit)
            @method('PUT')
        @endif

        <div class="row">
            <!-- Palette d'éléments BPMN -->
            <div class="col-md-3">
                <div class="card bg-light sticky-top" style="top: 20px;">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-box-seam"></i> {{ __('Éléments BPMN') }}</h6>
                    </div>
                    <div class="card-body p-2">
                        <div class="accordion accordion-flush" id="bpmnPalette">
                            <!-- Événements -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#events">
                                        <i class="bi bi-circle me-2"></i> {{ __('Événements') }}
                                    </button>
                                </h2>
                                <div id="events" class="accordion-collapse collapse show" data-bs-parent="#bpmnPalette">
                                    <div class="accordion-body p-2">
                                        <div class="bpmn-element mb-2" draggable="true" data-type="startEvent">
                                            <div class="element-box text-center p-2 border rounded bg-white">
                                                <i class="bi bi-play-circle text-success" style="font-size: 2rem;"></i>
                                                <div class="small mt-1">{{ __('Début') }}</div>
                                            </div>
                                        </div>
                                        <div class="bpmn-element mb-2" draggable="true" data-type="endEvent">
                                            <div class="element-box text-center p-2 border rounded bg-white">
                                                <i class="bi bi-stop-circle text-danger" style="font-size: 2rem;"></i>
                                                <div class="small mt-1">{{ __('Fin') }}</div>
                                            </div>
                                        </div>
                                        <div class="bpmn-element mb-2" draggable="true" data-type="intermediateEvent">
                                            <div class="element-box text-center p-2 border rounded bg-white">
                                                <i class="bi bi-circle text-warning" style="font-size: 2rem;"></i>
                                                <div class="small mt-1">{{ __('Intermédiaire') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Activités -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#activities">
                                        <i class="bi bi-square me-2"></i> {{ __('Activités') }}
                                    </button>
                                </h2>
                                <div id="activities" class="accordion-collapse collapse" data-bs-parent="#bpmnPalette">
                                    <div class="accordion-body p-2">
                                        <div class="bpmn-element mb-2" draggable="true" data-type="task">
                                            <div class="element-box text-center p-2 border rounded bg-white">
                                                <i class="bi bi-check-square text-primary" style="font-size: 2rem;"></i>
                                                <div class="small mt-1">{{ __('Tâche') }}</div>
                                            </div>
                                        </div>
                                        <div class="bpmn-element mb-2" draggable="true" data-type="userTask">
                                            <div class="element-box text-center p-2 border rounded bg-white">
                                                <i class="bi bi-person-check text-info" style="font-size: 2rem;"></i>
                                                <div class="small mt-1">{{ __('Tâche Utilisateur') }}</div>
                                            </div>
                                        </div>
                                        <div class="bpmn-element mb-2" draggable="true" data-type="serviceTask">
                                            <div class="element-box text-center p-2 border rounded bg-white">
                                                <i class="bi bi-gear text-secondary" style="font-size: 2rem;"></i>
                                                <div class="small mt-1">{{ __('Tâche Service') }}</div>
                                            </div>
                                        </div>
                                        <div class="bpmn-element mb-2" draggable="true" data-type="scriptTask">
                                            <div class="element-box text-center p-2 border rounded bg-white">
                                                <i class="bi bi-code-square text-dark" style="font-size: 2rem;"></i>
                                                <div class="small mt-1">{{ __('Script') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Portes logiques -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#gateways">
                                        <i class="bi bi-diamond me-2"></i> {{ __('Portes') }}
                                    </button>
                                </h2>
                                <div id="gateways" class="accordion-collapse collapse" data-bs-parent="#bpmnPalette">
                                    <div class="accordion-body p-2">
                                        <div class="bpmn-element mb-2" draggable="true" data-type="exclusiveGateway">
                                            <div class="element-box text-center p-2 border rounded bg-white">
                                                <i class="bi bi-diamond text-warning" style="font-size: 2rem;"></i>
                                                <div class="small mt-1">{{ __('XOR') }}</div>
                                            </div>
                                        </div>
                                        <div class="bpmn-element mb-2" draggable="true" data-type="parallelGateway">
                                            <div class="element-box text-center p-2 border rounded bg-white">
                                                <i class="bi bi-plus-diamond text-success" style="font-size: 2rem;"></i>
                                                <div class="small mt-1">{{ __('AND') }}</div>
                                            </div>
                                        </div>
                                        <div class="bpmn-element mb-2" draggable="true" data-type="inclusiveGateway">
                                            <div class="element-box text-center p-2 border rounded bg-white">
                                                <i class="bi bi-circle-square text-info" style="font-size: 2rem;"></i>
                                                <div class="small mt-1">{{ __('OR') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sous-processus -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#subprocess">
                                        <i class="bi bi-boxes me-2"></i> {{ __('Sous-processus') }}
                                    </button>
                                </h2>
                                <div id="subprocess" class="accordion-collapse collapse" data-bs-parent="#bpmnPalette">
                                    <div class="accordion-body p-2">
                                        <div class="bpmn-element mb-2" draggable="true" data-type="subProcess">
                                            <div class="element-box text-center p-2 border rounded bg-white">
                                                <i class="bi bi-box text-primary" style="font-size: 2rem;"></i>
                                                <div class="small mt-1">{{ __('Sous-processus') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Connexions -->
                    <div class="card bg-info bg-opacity-10 mt-3">
                        <div class="card-header bg-info text-white py-2">
                            <h6 class="mb-0 small"><i class="bi bi-arrow-right-circle"></i> {{ __('Connexions') }}</h6>
                        </div>
                        <div class="card-body p-2">
                            <p class="small mb-2">{{ __('Pour créer une flèche :') }}</p>
                            <ol class="small mb-2 ps-3">
                                <li>{{ __('Sélectionnez un élément') }}</li>
                                <li>{{ __('Cliquez "Connecter"') }}</li>
                                <li>{{ __('Cliquez l\'élément cible') }}</li>
                            </ol>
                            <button type="button" class="btn btn-sm btn-info w-100" id="connectionMode" disabled>
                                <i class="bi bi-link-45deg"></i> {{ __('Mode Connexion') }}
                            </button>
                            <div class="small text-muted mt-2 text-center" id="connectionStatus">
                                {{ __('Sélectionnez un élément') }}
                            </div>
                        </div>
                    </div>

                    <!-- Aide -->
                    <div class="card-footer p-2">
                        <h6 class="small mb-2"><i class="bi bi-info-circle"></i> {{ __('Aide') }}</h6>
                        <ul class="small mb-0 ps-3">
                            <li>{{ __('Glissez les éléments') }}</li>
                            <li>{{ __('Cliquez pour éditer') }}</li>
                            <li>{{ __('× pour supprimer') }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Canvas BPMN et Code -->
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header bg-white">
                        <ul class="nav nav-tabs card-header-tabs" id="bpmnTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="visual-tab" data-bs-toggle="tab" data-bs-target="#visual-mode" type="button" role="tab">
                                    <i class="bi bi-diagram-3"></i> {{ __('Mode Visuel') }}
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="code-tab" data-bs-toggle="tab" data-bs-target="#code-mode" type="button" role="tab">
                                    <i class="bi bi-code-slash"></i> {{ __('Code XML') }}
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-0">
                        <div class="tab-content" id="bpmnTabContent">
                            <!-- Mode Visuel -->
                            <div class="tab-pane fade show active p-3" id="visual-mode" role="tabpanel">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0"><i class="bi bi-easel"></i> {{ __('Canvas de Workflow') }}</h6>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-secondary" id="clearCanvas">
                                            <i class="bi bi-trash"></i> {{ __('Effacer') }}
                                        </button>
                                        <button type="button" class="btn btn-outline-primary" id="generateXML">
                                            <i class="bi bi-code"></i> {{ __('Générer XML') }}
                                        </button>
                                    </div>
                                </div>

                                <div id="bpmnCanvas" class="border rounded" style="min-height: 500px; background: repeating-linear-gradient(0deg, #f8f9fa 0px, #f8f9fa 1px, transparent 1px, transparent 20px), repeating-linear-gradient(90deg, #f8f9fa 0px, #f8f9fa 1px, transparent 1px, transparent 20px); background-size: 20px 20px; position: relative; overflow: auto;">
                                    <div class="text-center text-muted p-5" id="emptyCanvasMessage">
                                        <i class="bi bi-box-arrow-in-down" style="font-size: 3rem;"></i>
                                        <p class="mt-3">{{ __('Glissez des éléments BPMN ici pour créer votre workflow') }}</p>
                                    </div>
                                </div>

                                <!-- Propriétés -->
                                <div class="card mt-3" id="propertiesPanel" style="display: none;">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="bi bi-sliders"></i> {{ __('Propriétés') }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label class="form-label small">{{ __('ID') }}</label>
                                                <input type="text" class="form-control form-control-sm" id="elementId" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small">{{ __('Type') }}</label>
                                                <input type="text" class="form-control form-control-sm" id="elementType" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small">{{ __('Nom') }}</label>
                                                <input type="text" class="form-control form-control-sm" id="elementName">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small">&nbsp;</label>
                                                <button type="button" class="btn btn-sm btn-danger w-100" id="deleteElement">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <label class="form-label small">{{ __('Description') }}</label>
                                            <textarea class="form-control form-control-sm" id="elementDescription" rows="2"></textarea>
                                        </div>
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-info" id="connectElement">
                                                <i class="bi bi-arrow-right-circle"></i> {{ __('Créer une connexion') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Statistiques -->
                                <div class="alert alert-secondary mt-3 mb-0" id="statsPanel">
                                    <small>
                                        <strong><i class="bi bi-bar-chart"></i> Statistiques:</strong><br>
                                        Éléments: <span id="elementsCount">0</span> |
                                        Connexions: <span id="connectionsCount">0</span>
                                    </small>
                                </div>
                            </div>

                            <!-- Mode Code -->
                            <div class="tab-pane fade p-3" id="code-mode" role="tabpanel">
                                <textarea class="form-control font-monospace @error('bpmn_xml') is-invalid @enderror"
                                          id="bpmn_xml"
                                          name="bpmn_xml"
                                          rows="20"
                                          required
                                          style="font-size: 0.9rem;">{{ old('bpmn_xml', $definition->bpmn_xml ?? '<?xml version="1.0" encoding="UTF-8"?>
<bpmn:definitions xmlns:bpmn="http://www.omg.org/spec/BPMN/20100524/MODEL" xmlns:bpmndi="http://www.omg.org/spec/BPMN/20100524/DI" xmlns:dc="http://www.omg.org/spec/DD/20100524/DC" id="Definitions_1">
  <bpmn:process id="Process_1" isExecutable="true">
    <!-- Vos éléments BPMN -->
  </bpmn:process>
</bpmn:definitions>') }}</textarea>
                                @error('bpmn_xml')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text mt-2">
                                    <i class="bi bi-lightbulb"></i> {{ __('Utilisez le mode visuel pour générer le XML automatiquement.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('workflows.definitions.show', $definition) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> {{ __('Annuler') }}
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-save"></i> {{ __('Enregistrer la Configuration') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .bpmn-element {
        cursor: grab;
        transition: transform 0.2s;
    }
    .bpmn-element:hover {
        transform: scale(1.05);
    }
    .bpmn-element:active {
        cursor: grabbing;
    }

    .dropped-element {
        position: absolute;
        padding: 10px;
        border: 2px solid #0d6efd;
        border-radius: 8px;
        background: white;
        cursor: move;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        transition: all 0.2s;
    }

    .dropped-element:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.25);
        border-color: #0a58ca;
    }

    .dropped-element.selected {
        border-color: #198754;
        box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.25);
    }

    #bpmnCanvas.drag-over {
        background-color: rgba(13, 110, 253, 0.05);
    }

    .connection-source {
        border-color: #0dcaf0 !important;
        box-shadow: 0 0 0 4px rgba(13, 202, 240, 0.4) !important;
        animation: pulse 1s infinite;
    }

    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 0 4px rgba(13, 202, 240, 0.4); }
        50% { box-shadow: 0 0 0 8px rgba(13, 202, 240, 0.2); }
    }

    .element-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #dc3545;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        cursor: pointer;
        z-index: 10;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/bpmn-designer.js') }}"></script>
@endpush
