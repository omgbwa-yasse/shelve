@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1 class="mb-4"><i class="bi bi-envelope-fill me-2"></i>{{ __('mails') }}
            @if(isset($title))
                {{ $title }}
            @endif
        </h1>
        <button id="showPopupBtn" class="showPopupBtn">Afficher Popup</button>
        <div id="mailList">
            <div class="d-flex justify-content-between align-items-center bg-light p-3 mb-3">
                <div class="d-flex align-items-center">
                    <a href="#" id="cartBtn" class="btn btn-light btn-sm me-2">
                        <i class="bi bi-cart me-1"></i>
                        {{ __('cart') }}
                    </a>
                    <a href="#" id="exportBtn" class="btn btn-light btn-sm me-2">
                        <i class="bi bi-download me-1"></i>
                        {{ __('export') }}
                    </a>
                    <a href="#" id="printBtn" class="btn btn-light btn-sm me-2">
                        <i class="bi bi-printer me-1"></i>
                        {{ __('print') }}
                    </a>
                </div>
                <div class="d-flex align-items-center">
                    <a href="#" id="checkAllBtn" class="btn btn-light btn-sm">
                        <i class="bi bi-check-square me-1"></i>
                        {{ __('check_all') }}
                    </a>
                </div>
            </div>

            <div id="mailList" class="mb-4">
                @foreach ($mails as $mail)
                    <div class="mb-3" style="transition: all 0.3s ease; transform: translateZ(0);">
                        <div class="card-header bg-light d-flex align-items-center py-2" style="border-bottom: 1px solid rgba(0,0,0,0.125);">
                            <div class="form-check me-3">
                                <input class="form-check-input" type="checkbox" value="{{ $mail->id }}" id="mail-{{ $mail->id }}" name="selected_mail[]" />
                            </div>
                            <button class="btn btn-link btn-sm text-secondary text-decoration-none p-0 me-3" type="button" data-bs-toggle="collapse" data-bs-target="#details-{{ $mail->id }}" aria-expanded="false" aria-controls="details-{{ $mail->id }}">
                                <i class="bi bi-chevron-down fs-5"></i>
                            </button>
                            <h4 class="card-title flex-grow-1 m-0 text-primary" for="mail-{{ $mail->id }}">
                                <a href="{{ route('mails.show', $mail) }}" class="text-decoration-none text-dark">
                                    <span class="fs-5 fw-semibold">{{ $mail->code }}</span>
                                    <span class="fs-5"> - {{ $mail->name }}</span>
                                    <span class="badge bg-{{ $mail->priority->color ?? 'secondary' }} ms-2">{{ $mail->priority->name ?? '' }}</span>
                                </a>
                            </h4>
                        </div>
                        <div class="collapse" id="details-{{ $mail->id }}">
                            <div class="card-body bg-white">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <p class="mb-2"><i class="bi bi-card-text me-2 text-primary"></i><strong>{{ __('description') }}:</strong> {{ $mail->description }}</p>
                                    </div>
                                    <div class="col-md-9">
                                        <p class="mb-2">
                                            <i class="bi bi-person-fill me-2 text-primary"></i><strong>{{ __('author') }}:</strong>
                                            @foreach ($mail->authors as $index => $author)
                                                <a href="{{ route('mails.sort') }}?categ=author&value={{ $author->id }}">{{ $author->name }}</a>@if(!$loop->last),@endif
                                            @endforeach
                                            <br>
                                             <i class="bi bi-calendar-event me-2 text-primary"></i><strong>{{ __('date') }}:</strong> <a href="{{ route('mails.sort') }}?categ=dates&date_exact={{ $mail->date }}">{{ $mail->date }}</a>

                                            <i class="bi bi-exclamation-triangle-fill me-2 text-primary"></i><strong>{{ __('priority') }}:</strong> <a href="{{ route('mails.sort') }}?categ=priority&id={{ $mail->priority->id }}">{{ $mail->priority->name ?? '' }}</a>

                                            <i class="bi bi-envelope-fill me-2 text-primary"></i><strong>{{ __('mail_type') }}:</strong> <a href="{{ route('mails.sort') }}?categ=priority&id={{ $mail->type->id }}">{{ $mail->type->name ?? '' }}</a>

                                            <i class="bi bi-diagram-3-fill me-2 text-primary"></i><strong>{{ __('typology') }}:</strong> <a href="{{ route('mails.sort') }}?categ=typology&id={{ $mail->typology->id }}">{{ $mail->typology->name ?? '' }}</a>
                                            <br>
                                            <i class="bi bi-file-earmark-text-fill me-2 text-primary"></i><strong>{{ __('copy') }}:</strong> <a href="{{ route('mails.sort') }}?categ=documentType&id={{ $mail->documentType->id }}">{{ $mail->documentType->name ?? '' }}</a>
                                        </p>
                                    </div>
                                    <div class="col-md-3 text-md-end">
                                        @if($mail->attachments->count() > 0)
                                            <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#attachmentsModal{{ $mail->id }}">
                                                {{ $mail->attachments->count() }} <i class="bi bi-paperclip"></i>
                                            </button>
                                        @else
                                            <span class="text-muted"><i class="bi bi-paperclip"></i> 0</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Pagination -->
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item {{ $mails->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $mails->previousPageUrl() }}" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                @foreach ($mails->getUrlRange(1, $mails->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $mails->currentPage() ? 'active' : '' }}">
                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                    </li>
                @endforeach
                <li class="page-item {{ $mails->hasMorePages() ? '' : 'disabled' }}">
                    <a class="page-link" href="{{ $mails->nextPageUrl() }}" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Attachments Modals -->
        @foreach ($mails as $mail)
            @if($mail->attachments->count() > 0)
                <div class="modal fade" id="attachmentsModal{{ $mail->id }}" tabindex="-1" aria-labelledby="attachmentsModalLabel{{ $mail->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="attachmentsModalLabel{{ $mail->id }}">
                                    <i class="bi bi-paperclip me-2"></i>{{ __('attachments') }} - {{ $mail->code }}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <ul class="list-group">
                                    @foreach($mail->attachments as $attachment)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <i class="bi bi-file-earmark me-2"></i>{{ $attachment->name }}
                                            <a href="{{ route('mail-attachment.show', [$mail->id, $attachment->id]) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cartModalLabel">{{ __('add_to_cart') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('add_to_cart') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="confirmCart">{{ __('confirm') }}</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for Print -->
    <div class="modal fade" id="printModal" tabindex="-1" aria-labelledby="printModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="printModalLabel">{{ __('print_records') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('print_records') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="confirmPrint">{{ __('print') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection



@push('scripts')
    <script>
        document.getElementById('cartBtn').addEventListener('click', function(e) {
            e.preventDefault();
            let checkedRecords = Array.from(document.querySelectorAll('input[type="checkbox"]:checked'))
                .map(checkbox => checkbox.value);

            if (checkedRecords.length === 0) {
                alert('Veuillez sélectionner au moins un enregistrement.');
                return;
            }

            var cartModal = new bootstrap.Modal(document.getElementById('cartModal'));
            cartModal.show();
        });

        document.getElementById('confirmCart').addEventListener('click', function() {
            let checkedRecords = Array.from(document.querySelectorAll('input[type="checkbox"]:checked'))
                .map(checkbox => checkbox.value);

            fetch('{{ route("dolly.createWithRecords") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ records: checkedRecords })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Un nouveau chariot a été créé avec les enregistrements sélectionnés.');
                    } else {
                        alert('Une erreur est survenue lors de la création du chariot.');
                    }
                });

            var cartModal = bootstrap.Modal.getInstance(document.getElementById('cartModal'));
            cartModal.hide();
        });

        document.getElementById('printBtn').addEventListener('click', function(e) {
            e.preventDefault();
            let checkedRecords = Array.from(document.querySelectorAll('input[type="checkbox"]:checked'))
                .map(checkbox => checkbox.value);

            if (checkedRecords.length === 0) {
                alert('Veuillez sélectionner au moins un enregistrement à imprimer.');
                return;
            }

            var printModal = new bootstrap.Modal(document.getElementById('printModal'));
            printModal.show();
        });

        document.getElementById('confirmPrint').addEventListener('click', function() {
            let checkedRecords = Array.from(document.querySelectorAll('input[type="checkbox"]:checked'))
                .map(checkbox => checkbox.value);

            fetch('{{ route("records.print") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ records: checkedRecords })
            })
                .then(response => response.blob())
                .then(blob => {
                    let url = window.URL.createObjectURL(blob);
                    let a = document.createElement('a');
                    a.href = url;
                    a.download = 'records_print.pdf';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                });

            var printModal = bootstrap.Modal.getInstance(document.getElementById('printModal'));
            printModal.hide();
        });

        function confirmDelete(mailId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce courrier ?')) {
                document.getElementById('delete-form-' + mailId).submit();
            }
        }
        let checkAllBtn = document.getElementById('checkAllBtn');
        checkAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            let checkboxes = document.querySelectorAll('input[name="selected_mail[]"]');
            let allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);

            checkboxes.forEach(function(checkbox) {
                checkbox.checked = !allChecked;
            });

            this.innerHTML = allChecked ?
                '<i class="bi bi-check-square me-1"></i>Tout cocher' :
                '<i class="bi bi-square me-1"></i>Tout décocher';
        });

        document.getElementById('searchInput').addEventListener('keyup', function() {
            var input, filter, cards, card, i, txtValue;
            input = document.getElementById('searchInput');
            filter = input.value.toUpperCase();
            cards = document.getElementById('mailList').getElementsByClassName('card');

            for (i = 0; i < cards.length; i++) {
                card = cards[i];
                txtValue = card.textContent || card.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    card.style.display = "";
                } else {
                    card.style.display = "none";
                }
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const collapseElements = document.querySelectorAll('.collapse');
            collapseElements.forEach(collapse => {
                collapse.addEventListener('show.bs.collapse', function () {
                    const button = document.querySelector(`[data-bs-target="#${this.id}"]`);
                    button.querySelector('i').classList.replace('bi-chevron-down', 'bi-chevron-up');
                });
                collapse.addEventListener('hide.bs.collapse', function () {
                    const button = document.querySelector(`[data-bs-target="#${this.id}"]`);
                    button.querySelector('i').classList.replace('bi-chevron-up', 'bi-chevron-down');
                });
            });

            // Gestion du "voir plus / voir moins" pour le contenu
            document.querySelectorAll('.content-toggle').forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('data-target');
                    const targetElement = document.getElementById(targetId);
                    const fullText = this.getAttribute('data-full-text');

                    if (this.textContent === 'Voir plus') {
                        targetElement.textContent = fullText;
                        this.textContent = 'Voir moins';
                    } else {
                        targetElement.textContent = fullText.substr(0, 200) + '...';
                        this.textContent = 'Voir plus';
                    }
                });
            });
        });
    </script>
@endpush
