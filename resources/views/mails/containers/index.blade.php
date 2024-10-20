@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Contenant pour archivage (Transferring en cours) </h1>
        <div id="mailList">
        <div class="d-flex justify-content-between align-items-center bg-light p-3 mb-3">
            <div class="d-flex align-items-center">
                <a href="#" id="transferBtn" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-arrow-repeat me-1"></i>
                    {{ __('transfer') }}
                </a>
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
        <div class="list-group">
            @foreach ($mailContainers as $mailContainer)
              <label class="list-group-item mt-2">
                <input class="form-check-input me-2" type="checkbox" name="mail_containers[]" value="{{ $mailContainer->id }}" />
                <strong>
                  {{ $mailContainer->code }} - {{ $mailContainer->name }}
                </strong>
                ( appartenant à : {{ $mailContainer->creatorOrganisation->name }} )
                <strong>
                  {{ $mailContainer->containerType->name }}
                </strong>
                <a href="{{ route('mail-container.show', $mailContainer->id) }}" class="btn btn-info">Paramètre</a>
                <a href="{{ route('mails.sort') }}?categ=container&id={{ $mailContainer->id }}" class="btn btn-success"> {{ $mailContainer->mailArchivings->count() }} courriers archivés</a>
              </label>
            @endforeach
        </div>
    </div>
    </div>

    <div class="modal fade" id="transferModal" tabindex="-1" aria-labelledby="transferModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="transferModalLabel">Transfer Dolly</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="transferForm">
                        <div class="mb-3">
                            <label for="transferDescription" class="form-label">Description du transfert</label>
                            <input type="text" class="form-control" id="transferDescription" placeholder="Entrez la description du transfert">
                        </div>
                        <div class="mb-3">
                            <label for="serviceSelect" class="form-label">Service qui reçoit</label>
                            <select class="form-select" id="serviceSelect">
                                <option selected>Choisir un service</option>
                                <option value="1">Service A</option>
                                <option value="2">Service B</option>
                                <option value="3">Service C</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Envoyer le transfert</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>


    </script>
@endsection

