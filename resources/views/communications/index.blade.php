@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Fiches de communication {{ $title ?? ''}}</h1>
        <a href="{{ route('transactions.create') }}" class="btn btn-primary mb-3">
            <i class="bi bi-plus-circle"></i> Remplir une fiche
        </a>

        <div class="d-flex justify-content-between align-items-center bg-light p-3 mb-3">
            <div class="d-flex align-items-center">
                <a href="#" id="cartBtn" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-cart me-1"></i>
                    Chariot
                </a>
                <a href="#" id="exportBtn" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-download me-1"></i>
                    Exporter
                </a>
                <a href="#" id="printBtn" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-printer me-1"></i>
                    Imprimer
                </a>
            </div>
            <div class="d-flex align-items-center">
                <a href="#" id="checkAllBtn" class="btn btn-light btn-sm">
                    <i class="bi bi-check-square me-1"></i>
                    Tout cocher
                </a>
            </div>
        </div>
        <div class="row">
            @foreach ($communications as $communication)

            <div class="col-12 ml-3 mb-1">
                <h5 class="card-title">
                    <input class="form-check-input" type="checkbox" value="{{$communication->id}}" id="communication_id" />
                    <label class="form-check-label" for="">
                        <span style="font-size: 1.4em; font-weight: bold;">
                            <a href="{{ route('transactions.show', $communication->id) }}">
                                <strong> {{ $communication->code ?? 'N/A' }} : {{ $communication->name ?? 'N/A' }}</strong>
                            </a>
                        </span>
                    </label>
                </h5>
            </div>
            <div class="col-12 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="card-text">
                                        <strong>Contenu :</strong> {{ $communication->content ?? 'N/A' }}<br>
                                    </p>
                                </div>
                                <div class="card-text d-flex flex-wrap">
                                    <div class="mr-3">
                                        <strong>Demandeur :</strong>
                                        <span>
                                            <a href="{{ route('communications-sort')}}?user={{ $communication->user->id }}">
                                                {{ $communication->user->name ?? 'N/A' }}
                                            </a>

                                            (<a href="{{ route('communications-sort')}}?user_organisation={{ $communication->userOrganisation->id??'' }}">
                                                    {{ $communication->userOrganisation->name ?? 'N/A' }}
                                            </a>)</span>
                                    </div>
                                </div>

                                <div class="card-text d-flex flex-wrap">
                                    <div class="mr-3">
                                        <strong>Opérateur :</strong>
                                        <span>

                                            <a href="{{ route('communications-sort')}}?operator={{ $communication->operator->id }}">
                                                {{ $communication->operator->name ?? 'N/A' }}
                                            </a>

                                            (<a href="{{ route('communications-sort')}}?operator_organisation={{ $communication->operatorOrganisation->id ??'' }}">
                                                {{ $communication->operatorOrganisation->name ?? 'N/A' }}
                                            </a>
                                            )</span>
                                    </div>
                                </div>

                                <div class="card-text d-flex flex-wrap">
                                    <div class="mr-3">
                                        <strong>Date de retour :</strong> {{ $communication->return_date ?? 'N/A' }}
                                    </div>
                                    <div class="mr-3">
                                        <strong>Date de retour effectif :</strong> {{ $communication->return_effective ?? 'N/A' }}
                                    </div>
                                    <div>
                                        <strong>Statut :</strong>
                                        <a href="{{ route('communications-sort')}}?status={{ $communication->status->id }}">
                                            {{ $communication->status->name ?? 'N/A' }}
                                        </a>

                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <footer class="bg-light py-3">
        <div class="container">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <li class="page-item {{ $communications->currentPage() == 1 ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $communications->previousPageUrl() }}" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    @for ($i = 1; $i <= $communications->lastPage(); $i++)
                        <li class="page-item {{ $communications->currentPage() == $i ? 'active' : '' }}">
                            <a class="page-link" href="{{ $communications->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor
                    <li class="page-item {{ $communications->currentPage() == $communications->lastPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $communications->nextPageUrl() }}" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </footer>
@endsection
@push('scripts')
    <script>
        document.getElementById('cartBtn').addEventListener('click', function(e) {
            e.preventDefault();
            let checkedCommunications = Array.from(document.querySelectorAll('input[type="checkbox"]:checked'))
                .map(checkbox => checkbox.value);

            if (checkedCommunications.length === 0) {
                alert('Veuillez sélectionner au moins une communication à ajouter au chariot.');
                return;
            }

            fetch('{{ route('communications.addToCart') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ communications: checkedCommunications })
            })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    // Optionnel : rediriger vers la page du chariot
                    {{--// window.location.href = '{{ route('cart.show') }}';--}}
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Une erreur est survenue lors de l\'ajout au chariot.');
                });
        });
        document.getElementById('exportBtn').addEventListener('click', function(e) {
            e.preventDefault();
            let checkedCommunications = Array.from(document.querySelectorAll('input[type="checkbox"]:checked'))
                .map(checkbox => checkbox.value);

            if (checkedCommunications.length === 0) {
                alert('Veuillez sélectionner au moins une communication à exporter.');
                return;
            }

            window.location.href = `{{ route('communications.export') }}?communications=${checkedCommunications.join(',')}`;
        });

        document.getElementById('printBtn').addEventListener('click', function(e) {
            e.preventDefault();
            let checkedCommunications = Array.from(document.querySelectorAll('input[type="checkbox"]:checked'))
                .map(checkbox => checkbox.value);

            if (checkedCommunications.length === 0) {
                alert('Veuillez sélectionner au moins une communication à imprimer.');
                return;
            }

            // Rediriger vers la route d'impression avec les IDs des communications sélectionnées
            window.location.href = `{{ route('communications.print') }}?communications=${checkedCommunications.join(',')}`;
        });
        document.getElementById('checkAllBtn').addEventListener('click', function(e) {
            e.preventDefault();
            let checkboxes = document.querySelectorAll('input[type="checkbox"]');
            let allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);

            checkboxes.forEach(function(checkbox) {
                checkbox.checked = !allChecked;
            });

            this.innerHTML = allChecked ? '<i class="bi bi-check-square me-1"></i>Tout cocher' : '<i class="bi bi-square me-1"></i>Tout décocher';
        });
    </script>
@endpush
