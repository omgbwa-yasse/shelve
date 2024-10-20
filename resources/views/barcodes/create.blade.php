@extends('layouts.app')

@section('content')
    <div class="container-fluid py-5 bg-light">
        <div class="row justify-content-center">
            <div class="col-lg-10">
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
                                    </div>
                                    <div class="mb-3">
                                        <label for="count" class="form-label">Nombre de codes-barres</label>
                                        <input type="number" class="form-control" id="count" name="count" value="50" required min="1" max="1000">
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
                                        <label for="per_page" class="form-label">Codes-barres par page</label>
                                        <input type="number" class="form-control" id="per_page" name="per_page" value="20" required min="1" max="100">
                                    </div>
                                    <div class="mb-4">
                                        <label for="page_size" class="form-label">Format de page</label>
                                        <select class="form-select" id="page_size" name="page_size" required>
                                            <option value="A4">A4</option>
                                            <option value="Letter">Letter</option>
                                        </select>
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
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('barcodeForm');
            const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
            const previewContainer = document.getElementById('previewContainer');
            const previewContent = document.getElementById('previewContent');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            form.addEventListener('input', debounce(updatePreview, 300));

            function updatePreview() {
                const formData = new FormData(form);
                formData.set('count', Math.min(10, formData.get('count'))); // Limiter à 10 pour la prévisualisation

                fetch('{{ route('barcode.preview') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                    .then(response => response.ok ? response.json() : Promise.reject(response))
                    .then(data => {
                        previewContent.innerHTML = data.html || 'Erreur de prévisualisation';
                    })
                    .catch(error => {
                        console.error('Erreur de prévisualisation:', error);
                        previewContent.innerHTML = 'Erreur lors de la génération de la prévisualisation.';
                    });
            }

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                loadingModal.show();

                fetch('{{ route('barcode.generate') }}', {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                    .then(response => response.ok ? response.blob() : Promise.reject(response))
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
                        alert('Une erreur est survenue lors de la génération du PDF.');
                    });
            });

            function debounce(func, wait) {
                let timeout;
                return function(...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), wait);
                };
            }
        });
    </script>
@endpush
