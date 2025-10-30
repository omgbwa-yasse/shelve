{{--
    Éditeur de templates OPAC - Mode Édition
    Interface moderne avec prévisualisation temps réel
--}}
@extends('layouts.app')

@section('title', 'Éditer Template - ' . $template->name)

@push('styles')
<!-- Éditeur CSS et JS -->
<link href="https://cdn.jsdelivr.net/npm/codemirror@5.65.0/lib/codemirror.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/codemirror@5.65.0/theme/material-darker.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/codemirror@5.65.0/addon/hint/show-hint.css" rel="stylesheet">
<style>
/* Interface d'édition moderne */
.editor-container {
    height: 100vh;
    display: flex;
    flex-direction: column;
}

.editor-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1rem 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.editor-title {
    font-size: 1.5rem;
    font-weight: 300;
    margin: 0;
}

.editor-main {
    flex: 1;
    display: flex;
    overflow: hidden;
}

.editor-sidebar {
    width: 300px;
    background: #f8fafc;
    border-right: 1px solid #e5e7eb;
    overflow-y: auto;
    transition: width 0.3s ease;
}

.editor-sidebar.collapsed {
    width: 0;
    overflow: hidden;
}

.editor-content {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.editor-tabs {
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    padding: 0;
    margin: 0;
}

.editor-tab {
    padding: 0.75rem 1.5rem;
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
}

.editor-tab.active {
    background: white;
    border-bottom-color: #4f46e5;
    color: #4f46e5;
}

.editor-tab:hover {
    background: rgba(79, 70, 229, 0.05);
}

.editor-pane {
    flex: 1;
    display: none;
    flex-direction: column;
}

.editor-pane.active {
    display: flex;
}

.code-editor {
    flex: 1;
    border: none;
    font-family: 'Monaco', 'Menlo', monospace;
}

.preview-container {
    position: relative;
    flex: 1;
    background: white;
}

.preview-toolbar {
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
    padding: 0.5rem 1rem;
    display: flex;
    justify-content: between;
    align-items: center;
    gap: 1rem;
}

.preview-frame {
    width: 100%;
    height: calc(100% - 50px);
    border: none;
}

.sidebar-section {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.sidebar-title {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 1rem;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.form-group {
    margin-bottom: 1rem;
}

.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
}

.form-control {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    transition: border-color 0.2s ease;
}

.form-control:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.color-picker {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.color-swatch {
    width: 32px;
    height: 32px;
    border-radius: 0.375rem;
    border: 1px solid #d1d5db;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.color-input {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.component-library {
    max-height: 300px;
    overflow-y: auto;
}

.component-item {
    padding: 0.75rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    margin-bottom: 0.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.component-item:hover {
    border-color: #4f46e5;
    box-shadow: 0 2px 4px rgba(79, 70, 229, 0.1);
}

.component-name {
    font-weight: 500;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.component-description {
    font-size: 0.75rem;
    color: #6b7280;
}

.btn-group-vertical .btn {
    border-radius: 0;
    border-bottom-width: 0;
}

.btn-group-vertical .btn:first-child {
    border-top-left-radius: 0.375rem;
    border-top-right-radius: 0.375rem;
}

.btn-group-vertical .btn:last-child {
    border-bottom-left-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
    border-bottom-width: 1px;
}

.save-indicator {
    display: none;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.save-indicator.saving {
    display: flex;
    color: #f59e0b;
}

.save-indicator.saved {
    display: flex;
    color: #059669;
}

.save-indicator.error {
    display: flex;
    color: #dc2626;
}

.responsive-controls {
    display: flex;
    gap: 0.25rem;
    align-items: center;
}

.responsive-btn {
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    background: white;
    cursor: pointer;
    border-radius: 0.25rem;
    transition: all 0.2s ease;
}

.responsive-btn.active {
    background: #4f46e5;
    color: white;
    border-color: #4f46e5;
}

.split-view {
    display: flex;
    flex: 1;
}

.split-pane {
    flex: 1;
}

.split-handle {
    width: 4px;
    background: #e5e7eb;
    cursor: col-resize;
    position: relative;
}

.split-handle:hover {
    background: #4f46e5;
}

/* CodeMirror customizations */
.CodeMirror {
    height: 100%;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 14px;
}

.CodeMirror-gutters {
    background: #f8fafc;
    border-right: 1px solid #e5e7eb;
}

/* Loading states */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.spinner {
    width: 32px;
    height: 32px;
    border: 3px solid #e5e7eb;
    border-top: 3px solid #4f46e5;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 1024px) {
    .editor-sidebar {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        z-index: 200;
        box-shadow: 2px 0 8px rgba(0,0,0,0.1);
    }

    .split-view {
        flex-direction: column;
    }

    .split-handle {
        width: 100%;
        height: 4px;
        cursor: row-resize;
    }
}
</style>
@endpush

@section('content')
<div class="editor-container">
    <!-- Header -->
    <div class="editor-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="editor-title">
                        Éditer : {{ $template->name }}
                    </h1>
                    <div class="save-indicator" id="save-indicator">
                        <div class="spinner" style="width: 16px; height: 16px;"></div>
                        <span class="save-text">Enregistrement...</span>
                    </div>
                </div>

                <div class="d-flex gap-2 align-items-center">
                    <!-- Preview controls -->
                    <div class="responsive-controls">
                        <button class="responsive-btn active" data-size="desktop" title="Bureau">
                            <i class="fas fa-desktop"></i>
                        </button>
                        <button class="responsive-btn" data-size="tablet" title="Tablette">
                            <i class="fas fa-tablet-alt"></i>
                        </button>
                        <button class="responsive-btn" data-size="mobile" title="Mobile">
                            <i class="fas fa-mobile-alt"></i>
                        </button>
                    </div>

                    <button class="btn btn-outline-light" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>

                    <button class="btn btn-outline-light" onclick="saveTemplate()">
                        <i class="fas fa-save me-2"></i>
                        Enregistrer
                    </button>

                    <a href="{{ route('public.opac-templates.index') }}" class="btn btn-light">
                        <i class="fas fa-times me-2"></i>
                        Fermer
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main editor -->
    <div class="editor-main">
        <!-- Sidebar -->
        <div class="editor-sidebar" id="editor-sidebar">
            <!-- Configuration générale -->
            <div class="sidebar-section">
                <h3 class="sidebar-title">Configuration</h3>

                <div class="form-group">
                    <label class="form-label" for="template-name">Nom du template</label>
                    <input type="text" id="template-name" class="form-control"
                           value="{{ $template->name }}"
                           placeholder="Mon Template OPAC">
                    <div class="field-error" id="name-error"></div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="template-description">Description</label>
                    <textarea id="template-description" class="form-control" rows="3"
                              placeholder="Description du template...">{{ $template->description }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="template-category">Catégorie</label>
                    <select id="template-category" class="form-control">
                        <option value="general" {{ $template->category === 'general' ? 'selected' : '' }}>Général</option>
                        <option value="academic" {{ $template->category === 'academic' ? 'selected' : '' }}>Académique</option>
                        <option value="corporate" {{ $template->category === 'corporate' ? 'selected' : '' }}>Entreprise</option>
                        <option value="traditional" {{ $template->category === 'traditional' ? 'selected' : '' }}>Traditionnel</option>
                        <option value="modern" {{ $template->category === 'modern' ? 'selected' : '' }}>Moderne</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="template-status">Statut</label>
                    <select id="template-status" class="form-control">
                        <option value="draft" {{ $template->status === 'draft' ? 'selected' : '' }}>Brouillon</option>
                        <option value="active" {{ $template->status === 'active' ? 'selected' : '' }}>Actif</option>
                        <option value="inactive" {{ $template->status === 'inactive' ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>
            </div>

            <!-- Variables de thème -->
            <div class="sidebar-section">
                <h3 class="sidebar-title">Variables de Thème</h3>

                <div class="form-group">
                    <label class="form-label" for="primary-color">Couleur principale</label>
                    <div class="color-picker">
                        <div class="color-swatch" style="background-color: {{ $template->variables['primary_color'] ?? '#4f46e5' }};">
                            <input type="color" class="color-input" id="primary-color"
                                   value="{{ $template->variables['primary_color'] ?? '#4f46e5' }}" onchange="updateThemeVariable('primary_color', this.value)">
                        </div>
                        <input type="text" class="form-control" value="{{ $template->variables['primary_color'] ?? '#4f46e5' }}"
                               style="flex: 1;" onchange="updateColorSwatch('primary-color', this.value)">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="secondary-color">Couleur secondaire</label>
                    <div class="color-picker">
                        <div class="color-swatch" style="background-color: {{ $template->variables['secondary_color'] ?? '#6b7280' }};">
                            <input type="color" class="color-input" id="secondary-color"
                                   value="{{ $template->variables['secondary_color'] ?? '#6b7280' }}" onchange="updateThemeVariable('secondary_color', this.value)">
                        </div>
                        <input type="text" class="form-control" value="{{ $template->variables['secondary_color'] ?? '#6b7280' }}"
                               style="flex: 1;" onchange="updateColorSwatch('secondary-color', this.value)">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="accent-color">Couleur d'accent</label>
                    <div class="color-picker">
                        <div class="color-swatch" style="background-color: {{ $template->variables['accent_color'] ?? '#f59e0b' }};">
                            <input type="color" class="color-input" id="accent-color"
                                   value="{{ $template->variables['accent_color'] ?? '#f59e0b' }}" onchange="updateThemeVariable('accent_color', this.value)">
                        </div>
                        <input type="text" class="form-control" value="{{ $template->variables['accent_color'] ?? '#f59e0b' }}"
                               style="flex: 1;" onchange="updateColorSwatch('accent-color', this.value)">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="font-family">Police</label>
                    <select id="font-family" class="form-control" onchange="updateThemeVariable('font_family', this.value)">
                        <option value="Inter, system-ui, sans-serif" {{ ($template->variables['font_family'] ?? '') === 'Inter, system-ui, sans-serif' ? 'selected' : '' }}>Inter (moderne)</option>
                        <option value="Georgia, serif" {{ ($template->variables['font_family'] ?? '') === 'Georgia, serif' ? 'selected' : '' }}>Georgia (classique)</option>
                        <option value="'Roboto', sans-serif" {{ ($template->variables['font_family'] ?? '') === "'Roboto', sans-serif" ? 'selected' : '' }}>Roboto (clean)</option>
                        <option value="'Playfair Display', serif" {{ ($template->variables['font_family'] ?? '') === "'Playfair Display', serif" ? 'selected' : '' }}>Playfair Display (élégant)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="border-radius">Arrondi des bordures</label>
                    <select id="border-radius" class="form-control" onchange="updateThemeVariable('border_radius', this.value)">
                        <option value="0" {{ ($template->variables['border_radius'] ?? '') === '0' ? 'selected' : '' }}>Aucun (0px)</option>
                        <option value="0.25rem" {{ ($template->variables['border_radius'] ?? '') === '0.25rem' ? 'selected' : '' }}>Léger (4px)</option>
                        <option value="0.5rem" {{ ($template->variables['border_radius'] ?? '0.5rem') === '0.5rem' ? 'selected' : '' }}>Moyen (8px)</option>
                        <option value="0.75rem" {{ ($template->variables['border_radius'] ?? '') === '0.75rem' ? 'selected' : '' }}>Fort (12px)</option>
                        <option value="1rem" {{ ($template->variables['border_radius'] ?? '') === '1rem' ? 'selected' : '' }}>Très fort (16px)</option>
                    </select>
                </div>
            </div>

            <!-- Bibliothèque de composants -->
            <div class="sidebar-section">
                <h3 class="sidebar-title">Composants</h3>
                <div class="component-library">
                    <button class="component-item" onclick="insertComponent('search-bar')" tabindex="0" onkeydown="if(event.key==='Enter' || event.key===' ') { insertComponent('search-bar'); }">
                        <div class="component-name">Barre de recherche</div>
                        <div class="component-description">Barre de recherche avec filtres</div>
                    </button>

                    <button class="component-item" onclick="insertComponent('document-card')" tabindex="0" onkeydown="if(event.key==='Enter' || event.key===' ') { insertComponent('document-card'); }">
                        <div class="component-name">Carte document</div>
                        <div class="component-description">Affichage d'un document</div>
                    </button>

                    <button class="component-item" onclick="insertComponent('navigation')" tabindex="0" onkeydown="if(event.key==='Enter' || event.key===' ') { insertComponent('navigation'); }">
                        <div class="component-name">Navigation</div>
                        <div class="component-description">Menu de navigation principal</div>
                    </button>

                    <button class="component-item" onclick="insertComponent('pagination')" tabindex="0" onkeydown="if(event.key==='Enter' || event.key===' ') { insertComponent('pagination'); }">
                        <div class="component-name">Pagination</div>
                        <div class="component-description">Navigation entre pages</div>
                    </button>

                    <button class="component-item" onclick="insertComponent('filters')" tabindex="0" onkeydown="if(event.key==='Enter' || event.key===' ') { insertComponent('filters'); }">
                        <div class="component-name">Filtres</div>
                        <div class="component-description">Filtres de recherche</div>
                    </button>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="sidebar-section">
                <h3 class="sidebar-title">Actions</h3>
                <div class="btn-group-vertical w-100">
                    <button class="btn btn-outline-primary btn-sm" onclick="loadTemplate('modern-academic')">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Charger Modern Academic
                    </button>
                    <button class="btn btn-outline-primary btn-sm" onclick="loadTemplate('classic-library')">
                        <i class="fas fa-book me-2"></i>
                        Charger Classic Library
                    </button>
                    <button class="btn btn-outline-primary btn-sm" onclick="loadTemplate('corporate-clean')">
                        <i class="fas fa-building me-2"></i>
                        Charger Corporate Clean
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="resetTemplate()">
                        <i class="fas fa-undo me-2"></i>
                        Reset
                    </button>
                    <button class="btn btn-outline-success btn-sm" onclick="exportTemplate()">
                        <i class="fas fa-download me-2"></i>
                        Exporter JSON
                    </button>
                </div>
            </div>
        </div>

        <!-- Contenu principal -->
        <div class="editor-content">
            <!-- Onglets -->
            <div class="editor-tabs">
                <button class="editor-tab active" data-tab="visual">
                    <i class="fas fa-eye"></i>
                    Visuel
                </button>
                <button class="editor-tab" data-tab="html">
                    <i class="fas fa-code"></i>
                    HTML
                </button>
                <button class="editor-tab" data-tab="css">
                    <i class="fas fa-paint-brush"></i>
                    CSS
                </button>
                <button class="editor-tab" data-tab="js">
                    <i class="fab fa-js-square"></i>
                    JavaScript
                </button>
                <button class="editor-tab" data-tab="preview">
                    <i class="fas fa-external-link-alt"></i>
                    Aperçu
                </button>
            </div>

            <!-- Panneaux d'édition -->

            <!-- Éditeur visuel -->
            <div class="editor-pane active" id="visual-pane">
                <div class="split-view">
                    <div class="split-pane">
                        <textarea id="html-editor" class="code-editor">{!! htmlspecialchars($template->layout ?? '<div class="my-template-layout">
    <div class="container">
        <h1>Mon Template OPAC</h1>
        <p>Commencez à éditer votre template...</p>
    </div>
</div>', ENT_QUOTES, 'UTF-8') !!}</textarea>
                    </div>

                    <div class="split-handle" id="split-handle"></div>

                    <div class="split-pane">
                        <div class="preview-container">
                            <div class="preview-toolbar">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="small text-muted">Aperçu temps réel</span>
                                    <button class="btn btn-sm btn-outline-primary" onclick="refreshPreview()">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="small text-muted" id="preview-size">1200×800</span>
                                </div>
                            </div>
                            <iframe class="preview-frame" id="live-preview" title="Aperçu du template"></iframe>
                            <div class="loading-overlay" id="preview-loading" style="display: none;">
                                <div class="spinner"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Éditeur HTML -->
            <div class="editor-pane" id="html-pane">
                <textarea id="html-code-editor" class="code-editor">{!! $template->layout ?? '' !!}</textarea>
            </div>

            <!-- Éditeur CSS -->
            <div class="editor-pane" id="css-pane">
                <textarea id="css-editor" class="code-editor">{!! $template->css ?? '/* CSS personnalisé pour votre template */
.my-template-layout {
    font-family: var(--font-family);
}

.custom-header {
    background: var(--primary-color);
    color: white;
    padding: 2rem 0;
    margin-bottom: 2rem;
}

.custom-card {
    border-radius: var(--border-radius);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.custom-card:hover {
    transform: translateY(-2px);
}' !!}</textarea>
            </div>

            <!-- Éditeur JavaScript -->
            <div class="editor-pane" id="js-pane">
                <textarea id="js-editor" class="code-editor">{!! $template->js ?? '// JavaScript personnalisé pour votre template
document.addEventListener(\'DOMContentLoaded\', function() {
    console.log(\'Template OPAC chargé\');

    // Ajoutez votre code JavaScript ici

    // Exemple : Animation des cartes
    const cards = document.querySelectorAll(\'.custom-card\');
    cards.forEach(card => {
        card.addEventListener(\'mouseenter\', function() {
            this.style.transform = \'translateY(-4px)\';
        });

        card.addEventListener(\'mouseleave\', function() {
            this.style.transform = \'translateY(0)\';
        });
    });
});' !!}</textarea>
            </div>

            <!-- Aperçu plein écran -->
            <div class="editor-pane" id="preview-pane">
                <iframe class="preview-frame" id="full-preview" title="Aperçu complet du template"
                        style="height: 100%;"></iframe>
            </div>
        </div>
    </div>
</div>

<!-- Form caché pour la sauvegarde -->
<form id="template-form" method="POST" action="{{ route('public.opac-templates.update', $template) }}" style="display: none;">
    @csrf
    @method('PUT')

    <input type="hidden" id="form-name" name="name">
    <input type="hidden" id="form-description" name="description">
    <input type="hidden" id="form-category" name="category">
    <input type="hidden" id="form-status" name="status">
    <input type="hidden" id="form-layout" name="layout">
    <input type="hidden" id="form-variables" name="variables">
    <input type="hidden" id="form-css" name="css">
    <input type="hidden" id="form-js" name="js">
</form>
@endsection

@push('scripts')
<!-- CodeMirror et extensions -->
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.0/lib/codemirror.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.0/mode/xml/xml.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.0/mode/css/css.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.0/mode/javascript/javascript.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.0/mode/htmlmixed/htmlmixed.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.0/addon/hint/show-hint.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.0/addon/hint/html-hint.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.0/addon/hint/css-hint.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.0/addon/edit/closetag.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.0/addon/edit/matchbrackets.js"></script>

<script>
// Variables globales
let htmlEditor, cssEditor, jsEditor, htmlCodeEditor;
let isAutoSaving = false;
let autoSaveInterval;
let templateData = {
    id: {{ $template->id }},
    name: '{{ $template->name }}',
    description: '{{ addslashes($template->description ?? '') }}',
    category: '{{ $template->category ?? 'general' }}',
    status: '{{ $template->status ?? 'draft' }}',
    layout: `{!! addslashes($template->layout ?? '') !!}`,
    css: `{!! addslashes($template->css ?? '') !!}`,
    js: `{!! addslashes($template->js ?? '') !!}`,
    variables: {!! json_encode($template->variables ?? []) !!}
};

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initialisation de l\'éditeur de template...');

    // Initialiser CodeMirror pour chaque éditeur
    initializeEditors();

    // Gérer les onglets
    initializeTabs();

    // Gérer la responsivité de la prévisualisation
    initializeResponsiveControls();

    // Initialiser la vue séparée (split view)
    initializeSplitView();

    // Auto-sauvegarde toutes les 30 secondes
    startAutoSave();

    // Raccourcis clavier
    initializeKeyboardShortcuts();

    // Synchronisation des éditeurs
    syncEditorContent();

    // Première prévisualisation
    setTimeout(updatePreview, 1000);

    console.log('Éditeur initialisé avec succès');
});

function initializeEditors() {
    // Configuration commune pour tous les éditeurs
    const commonOptions = {
        lineNumbers: true,
        matchBrackets: true,
        autoCloseTags: true,
        extraKeys: {
            "Ctrl-Space": "autocomplete",
            "Ctrl-S": function() { saveTemplate(); },
            "F11": function(cm) {
                cm.setOption("fullScreen", !cm.getOption("fullScreen"));
            },
            "Esc": function(cm) {
                if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
            }
        },
        theme: 'material-darker'
    };

    // Éditeur HTML principal (vue visuelle)
    htmlEditor = CodeMirror.fromTextArea(document.getElementById('html-editor'), {
        ...commonOptions,
        mode: 'htmlmixed',
        hintOptions: {
            completeSingle: false
        }
    });

    // Éditeur HTML (onglet HTML)
    htmlCodeEditor = CodeMirror.fromTextArea(document.getElementById('html-code-editor'), {
        ...commonOptions,
        mode: 'htmlmixed'
    });

    // Éditeur CSS
    cssEditor = CodeMirror.fromTextArea(document.getElementById('css-editor'), {
        ...commonOptions,
        mode: 'css'
    });

    // Éditeur JavaScript
    jsEditor = CodeMirror.fromTextArea(document.getElementById('js-editor'), {
        ...commonOptions,
        mode: 'javascript'
    });

    // Synchroniser les éditeurs HTML
    htmlEditor.on('change', function() {
        htmlCodeEditor.setValue(htmlEditor.getValue());
        debouncePreviewUpdate();
    });

    htmlCodeEditor.on('change', function() {
        htmlEditor.setValue(htmlCodeEditor.getValue());
        debouncePreviewUpdate();
    });

    // Écouter les changements pour la prévisualisation
    cssEditor.on('change', debouncePreviewUpdate);
    jsEditor.on('change', debouncePreviewUpdate);

    console.log('Éditeurs CodeMirror initialisés');
}

function initializeTabs() {
    const tabs = document.querySelectorAll('.editor-tab');
    const panes = document.querySelectorAll('.editor-pane');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.dataset.tab;

            // Désactiver tous les onglets et panneaux
            tabs.forEach(t => t.classList.remove('active'));
            panes.forEach(p => p.classList.remove('active'));

            // Activer l'onglet et le panneau cible
            this.classList.add('active');
            document.getElementById(targetTab + '-pane').classList.add('active');

            // Rafraîchir l'éditeur si nécessaire
            setTimeout(() => {
                switch(targetTab) {
                    case 'html':
                        htmlCodeEditor.refresh();
                        break;
                    case 'css':
                        cssEditor.refresh();
                        break;
                    case 'js':
                        jsEditor.refresh();
                        break;
                    case 'visual':
                        htmlEditor.refresh();
                        break;
                    case 'preview':
                        updateFullPreview();
                        break;
                }
            }, 100);
        });
    });
}

function initializeResponsiveControls() {
    const responsiveButtons = document.querySelectorAll('.responsive-btn');
    const preview = document.getElementById('live-preview');

    responsiveButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            responsiveButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const size = this.dataset.size;
            const sizeLabel = document.getElementById('preview-size');

            switch(size) {
                case 'mobile':
                    preview.style.width = '375px';
                    preview.style.height = '667px';
                    sizeLabel.textContent = '375×667 (Mobile)';
                    break;
                case 'tablet':
                    preview.style.width = '768px';
                    preview.style.height = '1024px';
                    sizeLabel.textContent = '768×1024 (Tablette)';
                    break;
                case 'desktop':
                default:
                    preview.style.width = '100%';
                    preview.style.height = '100%';
                    sizeLabel.textContent = 'Bureau (100%)';
                    break;
            }
        });
    });
}

function initializeSplitView() {
    const splitHandle = document.getElementById('split-handle');
    const splitView = splitHandle.parentElement;
    let isResizing = false;

    splitHandle.addEventListener('mousedown', function(e) {
        isResizing = true;
        document.addEventListener('mousemove', handleResize);
        document.addEventListener('mouseup', stopResize);
        e.preventDefault();
    });

    function handleResize(e) {
        if (!isResizing) return;

        const rect = splitView.getBoundingClientRect();
        const percentage = ((e.clientX - rect.left) / rect.width) * 100;

        if (percentage > 20 && percentage < 80) {
            const leftPane = splitView.children[0];
            const rightPane = splitView.children[2];

            leftPane.style.flex = `0 0 ${percentage}%`;
            rightPane.style.flex = `0 0 ${100 - percentage}%`;
        }
    }

    function stopResize() {
        isResizing = false;
        document.removeEventListener('mousemove', handleResize);
        document.removeEventListener('mouseup', stopResize);

        // Rafraîchir l'éditeur après redimensionnement
        setTimeout(() => htmlEditor.refresh(), 100);
    }
}

function initializeKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl+S pour sauvegarder
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            saveTemplate();
        }

        // Ctrl+Shift+P pour aperçu
        if (e.ctrlKey && e.shiftKey && e.key === 'P') {
            e.preventDefault();
            document.querySelector('[data-tab="preview"]').click();
        }

        // Échap pour fermer les modales
        if (e.key === 'Escape') {
            // Logique pour fermer les modales si nécessaire
        }
    });
}

