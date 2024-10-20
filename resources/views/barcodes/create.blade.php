@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Générateur de Codes-Barres Professionnel</h3>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form id="barcodeForm" method="POST" action="{{ route('barcode.generate') }}">
                            @csrf
                            <div class="row g-3">
                                <!-- Les champs du formulaire restent les mêmes -->
                                <!-- ... -->
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary w-100">Générer les codes-barres</button>
                                </div>
                            </div>
                        </form>
                        <div id="generationStatus" class="mt-3 alert alert-info" style="display: none;">
                            Génération en cours... <span id="progressPercentage">0%</span>
                            <div class="progress mt-2">
                                <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <div id="downloadLink" class="mt-3 text-center" style="display: none;">
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
            const submitButton = form.querySelector('button[type="submit"]');
            const generationStatus = document.getElementById('generationStatus');
            const progressBar = document.getElementById('progressBar');
            const progressPercentage = document.getElementById('progressPercentage');

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                submitButton.disabled = true;
                submitButton.innerHTML = 'Génération en cours...';
                generationStatus.style.display = 'block';
                downloadLinkContainer.style.display = 'none';

                const formData = new FormData(form);

                // Simuler une progression pour donner un retour visuel à l'utilisateur
                let progress = 0;
                const progressInterval = setInterval(() => {
                    progress += 5;
                    if (progress > 90) {
                        clearInterval(progressInterval);
                    }
                    progressBar.style.width = `${progress}%`;
                    progressBar.setAttribute('aria-valuenow', progress);
                    progressPercentage.textContent = `${progress}%`;
                }, 500);

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erreur réseau ou serveur');
                        }
                        return response.json();
                    })
                    .then(data => {
                        clearInterval(progressInterval);
                        progressBar.style.width = '100%';
                        progressBar.setAttribute('aria-valuenow', 100);
                        progressPercentage.textContent = '100%';

                        if (data.pdf_file) {
                            setTimeout(() => {
                                generationStatus.style.display = 'none';
                                downloadLink.href = data.pdf_file;
                                downloadLinkContainer.style.display = 'block';

                                // Vérifier si le PDF est accessible avant de le télécharger
                                fetch(data.pdf_file, { method: 'HEAD' })
                                    .then(response => {
                                        if (response.ok) {
                                            const tempLink = document.createElement('a');
                                            tempLink.href = data.pdf_file;
                                            tempLink.setAttribute('download', 'barcodes.pdf');
                                            tempLink.click();
                                        } else {
                                            throw new Error('Le PDF généré n\'est pas accessible');
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Erreur lors de la vérification du PDF:', error);
                                        alert('Le PDF généré n\'est pas accessible. Veuillez réessayer ou contacter le support.');
                                    });
                            }, 1000);
                        } else {
                            throw new Error('Aucun fichier PDF n\'a été généré');
                        }
                    })
                    .catch(error => {
                        clearInterval(progressInterval);
                        console.error('Error:', error);
                        alert('Une erreur est survenue lors de la génération du PDF. Veuillez réessayer ou contacter le support.');
                        generationStatus.style.display = 'none';
                    })
                    .finally(() => {
                        submitButton.disabled = false;
                        submitButton.innerHTML = 'Générer les codes-barres';
                    });
            });
        });
    </script>
@endpush
