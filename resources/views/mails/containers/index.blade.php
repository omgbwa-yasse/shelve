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

    <!-- Modal -->
    <div class="modal fade" id="transferModal" tabindex="-1" aria-labelledby="transferModalLabel" aria-hidden="true">
        <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="transferModalLabel">Transfer Dolly</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <form action="" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="type_id" class="form-label">Type</label>
                    <select class="form-select" id="type_id" name="type_id" required>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
        </div>
    </div>

    <script>
        var transferBtn = document.getElementById('transferBtn');
        transferBtn.addEventListener('click', function(event) {
          event.preventDefault();
          var transferModal = new bootstrap.Modal(document.getElementById('transferModal'));
          transferModal.show();
        });
      </script>
@endsection