// Prévisualisation avec débounce
let previewTimeout;
function debouncePreviewUpdate() {
    clearTimeout(previewTimeout);
    previewTimeout = setTimeout(updatePreview, 500);
}

function updatePreview() {
    const preview = document.getElementById('live-preview');
    const loading = document.getElementById('preview-loading');

    if (loading) loading.style.display = 'flex';

    // Construire le HTML complet avec les variables
    const htmlContent = buildPreviewHTML();

    // Créer un blob URL pour l'iframe
    const blob = new Blob([htmlContent], {type: 'text/html'});
    const url = URL.createObjectURL(blob);

    preview.onload = function() {
        if (loading) loading.style.display = 'none';
    };

    preview.src = url;

    // Nettoyer l'ancienne URL
    setTimeout(() => URL.revokeObjectURL(url), 1000);
}

function updateFullPreview() {
    const fullPreview = document.getElementById('full-preview');
    const htmlContent = buildPreviewHTML();

    const blob = new Blob([htmlContent], {type: 'text/html'});
    const url = URL.createObjectURL(blob);

    fullPreview.onload = function() {
        setTimeout(() => URL.revokeObjectURL(url), 1000);
    };

    fullPreview.src = url;
}

function buildPreviewHTML() {
    const variables = getCurrentVariables();
    const htmlCode = htmlEditor.getValue() || htmlCodeEditor.getValue();
    const cssCode = cssEditor.getValue();
    const jsCode = jsEditor.getValue();

    // Template CSS avec variables
    const cssWithVariables = `
        :root {
            --primary-color: ${variables.primary_color};
            --secondary-color: ${variables.secondary_color};
            --accent-color: ${variables.accent_color};
            --font-family: ${variables.font_family};
            --border-radius: ${variables.border_radius};
        }

        body {
            font-family: var(--font-family);
            margin: 0;
            padding: 20px;
            background: #f8f9fa;
        }

        ${cssCode}
    `;

    return `
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Aperçu Template</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
            <style>${cssWithVariables}</style>
        </head>
        <body>
            ${htmlCode}
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"><\/script>
            <script>${jsCode}<\/script>
        </body>
        </html>
    `;
}

