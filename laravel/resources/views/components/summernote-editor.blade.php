@props([
    'name' => 'content',
    'id' => null,
    'value' => '',
    'height' => 400,
    'required' => false,
    'placeholder' => 'Commencez à écrire...'
])

@php
    $editorId = $id ?? 'summernote_' . $name;
@endphp

<div class="mb-3">
    <label class="form-label" for="{{ $editorId }}">Contenu</label>
    <textarea
        name="{{ $name }}"
        id="{{ $editorId }}"
        class="form-control summernote-editor @error($name) is-invalid @enderror"
        placeholder="{{ $placeholder }}"
        @if($required) required @endif
    >{{ $value }}</textarea>

    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

@once
@push('styles')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
<style>
.note-editor {
    border-radius: 0.375rem;
}
.note-toolbar {
    background-color: #f8f9fa;
    border-bottom: 1px solid #ced4da;
}
.note-editable {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    font-size: 14px;
    line-height: 1.6;
}
.is-invalid + .note-editor {
    border-color: #dc3545 !important;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-fr-FR.min.js"></script>
@endpush
@endonce

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const editorElement = document.getElementById('{{ $editorId }}');

    if (editorElement && typeof $.fn.summernote !== 'undefined') {
        $(editorElement).summernote({
            height: {{ $height }},
            lang: 'fr-FR',
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            placeholder: '{{ $placeholder }}',
            callbacks: {
                onChange: function(contents, $editable) {
                    // Déclencher l'événement change pour la validation Laravel
                    editorElement.dispatchEvent(new Event('change', { bubbles: true }));
                },
                onInit: function() {
                    // Personnalisation après initialisation
                    $(this).next('.note-editor').find('.note-editable').attr('contenteditable', true);
                }
            }
        });

        // Sauvegarder avant la soumission du formulaire
        const form = editorElement.closest('form');
        if (form) {
            form.addEventListener('submit', () => {
                if ($(editorElement).summernote('codeview.isActivated')) {
                    $(editorElement).summernote('codeview.deactivate');
                }
            });
        }
    } else {
        console.warn('Summernote non disponible, affichage du textarea normal');
    }
});
</script>
@endpush
