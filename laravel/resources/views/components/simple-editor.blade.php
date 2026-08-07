@props([
    'name' => 'content',
    'id' => null,
    'value' => '',
    'height' => 400,
    'required' => false,
    'placeholder' => 'Commencez à écrire...'
])

@php
    $editorId = $id ?? 'simple_' . $name;
@endphp

<div class="mb-3">
    <label class="form-label" for="{{ $editorId }}">Contenu</label>

    <!-- Barre d'outils simple -->
    <div class="simple-editor-toolbar" data-target="{{ $editorId }}">
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-secondary" data-command="bold" title="Gras">
                <i class="bi bi-type-bold"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary" data-command="italic" title="Italique">
                <i class="bi bi-type-italic"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary" data-command="underline" title="Souligné">
                <i class="bi bi-type-underline"></i>
            </button>
        </div>

        <div class="btn-group btn-group-sm ms-2" role="group">
            <button type="button" class="btn btn-outline-secondary" data-command="insertUnorderedList" title="Liste à puces">
                <i class="bi bi-list-ul"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary" data-command="insertOrderedList" title="Liste numérotée">
                <i class="bi bi-list-ol"></i>
            </button>
        </div>

        <div class="btn-group btn-group-sm ms-2" role="group">
            <button type="button" class="btn btn-outline-secondary" data-command="justifyLeft" title="Aligner à gauche">
                <i class="bi bi-text-left"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary" data-command="justifyCenter" title="Centrer">
                <i class="bi bi-text-center"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary" data-command="justifyRight" title="Aligner à droite">
                <i class="bi bi-text-right"></i>
            </button>
        </div>

        <div class="btn-group btn-group-sm ms-2" role="group">
            <button type="button" class="btn btn-outline-secondary" data-command="createLink" title="Insérer un lien">
                <i class="bi bi-link-45deg"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary" data-command="unlink" title="Supprimer le lien">
                <i class="bi bi-link-45deg-fill"></i>
            </button>
        </div>

        <div class="btn-group btn-group-sm ms-2" role="group">
            <select class="form-select form-select-sm" data-command="formatBlock" style="width: auto;">
                <option value="">Format</option>
                <option value="p">Paragraphe</option>
                <option value="h1">Titre 1</option>
                <option value="h2">Titre 2</option>
                <option value="h3">Titre 3</option>
                <option value="h4">Titre 4</option>
            </select>
        </div>

        <div class="btn-group btn-group-sm ms-2" role="group">
            <button type="button" class="btn btn-outline-danger" data-command="removeFormat" title="Effacer la mise en forme">
                <i class="bi bi-eraser"></i>
            </button>
        </div>
    </div>

    <!-- Zone d'édition -->
    <div
        id="{{ $editorId }}_editor"
        class="simple-editor @error($name) is-invalid @enderror"
        contenteditable="true"
        style="min-height: {{ $height }}px;"
        data-placeholder="{{ $placeholder }}"
    >{!! $value !!}</div>

    <!-- Textarea caché pour le formulaire -->
    <textarea
        name="{{ $name }}"
        id="{{ $editorId }}"
        class="d-none"
        @if($required) required @endif
    >{{ $value }}</textarea>

    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

@once
@push('styles')
<style>
.simple-editor-toolbar {
    padding: 10px;
    border: 1px solid #ced4da;
    border-bottom: none;
    background-color: #f8f9fa;
    border-radius: 0.375rem 0.375rem 0 0;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 5px;
}

.simple-editor {
    border: 1px solid #ced4da;
    border-top: none;
    border-radius: 0 0 0.375rem 0.375rem;
    padding: 15px;
    background-color: white;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    font-size: 14px;
    line-height: 1.6;
    overflow-y: auto;
}

.simple-editor:focus {
    outline: none;
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.simple-editor.is-invalid {
    border-color: #dc3545;
}

.simple-editor.is-invalid:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.simple-editor:empty::before {
    content: attr(data-placeholder);
    color: #6c757d;
    font-style: italic;
}

.simple-editor h1, .simple-editor h2, .simple-editor h3, .simple-editor h4 {
    margin-top: 1rem;
    margin-bottom: 0.5rem;
}

.simple-editor p {
    margin-bottom: 1rem;
}

.simple-editor ul, .simple-editor ol {
    margin-bottom: 1rem;
    padding-left: 2rem;
}

.simple-editor a {
    color: #007bff;
    text-decoration: underline;
}

.simple-editor-toolbar .btn.active {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}
</style>
@endpush
@endonce

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const editorElement = document.getElementById('{{ $editorId }}_editor');
    const textareaElement = document.getElementById('{{ $editorId }}');
    const toolbar = document.querySelector('.simple-editor-toolbar[data-target="{{ $editorId }}"]');

    if (editorElement && textareaElement && toolbar) {

        // Synchroniser le contenu avec le textarea
        function syncContent() {
            textareaElement.value = editorElement.innerHTML;
            textareaElement.dispatchEvent(new Event('change', { bubbles: true }));
        }

        // Écouter les changements dans l'éditeur
        editorElement.addEventListener('input', syncContent);
        editorElement.addEventListener('paste', function() {
            setTimeout(syncContent, 10);
        });

        // Gestion des boutons de la barre d'outils
        toolbar.addEventListener('click', function(e) {
            const button = e.target.closest('button');
            if (!button) return;

            e.preventDefault();
            editorElement.focus();

            const command = button.dataset.command;
            if (command === 'createLink') {
                const url = prompt('Entrez l\'URL du lien:');
                if (url) {
                    document.execCommand(command, false, url);
                }
            } else {
                document.execCommand(command, false, null);
            }

            syncContent();
            updateToolbarState();
        });

        // Gestion du sélecteur de format
        const formatSelect = toolbar.querySelector('select[data-command="formatBlock"]');
        if (formatSelect) {
            formatSelect.addEventListener('change', function() {
                if (this.value) {
                    editorElement.focus();
                    document.execCommand('formatBlock', false, this.value);
                    syncContent();
                }
                this.value = '';
            });
        }

        // Mettre à jour l'état des boutons selon la sélection
        function updateToolbarState() {
            const buttons = toolbar.querySelectorAll('button[data-command]');
            buttons.forEach(button => {
                const command = button.dataset.command;
                if (document.queryCommandState && document.queryCommandState(command)) {
                    button.classList.add('active');
                } else {
                    button.classList.remove('active');
                }
            });
        }

        // Écouter les changements de sélection
        editorElement.addEventListener('mouseup', updateToolbarState);
        editorElement.addEventListener('keyup', updateToolbarState);

        // Sauvegarder avant la soumission du formulaire
        const form = textareaElement.closest('form');
        if (form) {
            form.addEventListener('submit', syncContent);
        }

        // Gérer le collage de contenu
        editorElement.addEventListener('paste', function(e) {
            e.preventDefault();
            const text = (e.originalEvent || e).clipboardData.getData('text/plain');
            document.execCommand('insertText', false, text);
        });

        // Initialisation
        syncContent();
        updateToolbarState();
    }
});
</script>
@endpush
