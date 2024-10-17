@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="">
            <div class="">
                <div class="">
                    <h3 class="mb-0">Générateur de Codes-Barres</h3>
                </div>


                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif



                <div class="card-body">
                    <form id="barcodeForm" method="POST" action="{{ route('barcode.generate') }}" class="row g-3">
                        @csrf
                        <div class="col-md-6">
                            <label for="debut" class="form-label">Début de la séquence</label>
                            <input type="number" name="debut" id="debut" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="nombre" class="form-label">Nombre de codes-barres</label>
                            <input type="number" name="nombre" id="nombre" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="prefixe" class="form-label">Préfixe</label>
                            <input type="text" name="prefixe" id="prefixe" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="suffixe" class="form-label">Suffixe</label>
                            <input type="text" name="suffixe" id="suffixe" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="barcodes_per_line" class="form-label">Codes-barres par ligne</label>
                            <input type="number" name="barcodes_per_line" id="barcodes_per_line" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="barcodes_per_column" class="form-label">Codes-barres par colonne</label>
                            <input type="number" name="barcodes_per_column" id="barcodes_per_column" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Marges (en mm)</label>
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <span class="input-group-text">Gauche</span>
                                        <input type="number" name="margin_left" id="margin_left" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <span class="input-group-text">Haut</span>
                                        <input type="number" name="margin_top" id="margin_top" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <span class="input-group-text">Droite</span>
                                        <input type="number" name="margin_right" id="margin_right" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <span class="input-group-text">Bas</span>
                                        <input type="number" name="margin_bottom" id="margin_bottom" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary">Générer les codes-barres</button>
                        </div>
                    </form>
                    <div id="downloadLink" class="mt-3" style="display: none;">
                        <a href="#" class="btn btn-success" download>Télécharger le PDF</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('barcodeForm');
        const downloadLinkContainer = document.getElementById('downloadLink');
        const downloadLink = downloadLinkContainer.querySelector('a');

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.pdf_file) {
                    downloadLink.href = data.pdf_file;
                    downloadLinkContainer.style.display = 'block';

                    // Déclencher automatiquement le téléchargement
                    const tempLink = document.createElement('a');
                    tempLink.href = data.pdf_file;
                    tempLink.setAttribute('download', 'barcodes.pdf');
                    tempLink.click();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Une erreur est survenue lors de la génération du PDF.');
            });
        });
    });
    </script>
@endpush