function getCurrentVariables() {
    return {
        primary_color: document.getElementById('primary-color').value || '#4f46e5',
        secondary_color: document.getElementById('secondary-color').value || '#6b7280',
        accent_color: document.getElementById('accent-color').value || '#f59e0b',
        font_family: document.getElementById('font-family').value || 'Inter, system-ui, sans-serif',
        border_radius: document.getElementById('border-radius').value || '0.5rem'
    };
}

// Gestion des thèmes et variables
function updateThemeVariable(variable, value) {
    templateData.variables[variable] = value;

    // Mettre à jour la couleur de l'aperçu du swatch si c'est une couleur
    if (variable.includes('color')) {
        const colorId = variable.replace('_', '-');
        const swatch = document.querySelector(`#${colorId}`).closest('.color-picker').querySelector('.color-swatch');
        if (swatch) {
            swatch.style.backgroundColor = value;
        }
    }

    debouncePreviewUpdate();
    markAsModified();
}

function updateColorSwatch(inputId, value) {
    const colorInput = document.getElementById(inputId);
    const swatch = colorInput.closest('.color-picker').querySelector('.color-swatch');

    if (/^#[0-9A-F]{6}$/i.test(value)) {
        colorInput.value = value;
        swatch.style.backgroundColor = value;

        const variable = inputId.replace('-', '_');
        updateThemeVariable(variable, value);
    }
}

