<div class="card mb-4 border-primary-subtle" id="aiPrefillCard" data-context="{{ $aiPrefillContext }}">
    <div class="card-body">
        <h6 class="card-title d-flex align-items-center gap-2">
            <i class="bi bi-magic text-primary"></i>
            Préremplir avec l'IA
        </h6>
        <p class="text-muted small mb-3">
            Déposez un document (PDF ou image) : l'IA propose un préremplissage du formulaire ci-dessous.
            Vous restez libre de corriger ou compléter chaque champ avant de valider.
        </p>

        <div id="aiPrefillDropZone" class="border rounded p-3 text-center" style="cursor: pointer; border-style: dashed !important;">
            <i class="bi bi-file-earmark-arrow-up fs-3 text-primary"></i>
            <p class="mb-0" id="aiPrefillDropLabel">Cliquez ou glissez un document ici (PDF, JPG, PNG — 10 Mo max)</p>
            <input type="file" id="aiPrefillFileInput" class="d-none" accept=".pdf,.jpg,.jpeg,.png">
        </div>

        <div id="aiPrefillStatus" class="mt-3 d-none">
            <div class="d-flex align-items-center gap-2">
                <div class="spinner-border spinner-border-sm text-primary d-none" id="aiPrefillSpinner" role="status"></div>
                <span id="aiPrefillStatusText" class="small"></span>
            </div>
        </div>

        <div id="aiPrefillSummary" class="mt-2 small text-muted d-none"></div>

        {{-- Désactivé par défaut : non soumis tant qu'aucune pièce jointe IA n'est chargée
             (évite l'erreur « attachments_pending doit être un entier » sur un champ vide). --}}
        <input type="hidden" name="attachments_pending[]" id="aiPrefillAttachmentIdInput" value="" disabled>
    </div>
</div>
