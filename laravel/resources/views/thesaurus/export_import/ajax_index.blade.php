@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">{{ __('Thésaurus - Import/Export') }}</h1>

            <div class="row">
                <!-- Section Export -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ __('Export du thésaurus') }}</h4>
                        </div>
                        <div class="card-body">
                            <p>{{ __('Exportez votre thésaurus dans différents formats pour le partager ou l\'archiver.') }}</p>

                            <div class="d-grid gap-3">
                                <button type="button" class="btn btn-primary export-btn" data-format="skos-rdf">
                                    <i class="bi bi-file-earmark-code"></i> {{ __('Exporter au format SKOS/RDF (XML)') }}
                                    <span class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                                </button>
                                <button type="button" class="btn btn-primary export-btn" data-format="csv">
                                    <i class="bi bi-file-earmark-spreadsheet"></i> {{ __('Exporter au format CSV') }}
                                    <span class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                                </button>
                            </div>

                            <!-- Progress bar for export -->
                            <div class="progress mt-3 d-none" id="export-progress">
                                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                            </div>

                            <!-- Export results -->
                            <div id="export-results" class="mt-3"></div>
                        </div>
                    </div>
                </div>

                <!-- Section Import -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ __('Import du thésaurus') }}</h4>
                        </div>
                        <div class="card-body">
                            <p>{{ __('Importez un thésaurus existant à partir d\'un fichier externe.') }}</p>

                            <!-- Import Form -->
                            <form id="import-form" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label for="import_format" class="form-label">{{ __('Format du fichier') }}</label>
                                    <select class="form-select" id="import_format" name="import_format" required>
                                        <option value="">{{ __('Sélectionner un format') }}</option>
                                        <option value="skos-rdf">SKOS/RDF (XML)</option>
                                        <option value="csv">CSV</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="import_file" class="form-label">{{ __('Fichier à importer') }}</label>
                                    <input type="file" class="form-control" id="import_file" name="import_file" required>
                                    <div class="form-text">
                                        <span id="file-format-help">{{ __('Sélectionnez d\'abord un format') }}</span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="import_mode" class="form-label">{{ __('Mode d\'import') }}</label>
                                    <select class="form-select" id="import_mode" name="import_mode" required>
                                        <option value="add">{{ __('Ajouter uniquement (ignorer les termes existants)') }}</option>
                                        <option value="update">{{ __('Mettre à jour uniquement (ignorer les nouveaux termes)') }}</option>
                                        <option value="replace">{{ __('Remplacer tout (supprimer le thésaurus existant)') }}</option>
                                        <option value="merge">{{ __('Fusionner (ajouter et mettre à jour)') }}</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="preview_import" name="preview_import" checked>
                                        <label class="form-check-label" for="preview_import">
                                            {{ __('Prévisualiser avant import') }}
                                        </label>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-cloud-arrow-up"></i> {{ __('Importer') }}
                                    <span class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                                </button>
                            </form>

                            <!-- Progress bar for import -->
                            <div class="progress mt-3 d-none" id="import-progress">
                                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                            </div>

                            <!-- Import results -->
                            <div id="import-results" class="mt-3"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview Modal -->
            <div class="modal fade" id="preview-modal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="previewModalLabel">{{ __('Prévisualisation de l\'import') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="preview-content">
                                <div class="d-flex justify-content-center">
                                    <div class="spinner-border" role="status">
                                        <span class="visually-hidden">{{ __('Chargement...') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                            <button type="button" class="btn btn-success" id="confirm-import">
                                {{ __('Confirmer l\'import') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Information et aide -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ __('Informations') }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>{{ __('Format SKOS/RDF') }}</h5>
                                    <p class="small">{{ __('SKOS (Simple Knowledge Organization System) est une norme du W3C pour la représentation de thésaurus et de vocabulaires contrôlés, écrite en RDF (Resource Description Framework). Ce format permet la description sémantique et l\'échange standardisé de données terminologiques.') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h5>{{ __('Format CSV') }}</h5>
                                    <p class="small">{{ __('Le format CSV permet un import/export simple via des tableurs comme Excel ou LibreOffice Calc. Structure: terme, définition, synonymes, relations.') }}</p>
                                </div>
                            </div>

                            <div class="alert alert-warning mt-3">
                                <i class="bi bi-exclamation-triangle"></i>
                                {{ __('Attention : L\'import de données remplacera ou fusionnera avec votre thésaurus existant selon les options choisies. Il est recommandé de faire une sauvegarde avant d\'importer des données.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Handle format selection for file input
    $('#import_format').change(function() {
        const format = $(this).val();
        const fileInput = $('#import_file');
        const helpText = $('#file-format-help');

        switch(format) {
            case 'skos-rdf':
                fileInput.attr('accept', '.xml,.rdf,.n3,.ttl');
                helpText.text('{{ __("Fichiers SKOS/RDF (XML, N3, TTL) acceptés") }}');
                break;
            case 'csv':
                fileInput.attr('accept', '.csv');
                helpText.text('{{ __("Fichiers CSV acceptés") }}');
                break;
            default:
                fileInput.attr('accept', '');
                helpText.text('{{ __("Sélectionnez d\'abord un format") }}');
        }
    });

    // Handle export buttons
    $('.export-btn').click(function() {
        const format = $(this).data('format');
        const btn = $(this);
        const spinner = btn.find('.spinner-border');
        const progressBar = $('#export-progress');
        const resultsDiv = $('#export-results');

        btn.prop('disabled', true);
        spinner.removeClass('d-none');
        progressBar.removeClass('d-none');
        resultsDiv.empty();

        // Simulate progress
        let progress = 0;
        const progressInterval = setInterval(() => {
            progress += 10;
            progressBar.find('.progress-bar').css('width', progress + '%');
            if (progress >= 90) {
                clearInterval(progressInterval);
            }
        }, 200);

        $.ajax({
            url: '{{ route("thesaurus.export.ajax") }}',
            type: 'POST',
            data: {
                format: format,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                clearInterval(progressInterval);
                progressBar.find('.progress-bar').css('width', '100%');

                setTimeout(() => {
                    progressBar.addClass('d-none');
                    btn.prop('disabled', false);
                    spinner.addClass('d-none');

                    if (response.success) {
                        resultsDiv.html(`
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> ${response.message}
                                <br>
                                <a href="${response.download_url}" class="btn btn-sm btn-outline-success mt-2">
                                    <i class="bi bi-download"></i> {{ __('Télécharger') }}
                                </a>
                            </div>
                        `);
                    } else {
                        resultsDiv.html(`
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i> ${response.message}
                            </div>
                        `);
                    }
                }, 500);
            },
            error: function(xhr) {
                clearInterval(progressInterval);
                progressBar.addClass('d-none');
                btn.prop('disabled', false);
                spinner.addClass('d-none');

                resultsDiv.html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> {{ __('Erreur lors de l\'export') }}: ${xhr.responseJSON?.message || xhr.statusText}
                    </div>
                `);
            }
        });
    });

    // Handle import form submission
    $('#import-form').submit(function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const progressBar = $('#import-progress');
        const resultsDiv = $('#import-results');
        const submitBtn = $(this).find('button[type="submit"]');
        const spinner = submitBtn.find('.spinner-border');
        const previewCheckbox = $('#preview_import');

        // Validation
        if (!$('#import_format').val() || !$('#import_file')[0].files.length) {
            resultsDiv.html(`
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> {{ __('Veuillez sélectionner un format et un fichier') }}
                </div>
            `);
            return;
        }

        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        progressBar.removeClass('d-none');
        resultsDiv.empty();

        // Start progress simulation
        let progress = 0;
        const progressInterval = setInterval(() => {
            progress += 5;
            progressBar.find('.progress-bar').css('width', progress + '%');
            if (progress >= 80) {
                clearInterval(progressInterval);
            }
        }, 100);

        const url = previewCheckbox.is(':checked') ?
            '{{ route("thesaurus.import.preview") }}' :
            '{{ route("thesaurus.import.process") }}';

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                clearInterval(progressInterval);
                progressBar.find('.progress-bar').css('width', '100%');

                setTimeout(() => {
                    progressBar.addClass('d-none');
                    submitBtn.prop('disabled', false);
                    spinner.addClass('d-none');

                    if (previewCheckbox.is(':checked') && response.preview) {
                        // Show preview modal
                        $('#preview-content').html(response.preview);
                        $('#preview-modal').modal('show');
                        $('#confirm-import').data('import-data', formData);
                    } else if (response.success) {
                        resultsDiv.html(`
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> ${response.message}
                                ${response.stats ? `<br><small>${response.stats}</small>` : ''}
                            </div>
                        `);
                    } else {
                        resultsDiv.html(`
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i> ${response.message}
                            </div>
                        `);
                    }
                }, 500);
            },
            error: function(xhr) {
                clearInterval(progressInterval);
                progressBar.addClass('d-none');
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');

                let errorMessage = '{{ __("Erreur lors de l\'import") }}';
                if (xhr.responseJSON?.message) {
                    errorMessage += ': ' + xhr.responseJSON.message;
                } else if (xhr.responseJSON?.errors) {
                    errorMessage += ':<ul>';
                    Object.values(xhr.responseJSON.errors).forEach(errors => {
                        errors.forEach(error => {
                            errorMessage += `<li>${error}</li>`;
                        });
                    });
                    errorMessage += '</ul>';
                }

                resultsDiv.html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> ${errorMessage}
                    </div>
                `);
            }
        });
    });

    // Handle import confirmation from preview
    $('#confirm-import').click(function() {
        const importData = $(this).data('import-data');
        const progressBar = $('#import-progress');
        const resultsDiv = $('#import-results');

        $('#preview-modal').modal('hide');
        progressBar.removeClass('d-none');

        // Start progress simulation
        let progress = 0;
        const progressInterval = setInterval(() => {
            progress += 10;
            progressBar.find('.progress-bar').css('width', progress + '%');
            if (progress >= 90) {
                clearInterval(progressInterval);
            }
        }, 200);

        $.ajax({
            url: '{{ route("thesaurus.import.process") }}',
            type: 'POST',
            data: importData,
            processData: false,
            contentType: false,
            success: function(response) {
                clearInterval(progressInterval);
                progressBar.find('.progress-bar').css('width', '100%');

                setTimeout(() => {
                    progressBar.addClass('d-none');

                    if (response.success) {
                        resultsDiv.html(`
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> ${response.message}
                                ${response.stats ? `<br><small>${response.stats}</small>` : ''}
                            </div>
                        `);
                    } else {
                        resultsDiv.html(`
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i> ${response.message}
                            </div>
                        `);
                    }
                }, 500);
            },
            error: function(xhr) {
                clearInterval(progressInterval);
                progressBar.addClass('d-none');

                resultsDiv.html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> {{ __('Erreur lors de l\'import confirmé') }}: ${xhr.responseJSON?.message || xhr.statusText}
                    </div>
                `);
            }
        });
    });
});
</script>
@endpush
@endsection