// Insertion de composants
function insertComponent(componentType) {
    let componentHtml = '';

    switch(componentType) {
        case 'search-bar':
            componentHtml = `
    @include('opac.components.search-bar', [
        'showFilters' => true,
        'placeholder' => 'Rechercher dans le catalogue...'
    ])`;
            break;

        case 'document-card':
            componentHtml = `
    @include('opac.components.document-card', [
        'document' => (object)[
            'title' => 'Titre du document',
            'description' => 'Description...',
            'author' => 'Nom de l\'auteur'
        ]
    ])`;
            break;

        case 'navigation':
            componentHtml = `
    @include('opac.components.navigation', [
        'items' => [
            ['label' => 'Accueil', 'url' => '/'],
            ['label' => 'Catalogue', 'url' => '/catalogue'],
            ['label' => 'À propos', 'url' => '/about']
        ]
    ])`;
            break;

        case 'pagination':
            componentHtml = `
    @include('opac.components.pagination', [
        'currentPage' => 1,
        'totalPages' => 10,
        'baseUrl' => '/search'
    ])`;
            break;

        case 'filters':
            componentHtml = `
    @include('opac.components.filters', [
        'filters' => [
            'type' => ['Livre', 'Article', 'Thèse'],
            'year' => range(2000, date('Y'))
        ]
    ])`;
            break;
    }

    // Insérer à la position du curseur dans l'éditeur HTML actif
    const activeEditor = document.querySelector('.editor-tab.active').dataset.tab === 'visual' ? htmlEditor : htmlCodeEditor;
    const cursor = activeEditor.getCursor();
    activeEditor.replaceRange(componentHtml, cursor);

    markAsModified();
}

