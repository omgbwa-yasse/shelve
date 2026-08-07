{{--
    Éditeur de templates OPAC
    Interface moderne avec prévisualisation temps réel
--}}
@extends('layouts.app')

@section('title', isset($template) ? 'Éditer Template - ' . $template->name : 'Nouveau Template OPAC')

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

.editor-toolbar {
    background: white;
    border-bottom: 1px solid #e5e7eb;
    padding: 0.75rem 0;
    position: sticky;
    top: 0;
    z-index: 100;
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

/* Animation et transitions */
.fade-in {
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
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

/* Validation d'erreurs */
.field-error {
    color: #dc2626;
    font-size: 0.75rem;
    margin-top: 0.25rem;
}

.form-control.error {
    border-color: #dc2626;
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
                        {{ isset($template) ? 'Éditer : ' . $template->name : 'Nouveau Template OPAC' }}
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
                           value="{{ $template->name ?? '' }}"
                           placeholder="Mon Template OPAC">
                    <div class="field-error" id="name-error"></div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="template-description">Description</label>
                    <textarea id="template-description" class="form-control" rows="3"
                              placeholder="Description du template...">{{ $template->description ?? '' }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="template-category">Catégorie</label>
                    <select id="template-category" class="form-control">
                        <option value="general">Général</option>
                        <option value="academic">Académique</option>
                        <option value="corporate">Entreprise</option>
                        <option value="traditional">Traditionnel</option>
                        <option value="modern">Moderne</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="template-status">Statut</label>
                    <select id="template-status" class="form-control">
                        <option value="draft">Brouillon</option>
                        <option value="active">Actif</option>
                        <option value="inactive">Inactif</option>
                    </select>
                </div>
            </div>

            <!-- Variables de thème -->
            <div class="sidebar-section">
                <h3 class="sidebar-title">Variables de Thème</h3>

                <div class="form-group">
                    <label class="form-label">Couleur principale</label>
                    <div class="color-picker">
                        <div class="color-swatch" style="background-color: #4f46e5;">
                            <input type="color" class="color-input" id="primary-color"
                                   value="#4f46e5" onchange="updateThemeVariable('primary_color', this.value)">
                        </div>
                        <input type="text" class="form-control" value="#4f46e5"
                               style="flex: 1;" onchange="updateColorSwatch('primary-color', this.value)">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Couleur secondaire</label>
                    <div class="color-picker">
                        <div class="color-swatch" style="background-color: #6b7280;">
                            <input type="color" class="color-input" id="secondary-color"
                                   value="#6b7280" onchange="updateThemeVariable('secondary_color', this.value)">
                        </div>
                        <input type="text" class="form-control" value="#6b7280"
                               style="flex: 1;" onchange="updateColorSwatch('secondary-color', this.value)">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Couleur d'accent</label>
                    <div class="color-picker">
                        <div class="color-swatch" style="background-color: #f59e0b;">
                            <input type="color" class="color-input" id="accent-color"
                                   value="#f59e0b" onchange="updateThemeVariable('accent_color', this.value)">
                        </div>
                        <input type="text" class="form-control" value="#f59e0b"
                               style="flex: 1;" onchange="updateColorSwatch('accent-color', this.value)">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="font-family">Police</label>
                    <select id="font-family" class="form-control" onchange="updateThemeVariable('font_family', this.value)">
                        <option value="Inter, system-ui, sans-serif">Inter (moderne)</option>
                        <option value="Georgia, serif">Georgia (classique)</option>
                        <option value="'Roboto', sans-serif">Roboto (clean)</option>
                        <option value="'Playfair Display', serif">Playfair Display (élégant)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="border-radius">Arrondi des bordures</label>
                    <select id="border-radius" class="form-control" onchange="updateThemeVariable('border_radius', this.value)">
                        <option value="0">Aucun (0px)</option>
                        <option value="0.25rem">Léger (4px)</option>
                        <option value="0.5rem" selected>Moyen (8px)</option>
                        <option value="0.75rem">Fort (12px)</option>
                        <option value="1rem">Très fort (16px)</option>
                    </select>
                </div>
            </div>

            <!-- Bibliothèque de composants -->
            <div class="sidebar-section">
                <h3 class="sidebar-title">Composants</h3>
                <div class="component-library">
                    <div class="component-item" onclick="insertComponent('search-bar')">
                        <div class="component-name">Barre de recherche</div>
                        <div class="component-description">Barre de recherche avec filtres</div>
                    </div>

                    <div class="component-item" onclick="insertComponent('document-card')">
                        <div class="component-name">Carte document</div>
                        <div class="component-description">Affichage d'un document</div>
                    </div>

                    <div class="component-item" onclick="insertComponent('navigation')">
                        <div class="component-name">Navigation</div>
                        <div class="component-description">Menu de navigation principal</div>
                    </div>

                    <div class="component-item" onclick="insertComponent('pagination')">
                        <div class="component-name">Pagination</div>
                        <div class="component-description">Navigation entre pages</div>
                    </div>

                    <div class="component-item" onclick="insertComponent('filters')">
                        <div class="component-name">Filtres</div>
                        <div class="component-description">Filtres de recherche</div>
                    </div>
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
                        <textarea id="html-editor" class="code-editor">
@extends('opac.layouts.adaptive')

@section('title', 'Mon Template OPAC')

@section('content')
<div class="my-template-layout">
    <!-- Insérez vos composants ici -->

    @include('opac.components.search-bar', [
        'showFilters' => true,
        'placeholder' => 'Rechercher...'
    ])

    <div class="container mt-4">
        <div class="row">
            @for($i = 1; $i <= 6; $i++)
                <div class="col-md-4 mb-3">
                    @include('opac.components.document-card', [
                        'document' => (object)[
                            'id' => $i,
                            'title' => 'Document exemple ' . $i,
                            'description' => 'Description du document...',
                            'author' => 'Auteur ' . $i
                        ],
                        'showMetadata' => true
                    ])
                </div>
            @endfor
        </div>
    </div>
</div>
@endsection
                        </textarea>
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
                <textarea id="html-code-editor" class="code-editor"></textarea>
            </div>

            <!-- Éditeur CSS -->
            <div class="editor-pane" id="css-pane">
                <textarea id="css-editor" class="code-editor">
/* CSS personnalisé pour votre template */
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
}
                </textarea>
            </div>

            <!-- Éditeur JavaScript -->
            <div class="editor-pane" id="js-pane">
                <textarea id="js-editor" class="code-editor">
// JavaScript personnalisé pour votre template
document.addEventListener('DOMContentLoaded', function() {
    console.log('Template OPAC chargé');

    // Ajoutez votre code JavaScript ici

    // Exemple : Animation des cartes
    const cards = document.querySelectorAll('.custom-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
                </textarea>
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
<form id="template-form" method="POST" action="{{ isset($template) ? route('public.opac-templates.update', $template) : route('public.opac-templates.store') }}" style="display: none;">
    @csrf
    @if(isset($template))
        @method('PUT')
    @endif

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
<!-- CodeMirror -->
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.0/lib/codemirror.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.0/mode/xml/xml.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.0/mode/css/css.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.0/mode/javascript/javascript.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.0/mode/php/php.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.0/addon/hint/show-hint.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.0/addon/hint/html-hint.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.0/addon/hint/css-hint.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    let htmlEditor, cssEditor, jsEditor;
    let currentTemplate = @json($template ?? null);
    let themeVariables = @json($template->variables ?? []);
    let autoSaveTimeout;
    let previewUpdateTimeout;

    // Initialisation
    initializeEditors();
    initializeTabs();
    initializePreview();
    loadTemplateData();

    // Configuration des éditeurs CodeMirror
    function initializeEditors() {
        // Éditeur HTML
        htmlEditor = CodeMirror.fromTextArea(document.getElementById('html-code-editor'), {
            mode: 'application/x-httpd-php',
            theme: 'material-darker',
            lineNumbers: true,
            autoCloseTags: true,
            extraKeys: {"Ctrl-Space": "autocomplete"},
            hintOptions: {hint: CodeMirror.hint.html}
        });

        // Éditeur CSS
        cssEditor = CodeMirror.fromTextArea(document.getElementById('css-editor'), {
            mode: 'css',
            theme: 'material-darker',
            lineNumbers: true,
            autoCloseBrackets: true,
            extraKeys: {"Ctrl-Space": "autocomplete"}
        });

        // Éditeur JavaScript
        jsEditor = CodeMirror.fromTextArea(document.getElementById('js-editor'), {
            mode: 'javascript',
            theme: 'material-darker',
            lineNumbers: true,
            autoCloseBrackets: true,
            extraKeys: {"Ctrl-Space": "autocomplete"}
        });

        // Auto-sauvegarde et mise à jour preview
        [htmlEditor, cssEditor, jsEditor].forEach(editor => {
            editor.on('change', function() {
                clearTimeout(autoSaveTimeout);
                autoSaveTimeout = setTimeout(autoSave, 2000);

                clearTimeout(previewUpdateTimeout);
                previewUpdateTimeout = setTimeout(updatePreview, 1000);
            });
        });
    }

    // Gestion des onglets
    function initializeTabs() {
        document.querySelectorAll('.editor-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const targetTab = this.dataset.tab;

                // Mise à jour des onglets actifs
                document.querySelectorAll('.editor-tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.editor-pane').forEach(p => p.classList.remove('active'));

                this.classList.add('active');
                document.getElementById(targetTab + '-pane').classList.add('active');

                // Refresh des éditeurs si nécessaire
                setTimeout(() => {
                    if (targetTab === 'html') htmlEditor.refresh();
                    if (targetTab === 'css') cssEditor.refresh();
                    if (targetTab === 'js') jsEditor.refresh();
                }, 100);
            });
        });
    }

    // Initialisation de l'aperçu
    function initializePreview() {
        updatePreview();

        // Gestion responsive
        document.querySelectorAll('.responsive-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.responsive-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const size = this.dataset.size;
                const preview = document.getElementById('live-preview');

                switch(size) {
                    case 'desktop':
                        preview.style.width = '100%';
                        document.getElementById('preview-size').textContent = '1200×800';
                        break;
                    case 'tablet':
                        preview.style.width = '768px';
                        preview.style.margin = '0 auto';
                        document.getElementById('preview-size').textContent = '768×1024';
                        break;
                    case 'mobile':
                        preview.style.width = '375px';
                        preview.style.margin = '0 auto';
                        document.getElementById('preview-size').textContent = '375×667';
                        break;
                }
            });
        });
    }

    // Chargement des données du template
    function loadTemplateData() {
        if (currentTemplate) {
            document.getElementById('template-name').value = currentTemplate.name || '';
            document.getElementById('template-description').value = currentTemplate.description || '';
            document.getElementById('template-category').value = currentTemplate.category || 'general';
            document.getElementById('template-status').value = currentTemplate.status || 'draft';

            if (currentTemplate.layout) {
                htmlEditor.setValue(currentTemplate.layout);
                document.getElementById('html-editor').value = currentTemplate.layout;
            }

            if (currentTemplate.css) {
                cssEditor.setValue(currentTemplate.css);
            }

            if (currentTemplate.js) {
                jsEditor.setValue(currentTemplate.js);
            }

            // Variables de thème
            if (currentTemplate.variables) {
                loadThemeVariables(currentTemplate.variables);
            }
        }
    }

    // Chargement des variables de thème
    function loadThemeVariables(variables) {
        if (variables.primary_color) {
            updateColorInput('primary-color', variables.primary_color);
        }
        if (variables.secondary_color) {
            updateColorInput('secondary-color', variables.secondary_color);
        }
        if (variables.accent_color) {
            updateColorInput('accent-color', variables.accent_color);
        }
        if (variables.font_family) {
            document.getElementById('font-family').value = variables.font_family;
        }
        if (variables.border_radius) {
            document.getElementById('border-radius').value = variables.border_radius;
        }

        themeVariables = { ...themeVariables, ...variables };
    }

    // Mise à jour des couleurs
    function updateColorInput(inputId, color) {
        const colorInput = document.getElementById(inputId);
        const textInput = colorInput.parentElement.nextElementSibling;
        const swatch = colorInput.parentElement;

        colorInput.value = color;
        textInput.value = color;
        swatch.style.backgroundColor = color;
    }

    // Mise à jour de l'aperçu
    function updatePreview() {
        const loadingOverlay = document.getElementById('preview-loading');
        loadingOverlay.style.display = 'flex';

        const templateData = {
            name: document.getElementById('template-name').value,
            description: document.getElementById('template-description').value,
            layout: htmlEditor.getValue(),
            variables: themeVariables,
            css: cssEditor.getValue(),
            js: jsEditor.getValue()
        };

        // Envoi au serveur pour génération de l'aperçu
        fetch('{{ route("public.opac-templates.preview-ajax") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(templateData)
        })
        .then(response => response.text())
        .then(html => {
            const preview = document.getElementById('live-preview');
            const blob = new Blob([html], {type: 'text/html'});
            const url = URL.createObjectURL(blob);
            preview.src = url;

            loadingOverlay.style.display = 'none';
        })
        .catch(error => {
            console.error('Erreur de prévisualisation:', error);
            loadingOverlay.style.display = 'none';
        });
    }

    // Auto-sauvegarde
    function autoSave() {
        setSaveIndicator('saving');

        const templateData = {
            name: document.getElementById('template-name').value,
            description: document.getElementById('template-description').value,
            category: document.getElementById('template-category').value,
            status: document.getElementById('template-status').value,
            layout: htmlEditor.getValue(),
            variables: themeVariables,
            css: cssEditor.getValue(),
            js: jsEditor.getValue()
        };

        fetch('{{ route("public.opac-templates.auto-save") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                id: currentTemplate?.id,
                ...templateData
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                setSaveIndicator('saved');
                if (data.template_id && !currentTemplate) {
                    currentTemplate = { id: data.template_id };
                }
            } else {
                setSaveIndicator('error');
            }
        })
        .catch(error => {
            console.error('Erreur d\'auto-sauvegarde:', error);
            setSaveIndicator('error');
        });
    }

    // Indicateur de sauvegarde
    function setSaveIndicator(state) {
        const indicator = document.getElementById('save-indicator');
        const text = indicator.querySelector('.save-text');

        indicator.className = 'save-indicator ' + state;

        switch(state) {
            case 'saving':
                text.textContent = 'Enregistrement...';
                break;
            case 'saved':
                text.textContent = 'Enregistré';
                setTimeout(() => {
                    indicator.classList.remove('saved');
                }, 2000);
                break;
            case 'error':
                text.textContent = 'Erreur';
                setTimeout(() => {
                    indicator.classList.remove('error');
                }, 3000);
                break;
        }
    }

    // Fonctions globales
    window.toggleSidebar = function() {
        const sidebar = document.getElementById('editor-sidebar');
        sidebar.classList.toggle('collapsed');
    };

    window.saveTemplate = function() {
        // Remplir le formulaire caché
        document.getElementById('form-name').value = document.getElementById('template-name').value;
        document.getElementById('form-description').value = document.getElementById('template-description').value;
        document.getElementById('form-category').value = document.getElementById('template-category').value;
        document.getElementById('form-status').value = document.getElementById('template-status').value;
        document.getElementById('form-layout').value = htmlEditor.getValue();
        document.getElementById('form-variables').value = JSON.stringify(themeVariables);
        document.getElementById('form-css').value = cssEditor.getValue();
        document.getElementById('form-js').value = jsEditor.getValue();

        // Soumettre le formulaire
        document.getElementById('template-form').submit();
    };

    window.updateThemeVariable = function(variable, value) {
        themeVariables[variable] = value;
        updatePreview();
    };

    window.updateColorSwatch = function(inputId, value) {
        updateColorInput(inputId, value);
        const variable = inputId.replace('-', '_');
        updateThemeVariable(variable, value);
    };

    window.insertComponent = function(component) {
        const componentTemplates = {
            'search-bar': `
@include('opac.components.search-bar', [
    'showFilters' => true,
    'placeholder' => 'Rechercher...',
    'size' => 'medium'
])`,
            'document-card': `
@include('opac.components.document-card', [
    'document' => $document,
    'showMetadata' => true,
    'showBookmark' => true
])`,
            'navigation': `
@include('opac.components.navigation', [
    'showUserMenu' => true,
    'brandText' => 'Mon OPAC'
])`,
            'pagination': `
@include('opac.components.pagination', [
    'paginator' => $documents,
    'showInfo' => true
])`,
            'filters': `
@include('opac.components.filters', [
    'filters' => $availableFilters,
    'currentFilters' => $activeFilters
])`
        };

        const template = componentTemplates[component];
        if (template) {
            const cursor = htmlEditor.getCursor();
            htmlEditor.replaceRange(template, cursor);
            htmlEditor.focus();
        }
    };

    window.loadTemplate = function(templateName) {
        if (confirm('Charger ce template ? Vos modifications actuelles seront perdues.')) {
            fetch(`/admin/opac/templates/load/${templateName}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.template) {
                    loadTemplateFromData(data.template);
                }
            });
        }
    };

    window.resetTemplate = function() {
        if (confirm('Reset le template ? Toutes les modifications seront perdues.')) {
            document.getElementById('template-name').value = '';
            document.getElementById('template-description').value = '';
            htmlEditor.setValue('');
            cssEditor.setValue('');
            jsEditor.setValue('');
            themeVariables = {};
            updatePreview();
        }
    };

    window.exportTemplate = function() {
        const templateData = {
            name: document.getElementById('template-name').value,
            description: document.getElementById('template-description').value,
            category: document.getElementById('template-category').value,
            layout: htmlEditor.getValue(),
            variables: themeVariables,
            css: cssEditor.getValue(),
            js: jsEditor.getValue()
        };

        const blob = new Blob([JSON.stringify(templateData, null, 2)], {type: 'application/json'});
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = (templateData.name || 'template') + '.json';
        a.click();
        URL.revokeObjectURL(url);
    };

    window.refreshPreview = function() {
        updatePreview();
    };

    function loadTemplateFromData(template) {
        document.getElementById('template-name').value = template.name || '';
        document.getElementById('template-description').value = template.description || '';
        document.getElementById('template-category').value = template.category || 'general';

        htmlEditor.setValue(template.layout || '');
        cssEditor.setValue(template.css || '');
        jsEditor.setValue(template.js || '');

        if (template.variables) {
            loadThemeVariables(template.variables);
        }

        updatePreview();
    }

    // Raccourcis clavier
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey || e.metaKey) {
            switch(e.key) {
                case 's':
                    e.preventDefault();
                    saveTemplate();
                    break;
                case 'p':
                    e.preventDefault();
                    updatePreview();
                    break;
            }
        }
    });
});
</script>
@endpush
