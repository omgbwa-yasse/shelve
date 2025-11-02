@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h2>Test des éditeurs WYSIWYG</h2>
                </div>

                <div class="card-body">
                    <form method="POST" action="#" onsubmit="return false;">
                        @csrf

                        <h4 class="mt-4 mb-3">1. CKEditor 5 (Recommandé)</h4>
                        <x-wysiwyg-editor
                            name="content_ckeditor"
                            id="ckeditor_test"
                            value="<p>Ceci est un test avec <strong>CKEditor 5</strong>. Il est moderne et très fiable.</p>"
                            :height="300"
                            placeholder="Testez CKEditor ici..."
                            :required="false"
                        />

                        <hr class="my-4">

                        <h4 class="mt-4 mb-3">2. Summernote (Simple et efficace)</h4>
                        <x-summernote-editor
                            name="content_summernote"
                            id="summernote_test"
                            value="<p>Ceci est un test avec <strong>Summernote</strong>. Il est simple et fonctionne bien.</p>"
                            :height="300"
                            placeholder="Testez Summernote ici..."
                            :required="false"
                        />

                        <hr class="my-4">

                        <h4 class="mt-4 mb-3">3. Quill (Moderne et léger)</h4>
                        <x-quill-editor
                            name="content_quill"
                            id="quill_test"
                            value="<p>Ceci est un test avec <strong>Quill</strong>. Il est moderne et léger.</p>"
                            :height="300"
                            placeholder="Testez Quill ici..."
                            :required="false"
                        />

                        <hr class="my-4">

                        <h4 class="mt-4 mb-3">4. Éditeur simple (Toujours fonctionnel)</h4>
                        <x-simple-editor
                            name="content_simple"
                            id="simple_test"
                            value="<p>Ceci est un test avec l'<strong>éditeur simple</strong>. Il fonctionne toujours, même sans CDN.</p>"
                            :height="300"
                            placeholder="Testez l'éditeur simple ici..."
                            :required="false"
                        />

                        <hr class="my-4">

                        <h4 class="mt-4 mb-3">5. Textarea simple (Style email) ⭐</h4>
                        <x-textarea-editor
                            name="content_textarea"
                            id="textarea_test"
                            value="Ceci est un textarea simple comme celui utilisé dans les emails et communications. Style identique à votre application."
                            :rows="8"
                            placeholder="Style identique aux emails..."
                            :required="false"
                        />

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-info" onclick="showAllContents()">
                                Voir tous les contenus
                            </button>
                            <a href="{{ route('public.pages.index') }}" class="btn btn-secondary">
                                Retour aux pages
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour afficher les contenus -->
<div class="modal fade" id="contentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Contenus des éditeurs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="allContents"></div>
            </div>
        </div>
    </div>
</div>

<script>
function showAllContents() {
    let html = '';

    // CKEditor
    const ckeditorContent = document.querySelector('input[name="content_ckeditor"]')?.value ||
                           document.querySelector('textarea[name="content_ckeditor"]')?.value ||
                           (window.editor_ckeditor_test ? window.editor_ckeditor_test.getData() : 'Non disponible');

    html += '<h6>CKEditor:</h6><div class="border p-2 mb-3">' + ckeditorContent + '</div>';

    // Summernote
    const summernoteContent = document.querySelector('textarea[name="content_summernote"]')?.value || 'Non disponible';
    html += '<h6>Summernote:</h6><div class="border p-2 mb-3">' + summernoteContent + '</div>';

    // Quill
    const quillContent = document.querySelector('textarea[name="content_quill"]')?.value ||
                        (window.quill_quill_test ? window.quill_quill_test.root.innerHTML : 'Non disponible');
    html += '<h6>Quill:</h6><div class="border p-2 mb-3">' + quillContent + '</div>';

    // Simple Editor
    const simpleContent = document.querySelector('textarea[name="content_simple"]')?.value || 'Non disponible';
    html += '<h6>Éditeur simple:</h6><div class="border p-2 mb-3">' + simpleContent + '</div>';

    // Textarea simple (style email)
    const textareaContent = document.querySelector('textarea[name="content_textarea"]')?.value || 'Non disponible';
    html += '<h6>Textarea simple (style email):</h6><div class="border p-2 mb-3">' + textareaContent + '</div>';

    document.getElementById('allContents').innerHTML = html;    // Afficher le modal
    const modal = new bootstrap.Modal(document.getElementById('contentModal'));
    modal.show();
}
</script>
@endsection