// Chargement de templates prédéfinis
function loadTemplate(templateType) {
    if (!confirm('Charger ce template écrasera le contenu actuel. Continuer ?')) {
        return;
    }

    // Simuler le chargement (dans une vraie application, cela viendrait du serveur)
    const templates = {
        'modern-academic': {
            layout: `<div class="modern-academic-layout">
    <header class="academic-header">
        <div class="container">
            <h1>Bibliothèque Académique</h1>
            <nav>@include('opac.components.navigation')</nav>
        </div>
    </header>
    <main class="container mt-4">
        @include('opac.components.search-bar')
        <div class="results-grid mt-4">
            @include('opac.components.document-card')
        </div>
    </main>
</div>`,
            css: `.modern-academic-layout {
    font-family: var(--font-family);
}

.academic-header {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    padding: 2rem 0;
}`,
            js: `console.log('Template Modern Academic chargé');`
        },
        'classic-library': {
            layout: `<div class="classic-library-layout">
    <div class="classic-header">
        <h1>Bibliothèque Classique</h1>
    </div>
    <div class="container">
        @include('opac.components.search-bar')
        @include('opac.components.document-card')
    </div>
</div>`,
            css: `.classic-library-layout {
    background: #f5f5dc;
    font-family: Georgia, serif;
}`,
            js: `// Code pour template classique`
        },
        'corporate-clean': {
            layout: `<div class="corporate-layout">
    <header class="corporate-header">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="logo">Logo Entreprise</div>
            @include('opac.components.navigation')
        </div>
    </header>
    <main class="container mt-4">
        @include('opac.components.search-bar')
        <div class="row mt-4">
            <div class="col-md-8">
                @include('opac.components.document-card')
            </div>
            <div class="col-md-4">
                @include('opac.components.filters')
            </div>
        </div>
    </main>
</div>`,
            css: `.corporate-layout {
    font-family: 'Roboto', sans-serif;
}

.corporate-header {
    background: var(--primary-color);
    color: white;
    padding: 1rem 0;
}`,
            js: `// Code pour template corporate`
        }
    };

    const template = templates[templateType];
    if (template) {
        htmlEditor.setValue(template.layout);
        htmlCodeEditor.setValue(template.layout);
        cssEditor.setValue(template.css);
        jsEditor.setValue(template.js);

        debouncePreviewUpdate();
        markAsModified();
    }
}

