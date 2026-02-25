<!-- Transfer to Physical Archive Button - Digital Document -->
<div class="btn-group" role="group">
    <button
        type="button"
        class="btn btn-sm btn-outline-secondary"
        data-bs-toggle="modal"
        data-bs-target="#transferModal"
        onclick="initTransferModal('document', {{ $document->id }})"
        title="Transfer this digital document to a physical archive record"
    >
        <i class="fas fa-exchange-alt"></i> {{ __('Transfer to Physical') }}
    </button>
</div>
