@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h2><i class="bi bi-file-earmark-arrow-down"></i> {{ __('Import Records') }}</h2>
                <a href="{{ asset('docs/import_format.md') }}" target="_blank" class="btn btn-outline-info btn-sm">
                    <i class="bi bi-question-circle me-1"></i>{{ __('Format Guide') }}
                </a>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Aide sur les champs requis -->
                <div class="alert alert-info mb-4">
                    <h5><i class="bi bi-info-circle me-2"></i>Champs requis pour l'import</h5>
                    <p class="mb-2">Pour qu'un record soit importé avec succès, les champs suivants sont <strong>obligatoires</strong> :</p>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="mb-0">
                                <li><strong>code</strong> - Code unique du record <span class="badge bg-success">Auto-générable</span></li>
                                <li><strong>name</strong> - Nom/titre du record</li>
                                <li><strong>level</strong> - Niveau hiérarchique (fonds, série, etc.)</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="mb-0">
                                <li><strong>status</strong> - Statut du record (actif, inactif, etc.)</li>
                                <li><strong>support</strong> - Support matériel (papier, numérique, etc.)</li>
                                <li><strong>activity</strong> - Activité/domaine (administration, culture, etc.)</li>
                            </ul>
                        </div>
                    </div>
                    <div class="mt-3">
                        <p class="mb-2"><i class="bi bi-lightbulb text-warning me-1"></i><strong>Note :</strong> Si le champ <code>code</code> est vide ou manquant dans votre fichier, des codes uniques seront automatiquement générés pour chaque record.</p>
                    </div>
                </div>

                <!-- Étape 1: Upload du fichier -->
                <div id="upload-step" class="import-step">
                    <form id="upload-form" enctype="multipart/form-data" onsubmit="return false;">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="file" class="form-label">{{ __('File') }}:</label>
                            <input type="file" name="file" id="file" class="form-control" required accept=".xlsx,.csv">
                        </div>
                        <div class="form-group mb-3">
                            <label for="format" class="form-label">{{ __('Format') }}:</label>
                            <select name="format" id="format" class="form-select" required>
                                <option value="">{{ __('Select a format') }}</option>
                                <option value="excel">Excel</option>
                                <option value="csv">CSV</option>
                            </select>
                        </div>

                        <div class="form-group mb-3 excel-options" style="display: none;">
                            <div class="card">
                                <div class="card-header">{{ __('Import Options') }}</div>
                                <div class="card-body">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="has_headers" id="has_headers" value="1" checked>
                                        <label class="form-check-label" for="has_headers">
                                            {{ __('File has headers') }}
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="update_existing" id="update_existing" value="1">
                                        <label class="form-check-label" for="update_existing">
                                            {{ __('Update existing records') }}
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="auto_generate_codes" id="auto_generate_codes" value="1" checked>
                                        <label class="form-check-label" for="auto_generate_codes">
                                            <i class="bi bi-magic text-success me-1"></i>{{ __('Auto-generate codes for empty fields') }}
                                        </label>
                                        <small class="form-text text-muted">
                                            {{ __('Automatically create unique codes when the code field is empty or missing') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('records.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                            <button type="button" id="analyze-file" class="btn btn-primary">
                                <i class="bi bi-search me-1"></i>{{ __('Analyze File') }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Étape 2: Mapping des colonnes -->
                <div id="mapping-step" class="import-step" style="display: none;">
                    <h4>{{ __('Column Mapping') }}</h4>
                    <p class="text-muted">{{ __('Map your file columns to database fields') }}</p>
                    <div id="mapping-container"></div>
                    <div class="d-flex justify-content-between mt-3">
                        <button type="button" id="back-to-upload" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}
                        </button>
                        <button type="button" id="start-import" class="btn btn-success">
                            <i class="bi bi-file-earmark-arrow-down me-1"></i>{{ __('Start Import') }}
                        </button>
                    </div>
                </div>

                <!-- Étape 3: Progression de l'import -->
                <div id="import-step" class="import-step" style="display: none;">
                    <h4>{{ __('Import in Progress') }}</h4>
                    <div class="progress mb-3">
                        <div id="import-progress" class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                    <div id="import-status"></div>
                </div>
            </div>

            <div id="excel-instructions" class="card-footer bg-light" style="display: none;">
                <h5>{{ __('Excel Import Field Instructions') }}</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr class="bg-dark text-secondary">
                                <th>{{ __('Field Name') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Format') }}</th>
                                <th>{{ __('Required') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>code</code></td>
                                <td>{{ __('Record unique identifier') }}</td>
                                <td>{{ __('Text') }}</td>
                                <td>{{ __('Yes') }}</td>
                            </tr>
                            <tr>
                                <td><code>name</code></td>
                                <td>{{ __('Record name/title') }}</td>
                                <td>{{ __('Text') }}</td>
                                <td>{{ __('Yes') }}</td>
                            </tr>
                            <tr>
                                <td><code>date_format</code></td>
                                <td>{{ __('Format of the date') }}</td>
                                <td>{{ __('Text') }}</td>
                                <td>{{ __('No') }}</td>
                            </tr>
                            <tr>
                                <td><code>start_date</code></td>
                                <td>{{ __('Starting date of the record') }}</td>
                                <td>{{ __('YYYY-MM-DD') }}</td>
                                <td>{{ __('No') }}</td>
                            </tr>
                            <tr>
                                <td><code>end_date</code></td>
                                <td>{{ __('Ending date of the record') }}</td>
                                <td>{{ __('YYYY-MM-DD') }}</td>
                                <td>{{ __('No') }}</td>
                            </tr>
                            <tr>
                                <td><code>exact_date</code></td>
                                <td>{{ __('Exact date of the record') }}</td>
                                <td>{{ __('YYYY-MM-DD') }}</td>
                                <td>{{ __('No') }}</td>
                            </tr>
                            <tr>
                                <td><code>level</code></td>
                                <td>{{ __('Hierarchical level') }}</td>
                                <td>{{ __('Text') }}</td>
                                <td>{{ __('Yes') }}</td>
                            </tr>
                            <tr>
                                <td><code>width</code></td>
                                <td>{{ __('Physical width of the record') }}</td>
                                <td>{{ __('Number') }}</td>
                                <td>{{ __('No') }}</td>
                            </tr>
                            <tr>
                                <td><code>width_description</code></td>
                                <td>{{ __('Description of the width') }}</td>
                                <td>{{ __('Text') }}</td>
                                <td>{{ __('No') }}</td>
                            </tr>
                            <tr>
                                <td><code>content</code></td>
                                <td>{{ __('Content description of the record') }}</td>
                                <td>{{ __('Text') }}</td>
                                <td>{{ __('No') }}</td>
                            </tr>
                            <tr>
                                <td><code>status</code></td>
                                <td>{{ __('Status of the record') }}</td>
                                <td>{{ __('Text') }}</td>
                                <td>{{ __('Yes') }}</td>
                            </tr>
                            <tr>
                                <td><code>support</code></td>
                                <td>{{ __('Physical support type') }}</td>
                                <td>{{ __('Text') }}</td>
                                <td>{{ __('Yes') }}</td>
                            </tr>
                            <tr>
                                <td><code>activity</code></td>
                                <td>{{ __('Related activity') }}</td>
                                <td>{{ __('Text') }}</td>
                                <td>{{ __('Yes') }}</td>
                            </tr>
                            <tr>
                                <td><code>authors</code></td>
                                <td>{{ __('Record authors') }}</td>
                                <td>{{ __('Comma separated names') }}</td>
                                <td>{{ __('No') }}</td>
                            </tr>
                            <tr>
                                <td><code>terms</code></td>
                                <td>{{ __('Related terms/tags') }}</td>
                                <td>{{ __('Comma separated values') }}</td>
                                <td>{{ __('No') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-info mt-3">
                    <p><strong>{{ __('Note') }}:</strong> {{ __('For EAD and SEDA imports, files must be valid XML following the respective schema.') }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const formatSelect = document.getElementById('format');
    const excelOptions = document.querySelector('.excel-options');
    const uploadStep = document.getElementById('upload-step');
    const mappingStep = document.getElementById('mapping-step');
    const importStep = document.getElementById('import-step');

    let fileHeaders = [];
    let uploadedFile = null;

    formatSelect.addEventListener('change', function() {
        if (this.value === 'excel' || this.value === 'csv') {
            excelOptions.style.display = 'block';
        } else {
            excelOptions.style.display = 'none';
        }
    });

    document.getElementById('analyze-file').addEventListener('click', function() {
        const fileInput = document.getElementById('file');
        const formatInput = document.getElementById('format');
        if (!fileInput.files[0] || !formatInput.value) {
            alert('Veuillez sélectionner un fichier et un format');
            return;
        }
        uploadedFile = fileInput.files[0];
        analyzeFile(uploadedFile, formatInput.value);
    });

    document.getElementById('back-to-upload').addEventListener('click', function() {
        showStep('upload');
    });

    document.getElementById('start-import').addEventListener('click', function() {
        const mapping = collectMapping();
        startImport(mapping);
    });

    function analyzeFile(file, format) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('format', format);
        formData.append('_token', document.querySelector('input[name="_token"]').value);

        fetch('{{ route("records.analyze-file") }}', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    fileHeaders = data.headers;
                    window.__filePreview = data.preview || [];
                    createMappingInterface(data.headers);
                    showStep('mapping');
                } else {
                    alert('Erreur lors de l\'analyse du fichier: ' + data.message);
                }
            })
            .catch(() => alert('Erreur lors de l\'analyse du fichier'));
    }

    function createMappingInterface(headers) {
        const container = document.getElementById('mapping-container');
        const dbFields = {
            'code': { label: 'Code', required: false, description: "Identifiant unique (généré automatiquement si vide)" },
            'name': { label: 'Nom/Titre', required: true, description: 'Nom ou titre' },
            'date_format': { label: 'Format de date', required: false, description: 'Format de la date (Y/M/D)' },
            'start_date': { label: 'Date de début', required: false, description: 'YYYY-MM-DD' },
            'end_date': { label: 'Date de fin', required: false, description: 'YYYY-MM-DD' },
            'exact_date': { label: 'Date exacte', required: false, description: 'YYYY-MM-DD' },
            'level': { label: 'Niveau hiérarchique', required: true, description: 'Niveau' },
            'width': { label: 'Largeur', required: false, description: 'Largeur' },
            'width_description': { label: 'Description largeur', required: false, description: 'Description' },
            'content': { label: 'Contenu', required: false, description: 'Description du contenu' },
            'status': { label: 'Statut', required: true, description: 'Statut' },
            'support': { label: 'Support', required: true, description: 'Type de support' },
            'activity': { label: 'Activité', required: true, description: 'Activité associée' },
            'authors': { label: 'Auteurs', required: false, description: 'Auteurs séparés par des virgules' },
            'terms': { label: 'Termes', required: false, description: 'Termes séparés par des virgules' }
        };

        let html = '';
        const preview = window.__filePreview || [];
        if (Array.isArray(preview) && preview.length) {
            html += '<div class="mb-3">';
            html += '<h6 class="text-muted">Aperçu des données</h6>';
            html += '<div class="table-responsive">';
            html += '<table class="table table-sm table-striped">';
            html += '<thead><tr>';
            headers.forEach(h => { html += `<th>${h}</th>`; });
            html += '</tr></thead><tbody>';
            preview.forEach(row => {
                html += '<tr>';
                headers.forEach((_, idx) => {
                    const cell = ((row[idx] ?? '') + '').substring(0, 80);
                    html += `<td title="${cell}">${cell}</td>`;
                });
                html += '</tr>';
            });
            html += '</tbody></table></div></div>';
        }
        html += '<div class="table-responsive"><table class="table table-bordered">';
        html += '<thead><tr>';
        html += '<th>Champ de la base de données</th>';
        html += '<th>Requis</th>';
        html += '<th>Colonne du fichier</th>';
        html += '<th>Aperçu</th>';
        html += '</tr></thead><tbody>';

        Object.entries(dbFields).forEach(([field, config]) => {
            html += '<tr>';
            html += `<td><strong>${config.label}</strong><br><small class="text-muted">${config.description}</small></td>`;
            html += `<td>${config.required ? '<span class="badge bg-danger">Requis<\/span>' : '<span class="badge bg-secondary">Optionnel<\/span>'}</td>`;
            html += '<td>';
            html += `<select class="form-select mapping-select" data-field="${field}" ${config.required ? 'required' : ''}>`;
            html += '<option value="">-- Ignorer --</option>';
            headers.forEach((header, index) => {
                const selected = header.toLowerCase().includes(field.toLowerCase()) || field.toLowerCase().includes(header.toLowerCase()) ? 'selected' : '';
                html += `<option value="${index}" ${selected}>${header}</option>`;
            });
            html += '</select>';
            html += '</td>';
            html += '<td><span class="preview-text" data-field="' + field + '">--</span></td>';
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        container.innerHTML = html;

        document.querySelectorAll('.mapping-select').forEach(select => {
            select.addEventListener('change', updatePreview);
        });
    }

    function updatePreview() {
        document.querySelectorAll('.preview-text').forEach(span => {
            const field = span.dataset.field;
            const select = document.querySelector(`select[data-field="${field}"]`);
            if (select.value) {
                const firstRow = (window.__filePreview || [])[0] || [];
                const idx = parseInt(select.value, 10);
                const val = firstRow[idx];
                span.textContent = (val !== undefined && val !== null) ? (val + '').substring(0, 80) : '—';
            } else {
                span.textContent = '--';
            }
        });
    }

    function collectMapping() {
        const mapping = {};
        document.querySelectorAll('.mapping-select').forEach(select => {
            const field = select.dataset.field;
            const columnIndex = select.value;
            if (columnIndex !== '') {
                mapping[field] = { column_index: parseInt(columnIndex), column_name: fileHeaders[parseInt(columnIndex)] };
            }
        });
        return mapping;
    }

    function startImport(mapping) {
        showStep('import');
        const formData = new FormData();
        formData.append('file', uploadedFile);
        formData.append('format', document.getElementById('format').value);
        formData.append('mapping', JSON.stringify(mapping));
        formData.append('has_headers', document.getElementById('has_headers').checked ? '1' : '0');
        formData.append('update_existing', document.getElementById('update_existing').checked ? '1' : '0');
        formData.append('auto_generate_codes', document.getElementById('auto_generate_codes').checked ? '1' : '0');
        formData.append('_token', document.querySelector('input[name="_token"]').value);

        fetch('{{ route("records.import") }}', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let statusHtml = '<div class="alert alert-success">' + data.message + '</div>';
                    
                    if (data.summary) {
                        statusHtml += '<div class="mt-3"><h5>Détails de l\'import :</h5>';
                        statusHtml += '<ul class="list-group list-group-flush">';
                        statusHtml += '<li class="list-group-item d-flex justify-content-between align-items-center">';
                        statusHtml += 'Enregistrements importés <span class="badge bg-success rounded-pill">' + data.summary.imported + '</span>';
                        statusHtml += '</li>';
                        if (data.summary.auto_generated_codes > 0) {
                            statusHtml += '<li class="list-group-item d-flex justify-content-between align-items-center">';
                            statusHtml += 'Codes générés automatiquement <span class="badge bg-info rounded-pill">' + data.summary.auto_generated_codes + '</span>';
                            statusHtml += '</li>';
                        }
                        if (data.summary.skipped > 0) {
                            statusHtml += '<li class="list-group-item d-flex justify-content-between align-items-center">';
                            statusHtml += 'Lignes ignorées <span class="badge bg-warning rounded-pill">' + data.summary.skipped + '</span>';
                            statusHtml += '</li>';
                        }
                        if (data.summary.errors > 0) {
                            statusHtml += '<li class="list-group-item d-flex justify-content-between align-items-center">';
                            statusHtml += 'Erreurs <span class="badge bg-danger rounded-pill">' + data.summary.errors + '</span>';
                            statusHtml += '</li>';
                        }
                        statusHtml += '</ul></div>';
                        
                        if (data.summary.skipped > 0 && data.summary.skipped_rows && data.summary.skipped_rows.length > 0) {
                            statusHtml += '<div class="mt-3"><h6>Lignes ignorées (premières 5) :</h6>';
                            statusHtml += '<div class="table-responsive"><table class="table table-sm table-bordered">';
                            statusHtml += '<thead><tr><th>Ligne</th><th>Champs manquants</th><th>Données</th></tr></thead><tbody>';
                            
                            data.summary.skipped_rows.slice(0, 5).forEach(row => {
                                statusHtml += '<tr>';
                                statusHtml += '<td>' + row.row + '</td>';
                                statusHtml += '<td><span class="badge bg-warning">' + row.missing_fields.join(', ') + '</span></td>';
                                statusHtml += '<td><small>' + JSON.stringify(row.data).substring(0, 100) + '...</small></td>';
                                statusHtml += '</tr>';
                            });
                            
                            if (data.summary.skipped_rows.length > 5) {
                                statusHtml += '<tr><td colspan="3" class="text-center text-muted">... et ' + (data.summary.skipped_rows.length - 5) + ' autres lignes</td></tr>';
                            }
                            
                            statusHtml += '</tbody></table></div></div>';
                        }
                    }
                    
                    document.getElementById('import-status').innerHTML = statusHtml;
                    document.getElementById('import-progress').style.width = '100%';
                    
                    if (data.summary && data.summary.imported > 0) {
                        setTimeout(() => { window.location.href = '{{ route("records.index") }}'; }, 5000);
                    }
                } else {
                    document.getElementById('import-status').innerHTML = '<div class="alert alert-danger">Erreur: ' + data.message + '</div>';
                }
            })
            .catch(() => {
                document.getElementById('import-status').innerHTML = '<div class="alert alert-danger">Erreur lors de l\'import</div>';
            });
    }

    function showStep(step) {
        document.querySelectorAll('.import-step').forEach(el => el.style.display = 'none');
        document.getElementById(step + '-step').style.display = 'block';
    }
});
</script>
@endpush
