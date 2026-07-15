(function () {
    'use strict';

    const card = document.getElementById('aiPrefillCard');
    if (!card) return;

    const context = card.dataset.context;
    const dropZone = document.getElementById('aiPrefillDropZone');
    const dropLabel = document.getElementById('aiPrefillDropLabel');
    const fileInput = document.getElementById('aiPrefillFileInput');
    const statusBox = document.getElementById('aiPrefillStatus');
    const statusText = document.getElementById('aiPrefillStatusText');
    const spinner = document.getElementById('aiPrefillSpinner');
    const summaryBox = document.getElementById('aiPrefillSummary');
    const pendingInput = document.getElementById('aiPrefillAttachmentIdInput');

    const csrfToken = () => document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function setStatus(text, showSpinner) {
        statusBox.classList.remove('d-none');
        statusText.textContent = text;
        spinner.classList.toggle('d-none', !showSpinner);
    }

    function markSuggested(fieldId) {
        const el = document.getElementById(fieldId);
        if (!el) return;
        el.classList.add('border-primary');
        if (el.parentElement && !el.parentElement.querySelector('.ai-suggested-badge')) {
            const badge = document.createElement('small');
            badge.className = 'ai-suggested-badge text-primary d-block mt-1';
            badge.innerHTML = '<i class="bi bi-magic"></i> Suggestion IA — à vérifier';
            el.insertAdjacentElement('afterend', badge);
        }
    }

    function applySuggestions(suggestions) {
        const set = (id, value) => {
            const el = document.getElementById(id);
            if (el && value !== null && value !== undefined && value !== '') {
                el.value = value;
                markSuggested(id);
            }
        };

        set('name', suggestions.name);
        set('date', suggestions.date);
        set('typology_id', suggestions.typology_id);
        set('priority_id', suggestions.priority_id);
        set('action_id', suggestions.action_id);

        let summary = '';
        if (suggestions.sender_name) {
            summary += `Expéditeur/destinataire probable : <strong>${escapeHtml(suggestions.sender_name)}</strong>`;
            summary += suggestions.sender_kind ? ` (${suggestions.sender_kind === 'internal' ? 'interne' : 'externe'})` : '';
            summary += ' — à sélectionner manuellement ci-dessous.<br>';
        }
        if (suggestions.confidence_note) {
            summary += `<em>${escapeHtml(suggestions.confidence_note)}</em>`;
        }
        if (summary) {
            summaryBox.innerHTML = summary;
            summaryBox.classList.remove('d-none');
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    async function uploadFile(file) {
        const formData = new FormData();
        formData.append('file', file);

        const response = await fetch('/mails/ai-prefill/upload', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken() },
            body: formData,
        });

        if (!response.ok) {
            throw new Error('upload_failed');
        }

        return response.json();
    }

    async function requestSuggestions(attachmentId) {
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 25000);

        try {
            const response = await fetch('/mails/ai-prefill/suggest', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify({ attachment_id: attachmentId, context: context }),
                signal: controller.signal,
            });

            return await response.json();
        } finally {
            clearTimeout(timeout);
        }
    }

    async function handleFile(file) {
        if (file.size > 10 * 1024 * 1024) {
            setStatus('Fichier trop volumineux (10 Mo max).', false);
            return;
        }

        dropLabel.textContent = file.name;
        setStatus('Envoi du document...', true);

        let uploadResult;
        try {
            uploadResult = await uploadFile(file);
        } catch (e) {
            setStatus("Échec de l'envoi du document. Vous pouvez continuer à remplir manuellement.", false);
            return;
        }

        pendingInput.value = uploadResult.attachment_id;
        setStatus("Analyse du document en cours...", true);

        let result;
        try {
            result = await requestSuggestions(uploadResult.attachment_id);
        } catch (e) {
            setStatus("L'analyse prend plus de temps que prévu. Vous pouvez continuer à remplir manuellement.", false);
            return;
        }

        if (result.status === 'ok') {
            setStatus('Suggestions appliquées — vérifiez et complétez les champs.', false);
            applySuggestions(result.suggestions);
        } else {
            setStatus(result.message || "Impossible d'analyser ce document. Le fichier a bien été joint, remplissez le formulaire manuellement.", false);
        }
    }

    dropZone.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', function () {
        if (this.files[0]) handleFile(this.files[0]);
    });

    ['dragover', 'dragleave', 'drop'].forEach((evt) => {
        dropZone.addEventListener(evt, (e) => e.preventDefault());
    });
    dropZone.addEventListener('drop', (e) => {
        const file = e.dataTransfer.files[0];
        if (file) handleFile(file);
    });
})();
