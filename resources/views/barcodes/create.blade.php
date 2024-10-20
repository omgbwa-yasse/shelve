@extends('layouts.app')

@section('content')
    <div class="container-fluid bg-light">
        <div class="row justify-content-center">
            <div class="">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="row g-0">
                            <div class="col-md-5 p-4">
                                <h2 class="mb-4 fw-bold text-primary">Générateur de Codes-Barres</h2>
                                <form id="barcodeForm" class="needs-validation" novalidate>
                                    @csrf
                                    <div class="mb-3">
                                        <label for="start" class="form-label">Numéro de début</label>
                                        <input type="number" class="form-control" id="start" name="start" value="1" required min="0">
                                        <div class="invalid-feedback">Veuillez entrer un numéro de début valide.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="count" class="form-label">Nombre de codes-barres</label>
                                        <input type="number" class="form-control" id="count" name="count" value="50" required min="1" max="1000">
                                        <div class="invalid-feedback">Veuillez entrer un nombre entre 1 et 1000.</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="prefix" class="form-label">Préfixe</label>
                                            <input type="text" class="form-control" id="prefix" name="prefix" maxlength="10" placeholder="Optionnel">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="suffix" class="form-label">Suffixe</label>
                                            <input type="text" class="form-control" id="suffix" name="suffix" maxlength="10" placeholder="Optionnel">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="per_page" class="form-label">Codes-barres par page (souhaité)</label>
                                        <input type="number" class="form-control" id="per_page" name="per_page" value="20" required min="1" max="100">
                                        <div id="layoutInfo" class="form-text"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="page_size" class="form-label">Format de page</label>
                                        <select class="form-select" id="page_size" name="page_size" required>
                                            <option value="A4">A4</option>
                                            <option value="Letter">Letter</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="barcode_type" class="form-label">Type de code-barres</label>
                                        <select class="form-select" id="barcode_type" name="barcode_type" required>
                                            <option value="C128">Code 128</option>
                                            <option value="C39">Code 39</option>
                                            <option value="EAN13">EAN-13</option>
                                            <option value="UPC">UPC</option>
                                            <option value="I25">Interleaved 2 of 5</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="barcode_width" class="form-label">Largeur du code-barres</label>
                                        <input type="number" class="form-control" id="barcode_width" name="barcode_width" value="2" required min="1" max="5" step="0.1">
                                        <div class="invalid-feedback">Veuillez entrer une largeur entre 1 et 5.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="barcode_height" class="form-label">Hauteur du code-barres</label>
                                        <input type="number" class="form-control" id="barcode_height" name="barcode_height" value="30" required min="10" max="100">
                                        <div class="invalid-feedback">Veuillez entrer une hauteur entre 10 et 100.</div>
                                    </div>
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="show_text" name="show_text" checked>
                                        <label class="form-check-label" for="show_text">Afficher le texte sous le code-barres</label>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 py-2">Générer les codes-barres</button>
                                </form>
                            </div>
                            <div class="col-md-7 bg-white">
                                <div class="h-100 d-flex flex-column">
                                    <div class="p-4 border-bottom">
                                        <h4 class="mb-0 fw-bold">Prévisualisation</h4>
                                    </div>
                                    <div id="previewContainer" class="flex-grow-1 overflow-auto p-4">
                                        <div id="previewContent" class="text-center">
                                            <p class="text-muted">La prévisualisation apparaîtra ici</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 bg-transparent">
                <div class="modal-body text-center text-white">
                    <div class="spinner-border text-light" role="status"></div>
                    <p class="mt-2">Génération des codes-barres en cours, veuillez patienter...</p>
                </div>
            </div>
        </div>
    </div>
    <div id="layoutInfo" class="mt-2 text-muted"></div>
@endsection

