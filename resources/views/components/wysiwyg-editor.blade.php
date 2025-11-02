@props([
    'name' => 'content',
    'id' => null,
    'value' => '',
    'height' => 400,
    'required' => false,
    'placeholder' => 'Commencez à écrire...'
])

@php
    $editorId = $id ?? 'editor_' . $name;
@endphp

<div class="mb-3">
    <label class="form-label" for="{{ $editorId }}">{{ $placeholder }}</label>
    <div class="ckeditor-container">
        <textarea
            name="{{ $name }}"
            id="{{ $editorId }}"
            class="form-control ckeditor-editor @error($name) is-invalid @enderror"
            style="display: none;"
            @if($required) required @endif
        >{{ $value }}</textarea>
        <div id="{{ $editorId }}_editor" class="ckeditor-instance" style="min-height: {{ $height }}px;"></div>
    </div>

    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

@once
@push('styles')
<style>
.ckeditor-container {
    position: relative;
}
.ck-editor__editable {
    min-height: {{ $height }}px !important;
    border-radius: 0.375rem;
    border: 1px solid #ced4da;
}
.ck-editor__editable:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
.ck.ck-toolbar {
    border-radius: 0.375rem 0.375rem 0 0;
    border: 1px solid #ced4da;
    background: #f8f9fa;
}
.ck.ck-editor__main > .ck-editor__editable {
    border-radius: 0 0 0.375rem 0.375rem;
    border-top: none;
}
.ck.ck-editor__editable.ck-focused {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/40.1.0/classic/ckeditor.js"></script>
@endpush
@endonce

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const editorElement = document.getElementById('{{ $editorId }}_editor');
    const textareaElement = document.getElementById('{{ $editorId }}');

    if (editorElement && textareaElement) {
        ClassicEditor
            .create(editorElement, {
                language: 'fr',
                toolbar: {
                    items: [
                        'heading', '|',
                        'bold', 'italic', 'underline', '|',
                        'fontSize', 'fontColor', 'fontBackgroundColor', '|',
                        'alignment', '|',
                        'numberedList', 'bulletedList', '|',
                        'outdent', 'indent', '|',
                        'link', 'insertTable', '|',
                        'blockQuote', 'insertImage', '|',
                        'undo', 'redo', '|',
                        'sourceEditing'
                    ]
                },
                heading: {
                    options: [
                        { model: 'paragraph', title: 'Paragraphe', class: 'ck-heading_paragraph' },
                        { model: 'heading1', view: 'h1', title: 'Titre 1', class: 'ck-heading_heading1' },
                        { model: 'heading2', view: 'h2', title: 'Titre 2', class: 'ck-heading_heading2' },
                        { model: 'heading3', view: 'h3', title: 'Titre 3', class: 'ck-heading_heading3' }
                    ]
                },
                fontSize: {
                    options: [ 9, 11, 13, 'default', 17, 19, 21 ],
                    supportAllValues: true
                },
                alignment: {
                    options: [ 'left', 'center', 'right', 'justify' ]
                },
                table: {
                    contentToolbar: [ 'tableColumn', 'tableRow', 'mergeTableCells' ]
                },
                image: {
                    toolbar: [
                        'imageTextAlternative', 'imageStyle:inline', 'imageStyle:block', 'imageStyle:side'
                    ]
                }
            })
            .then(editor => {
                // Initialiser avec le contenu du textarea
                editor.setData(textareaElement.value);

                // Synchroniser les changements avec le textarea
                editor.model.document.on('change:data', () => {
                    textareaElement.value = editor.getData();
                    // Déclencher l'événement change pour la validation Laravel
                    textareaElement.dispatchEvent(new Event('change', { bubbles: true }));
                });

                // Gérer la validation des erreurs
                if (textareaElement.classList.contains('is-invalid')) {
                    editor.ui.view.editable.element.style.borderColor = '#dc3545';
                }

                // Sauvegarder avant la soumission du formulaire
                const form = textareaElement.closest('form');
                if (form) {
                    form.addEventListener('submit', () => {
                        textareaElement.value = editor.getData();
                    });
                }

                window['editor_{{ $editorId }}'] = editor;
            })
            .catch(error => {
                console.error('Erreur lors de l\'initialisation de CKEditor:', error);
                // En cas d'erreur, afficher le textarea normal
                textareaElement.style.display = 'block';
                editorElement.style.display = 'none';
            });
    }
});
</script>
@endpush