// Auto-sauvegarde
function startAutoSave() {
    autoSaveInterval = setInterval(() => {
        if (hasUnsavedChanges()) {
            autoSave();
        }
    }, 30000); // Toutes les 30 secondes
}

function autoSave() {
    if (isAutoSaving) return;

    isAutoSaving = true;
    showSaveIndicator('saving');

    const formData = collectFormData();

    fetch(`{{ route('public.opac-templates.update', $template) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-HTTP-Method-Override': 'PUT'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSaveIndicator('saved');
        } else {
            showSaveIndicator('error');
        }
    })
    .catch(error => {
        console.error('Erreur auto-sauvegarde:', error);
        showSaveIndicator('error');
    })
    .finally(() => {
        isAutoSaving = false;
        setTimeout(() => hideSaveIndicator(), 2000);
    });
}

// Sauvegarde manuelle
function saveTemplate() {
    const formData = collectFormData();

    // Remplir le formulaire caché
    Object.keys(formData).forEach(key => {
        const input = document.getElementById(`form-${key}`);
        if (input) {
            if (key === 'variables') {
                input.value = JSON.stringify(formData[key]);
            } else {
                input.value = formData[key];
            }
        }
    });

    // Soumettre le formulaire
    document.getElementById('template-form').submit();
}

function collectFormData() {
    return {
        name: document.getElementById('template-name').value,
        description: document.getElementById('template-description').value,
        category: document.getElementById('template-category').value,
        status: document.getElementById('template-status').value,
        layout: htmlEditor.getValue() || htmlCodeEditor.getValue(),
        css: cssEditor.getValue(),
        js: jsEditor.getValue(),
        variables: getCurrentVariables()
    };
}

// Interface utilisateur
function showSaveIndicator(status) {
    const indicator = document.getElementById('save-indicator');
    const text = indicator.querySelector('.save-text');

    indicator.className = `save-indicator ${status}`;

    switch(status) {
        case 'saving':
            text.textContent = 'Enregistrement...';
            break;
        case 'saved':
            text.textContent = 'Enregistré';
            break;
        case 'error':
            text.textContent = 'Erreur sauvegarde';
            break;
    }
}

function hideSaveIndicator() {
    document.getElementById('save-indicator').style.display = 'none';
}

function markAsModified() {
    // Logique pour marquer le template comme modifié
    templateData.modified = true;
}

function hasUnsavedChanges() {
    const currentData = collectFormData();
    return JSON.stringify(currentData) !== JSON.stringify(templateData);
}

function syncEditorContent() {
    // Synchroniser le contenu initial
    if (templateData.layout) {
        htmlEditor.setValue(templateData.layout);
        htmlCodeEditor.setValue(templateData.layout);
    }
    if (templateData.css) {
        cssEditor.setValue(templateData.css);
    }
    if (templateData.js) {
        jsEditor.setValue(templateData.js);
    }
}

// Fonctions utilitaires
function toggleSidebar() {
    const sidebar = document.getElementById('editor-sidebar');
    sidebar.classList.toggle('collapsed');
}

function refreshPreview() {
    updatePreview();
}

function resetTemplate() {
    if (!confirm('Réinitialiser le template ? Toutes les modifications seront perdues.')) {
        return;
    }

    htmlEditor.setValue('');
    htmlCodeEditor.setValue('');
    cssEditor.setValue('');
    jsEditor.setValue('');

    // Reset des variables
    document.getElementById('primary-color').value = '#4f46e5';
    document.getElementById('secondary-color').value = '#6b7280';
    document.getElementById('accent-color').value = '#f59e0b';

    debouncePreviewUpdate();
}

function exportTemplate() {
    const templateJson = {
        name: document.getElementById('template-name').value,
        description: document.getElementById('template-description').value,
        category: document.getElementById('template-category').value,
        layout: htmlEditor.getValue(),
        css: cssEditor.getValue(),
        js: jsEditor.getValue(),
        variables: getCurrentVariables()
    };

    const blob = new Blob([JSON.stringify(templateJson, null, 2)], {type: 'application/json'});
    const url = URL.createObjectURL(blob);

    const a = document.createElement('a');
    a.href = url;
    a.download = `template-${templateJson.name.toLowerCase().replace(/\s+/g, '-')}.json`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);

    URL.revokeObjectURL(url);
}

// Nettoyage au déchargement de la page
window.addEventListener('beforeunload', function(e) {
    if (hasUnsavedChanges()) {
        e.preventDefault();
        e.returnValue = 'Vous avez des modifications non sauvegardées. Voulez-vous vraiment quitter ?';
    }

    if (autoSaveInterval) {
        clearInterval(autoSaveInterval);
    }
});
</script>
@endpush