@push('styles')
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-control, .form-select {
            border-radius: 0;
        }
        .btn-primary {
            border-radius: 0;
        }
        #previewContainer {
            max-height: 500px;
        }
        .barcode-image svg {
            max-width: 100%;
            height: auto;
        }
        .error-message {
            color: #dc3545;
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
@endpush

@push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let currentLayout = {};
                const form = document.getElementById('barcodeForm');
                const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
                const previewContainer = document.getElementById('previewContainer');
                const previewContent = document.getElementById('previewContent');
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const barcodeTypeSelect = document.getElementById('barcode_type');
                const startInput = document.getElementById('start');
                const countInput = document.getElementById('count');
                const perPageInput = document.getElementById('per_page');
                const barcodeWidthInput = document.getElementById('barcode_width');
                const barcodeHeightInput = document.getElementById('barcode_height');
                const pageSizeSelect = document.getElementById('page_size');
                const layoutInfoDiv = document.getElementById('layoutInfo');

                function updateFieldsBasedOnBarcodeType() {
                    const selectedType = barcodeTypeSelect.value;
                    if (selectedType === 'UPC' || selectedType === 'EAN13') {
                        startInput.min = 100000000000;
                        startInput.max = 999999999999;
                        countInput.max = 100;
                        if (parseInt(startInput.value) < 100000000000) {
                            startInput.value = 100000000000;
                        }
                        if (parseInt(countInput.value) > 100) {
                            countInput.value = 100;
                        }
                    } else {
                        startInput.min = 0;
                        startInput.removeAttribute('max');
                        countInput.max = 1000;
                    }
                    calculateOptimalLayout();
                }

                function calculateOptimalLayout() {
                    const pageSize = pageSizeSelect.value;
                    const barcodeWidth = parseFloat(barcodeWidthInput.value);
                    const barcodeHeight = parseFloat(barcodeHeightInput.value);
                    const desiredPerPage = parseInt(perPageInput.value);

                    // Dimensions en mm (approximatives)
                    const pageDimensions = {
                        'A4': { width: 210, height: 297 },
                        'Letter': { width: 215.9, height: 279.4 }
                    };

                    const pageWidth = pageDimensions[pageSize].width;
                    const pageHeight = pageDimensions[pageSize].height;

                    // Conversion approximative de px à mm (1 px ≈ 0.26 mm)
                    const barcodeWidthMm = barcodeWidth * 0.26;
                    const barcodeHeightMm = barcodeHeight * 0.26;

                    // Marge et espacement (en mm)
                    const margin = 10;
                    const spacing = 5;

                    // Calcul du nombre maximum de colonnes et de lignes
                    const availableWidth = pageWidth - (2 * margin);
                    const availableHeight = pageHeight - (2 * margin);
                    const maxColumns = Math.floor(availableWidth / (barcodeWidthMm + spacing));
                    const maxRows = Math.floor(availableHeight / (barcodeHeightMm + spacing));

                    // Calcul du nombre optimal de colonnes
                    let optimalColumns = Math.min(5, maxColumns);
                    let optimalRows = Math.ceil(desiredPerPage / optimalColumns);

                    // Ajuster si nécessaire pour ne pas dépasser le nombre maximum de lignes
                    if (optimalRows > maxRows) {
                        optimalRows = maxRows;
                        optimalColumns = Math.min(5, Math.ceil(desiredPerPage / optimalRows));
                    }

                    const actualPerPage = optimalColumns * optimalRows;

                    // Mise à jour du champ "Codes-barres par page"
                    perPageInput.value = actualPerPage;
                    perPageInput.max = maxColumns * maxRows;

                    // Mise à jour des informations de mise en page
                    layoutInfoDiv.textContent = `Mise en page optimale : ${optimalColumns} colonnes x ${optimalRows} lignes = ${actualPerPage} codes-barres par page`;

                    currentLayout = { columns: optimalColumns, rows: optimalRows, perPage: actualPerPage };

                    return currentLayout;
                }

                function validateForm() {
                    let isValid = true;
                    form.classList.add('was-validated');

                    // Validation spécifique pour UPC et EAN13
                    if (barcodeTypeSelect.value === 'UPC' || barcodeTypeSelect.value === 'EAN13') {
                        if (parseInt(startInput.value) < 100000000000 || parseInt(startInput.value) > 999999999999) {
                            startInput.setCustomValidity('Le numéro de début doit être compris entre 100000000000 et 999999999999 pour UPC et EAN13.');
                            isValid = false;
                        } else {
                            startInput.setCustomValidity('');
                        }
                    } else {
                        startInput.setCustomValidity('');
                    }

                    // La validation du nombre de codes-barres par page n'est plus nécessaire ici
                    // car elle est gérée automatiquement par calculateOptimalLayout

                    return isValid;
                }

                function updatePreview() {
                    if (!validateForm()) return;

                    const layout = calculateOptimalLayout();
                    const formData = new FormData(form);
                    formData.set('count', Math.min(10, formData.get('count'))); // Limiter à 10 pour la prévisualisation
                    formData.set('show_text', document.getElementById('show_text').checked ? '1' : '0');
                    formData.set('per_page', layout.perPage);

                    fetch('{{ route('barcode.preview') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                previewContent.innerHTML = `<div class="error-message">${data.error}</div>`;
                            } else if (data.html) {
                                previewContent.innerHTML = data.html;
                            } else {
                                previewContent.innerHTML = 'Erreur de prévisualisation';
                            }
                        })
                        .catch(error => {
                            console.error('Erreur détaillée:', error);
                            previewContent.innerHTML = 'Erreur lors de la génération de la prévisualisation.';
                        });
                }

                barcodeTypeSelect.addEventListener('change', updateFieldsBasedOnBarcodeType);
                [pageSizeSelect, barcodeWidthInput, barcodeHeightInput, perPageInput].forEach(input => {
                    input.addEventListener('input', calculateOptimalLayout);
                    input.addEventListener('input', debounce(updatePreview, 300));
                });

                [startInput, countInput].forEach(input => {
                    input.addEventListener('input', debounce(updatePreview, 300));
                });

                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    if (!validateForm()) return;

                    loadingModal.show();

                    const layout = calculateOptimalLayout();
                    const formData = new FormData(form);
                    formData.set('show_text', document.getElementById('show_text').checked ? '1' : '0');
                    formData.set('per_page', layout.perPage);
                    formData.set('columns', layout.columns);  // Ajout du nombre de colonnes
                    formData.set('rows', layout.rows);        // Ajout du nombre de lignes

                    fetch('{{ route('barcode.generate') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                        .then(response => {
                            if (!response.ok) {
                                return response.json().then(err => { throw err; });
                            }
                            return response.blob();
                        })
                        .then(blob => {
                            loadingModal.hide();
                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.style.display = 'none';
                            a.href = url;
                            a.download = 'barcodes.pdf';
                            document.body.appendChild(a);
                            a.click();
                            window.URL.revokeObjectURL(url);
                        })
                        .catch(error => {
                            loadingModal.hide();
                            console.error('Erreur:', error);
                            if (error.error) {
                                alert(error.error);
                            } else {
                                alert('Une erreur est survenue lors de la génération du PDF.');
                            }
                        });
                });

                function debounce(func, wait) {
                    let timeout;
                    return function(...args) {
                        clearTimeout(timeout);
                        timeout = setTimeout(() => func.apply(this, args), wait);
                    };
                }

                // Initialisation
                updateFieldsBasedOnBarcodeType();
                calculateOptimalLayout();
                updatePreview();
            });
        </script>
@endpush
