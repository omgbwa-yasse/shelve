@props([
    'name' => 'content',
    'id' => null,
    'value' => '',
    'height' => 400,
    'required' => false,
    'placeholder' => 'Commencez à écrire...'
])

@php
    $editorId = $id ?? 'quill_' . $name;
@endphp

<div class="mb-3">
    <label class="form-label" for="{{ $editorId }}">{{ $placeholder }}</label>
    <div class="quill-container">
        <div id="{{ $editorId }}_toolbar" class="quill-toolbar">
            <span class="ql-formats">
                <select class="ql-header">
                    <option selected>Normal</option>
                    <option value="1">Titre 1</option>
                    <option value="2">Titre 2</option>
                    <option value="3">Titre 3</option>
                </select>
            </span>
            <span class="ql-formats">
                <button class="ql-bold" title="Gras"></button>
                <button class="ql-italic" title="Italique"></button>
                <button class="ql-underline" title="Souligné"></button>
            </span>
            <span class="ql-formats">
                <select class="ql-color" title="Couleur du texte"></select>
                <select class="ql-background" title="Couleur de fond"></select>
            </span>
            <span class="ql-formats">
                <button class="ql-list" value="ordered" title="Liste numérotée"></button>
                <button class="ql-list" value="bullet" title="Liste à puces"></button>
                <button class="ql-indent" value="-1" title="Diminuer l'indentation"></button>
                <button class="ql-indent" value="+1" title="Augmenter l'indentation"></button>
            </span>
            <span class="ql-formats">
                <button class="ql-align" value="" title="Aligné à gauche"></button>
                <button class="ql-align" value="center" title="Centré"></button>
                <button class="ql-align" value="right" title="Aligné à droite"></button>
                <button class="ql-align" value="justify" title="Justifié"></button>
            </span>
            <span class="ql-formats">
                <button class="ql-link" title="Lien"></button>
                <button class="ql-image" title="Image"></button>
            </span>
            <span class="ql-formats">
                <button class="ql-blockquote" title="Citation"></button>
                <button class="ql-code-block" title="Bloc de code"></button>
            </span>
            <span class="ql-formats">
                <button class="ql-clean" title="Effacer la mise en forme"></button>
            </span>
        </div>

        <div id="{{ $editorId }}_editor" class="quill-editor" style="height: {{ $height }}px;"></div>

        <textarea
            name="{{ $name }}"
            id="{{ $editorId }}"
            class="form-control @error($name) is-invalid @enderror"
            style="display: none;"
            @if($required) required @endif
        >{{ $value }}</textarea>
    </div>

    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

@once
@push('styles')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
.quill-container {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    overflow: hidden;
}
.quill-toolbar {
    border: none !important;
    border-bottom: 1px solid #ced4da !important;
    background: #f8f9fa;
}
.quill-editor {
    border: none !important;
}
.ql-container {
    font-size: 14px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}
.ql-editor {
    padding: 15px;
    min-height: inherit;
}
.ql-editor.ql-blank::before {
    color: #6c757d;
    font-style: italic;
}
.quill-container:focus-within {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
.is-invalid + .quill-container {
    border-color: #dc3545;
}
.is-invalid + .quill-container:focus-within {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
@endpush
@endonce

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const editorElement = document.getElementById('{{ $editorId }}_editor');
    const textareaElement = document.getElementById('{{ $editorId }}');
    const toolbarElement = document.getElementById('{{ $editorId }}_toolbar');

    if (editorElement && textareaElement && toolbarElement) {
        // Configuration de Quill
        const quill = new Quill(editorElement, {
            modules: {
                toolbar: toolbarElement
            },
            placeholder: '{{ $placeholder }}',
            theme: 'snow'
        });

        // Initialiser avec le contenu du textarea
        if (textareaElement.value) {
            quill.root.innerHTML = textareaElement.value;
        }

        // Synchroniser les changements avec le textarea
        quill.on('text-change', function() {
            textareaElement.value = quill.root.innerHTML;
            // Déclencher l'événement change pour la validation Laravel
            textareaElement.dispatchEvent(new Event('change', { bubbles: true }));
        });

        // Sauvegarder avant la soumission du formulaire
        const form = textareaElement.closest('form');
        if (form) {
            form.addEventListener('submit', () => {
                textareaElement.value = quill.root.innerHTML;
            });
        }

        // Stocker l'instance pour un accès global si nécessaire
        window['quill_{{ $editorId }}'] = quill;
    }
});
</script>
@endpush
