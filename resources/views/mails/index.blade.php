@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1 class="mb-4"><i class="bi bi-envelope-fill me-2"></i>Courriers</h1>
        <div id="mailList">
            <div class="d-flex justify-content-between align-items-center bg-light p-3 mb-3">
                <div class="d-flex align-items-center">
                    <a href="#" id="cartBtn" class="btn btn-light btn-sm me-2">
                        <i class="bi bi-cart me-1"></i>
                        Chariot ***
                    </a>
                    <a href="#" id="exportBtn" class="btn btn-light btn-sm me-2">
                        <i class="bi bi-download me-1"></i>
                        Exporter ***
                    </a>
                    <a href="#" id="printBtn" class="btn btn-light btn-sm me-2">
                        <i class="bi bi-printer me-1"></i>
                        Imprimer ***
                    </a>
                </div>
                <div class="d-flex align-items-center">
                    <a href="#" id="transferBtn" class="btn btn-light btn-sm me-2">
                        <i class="bi bi-arrow-repeat me-1"></i>
                        Transférer ***
                    </a>

                    <a href="#" id="communicateBtn" class="btn btn-light btn-sm me-2">
                        <i class="bi bi-envelope me-1"></i>
                        Communiquer ***
                    </a>
                    <a href="#" id="checkAllBtn" class="btn btn-light btn-sm">
                        <i class="bi bi-check-square me-1"></i>
                        Tout cocher ***
                    </a>
                </div>
            </div>



            @foreach ($mails as $mail)
            <h4 class="card-title mb-2">
                <div class="btn-group mt-1" role="group">
                    <input type="checkbox" class="me-2" name="selected_mail[]" value="{{ $mail->id }}" id="mail_{{ $mail->id }}" autocomplete="off" />
                </div>
                <a href="{{ route('mails.show', $mail) }}"><b>{{ $mail->code }} - {{ $mail->name }}</b></a>
                <span class="badge bg-{{ $mail->priority->color ?? 'secondary' }}">
                    {{ $mail->priority->name ?? '' }}
                </span>
            </h4>
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-9">
                                <p class="card-text mb-1">
                                    <i class="bi bi-card-text me-2"></i><em>Description:</em> {{ $mail->description }} <br>
                                    @foreach ($mail->authors as $index => $author)
                                        <i class="bi bi-person-fill me-2"></i><em>Auteur:</em><i>
                                                <a href="{{ route('mails.sort')}}?categ=author&value={{ $author->id }}">{{ $author->name }}</a>
                                            </i>
                                        @if(!$loop->last)
                                            ;
                                        @endif
                                    @endforeach
                                    <i class="bi bi-calendar-event me-2"></i><em>Date:</em><a href="{{ route('mails.sort')}}?categ=dates&value={{ $mail->date }}"> {{ $mail->date }}</a>
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i><em>Priorité :</em> <a href="{{ route('mails.sort')}}?categ=priority&id={{ $mail->priority->id }}">{{ $mail->priority->name ?? '' }}</a>
                                    <i class="bi bi-envelope-fill me-2"></i><em>Type de courriel :</em> <a href="{{ route('mails.sort')}}?categ=priority&id={{ $mail->priority->id }}">{{ $mail->priority->name ?? '' }}</a>
                                    <i class="bi bi-diagram-3-fill me-2"></i><em>Typologie :</em> <a href="{{ route('mails.sort')}}?categ=typology&id={{ $mail->typology->id }}"> {{ $mail->typology->name ?? '' }} </a>
                                    <i class="bi bi-file-earmark-text-fill me-2"></i><em>Copie :</em> <a href="{{ route('mails.sort')}}?categ=documentType&id={{ $mail->documentType->id }}">{{ $mail->documentType->name ?? '' }}</a>
                                </p>
                            </div>
                            <div class="col-md-3 text-md-end text-center">
                                <div class="d-flex justify-content-md-end justify-content-center align-items-center">
                                    @if($mail->attachments->count() > 0)
                                        <button class="btn btn-sm btn-outline-info me-2" data-bs-toggle="modal" data-bs-target="#attachmentsModal{{ $mail->id }}">
                                            {{ $mail->attachments->count() }} <i class="bi bi-paperclip"></i>
                                        </button>
                                    @else
                                        <span class="text-muted me-2"><i class="bi bi-paperclip"></i> 0</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            {{--            {{ $mails->links() }}--}}
        </div>

        <!-- Attachments Modals -->
        @foreach ($mails as $mail)
            @if($mail->attachments->count() > 0)
                <div class="modal fade" id="attachmentsModal{{ $mail->id }}" tabindex="-1" aria-labelledby="attachmentsModalLabel{{ $mail->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="attachmentsModalLabel{{ $mail->id }}">
                                    <i class="bi bi-paperclip me-2"></i>Pièces jointes - {{ $mail->code }}
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

@endsection



@push('scripts')
    <script>
        function confirmDelete(mailId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce courrier ?')) {
                document.getElementById('delete-form-' + mailId).submit();
            }
        }

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
    </script>
@endpush
