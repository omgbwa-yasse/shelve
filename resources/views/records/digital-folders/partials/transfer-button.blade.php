<!-- Transfer to Physical Archive Button - Digital Folder -->
<div class="btn-group" role="group">
    <button
        type="button"
        class="btn btn-sm btn-outline-secondary"
        data-bs-toggle="modal"
        data-bs-target="#transferModal"
        onclick="initTransferModal('folder', {{ $folder->id }})"
        title="Transfer this digital folder to a physical archive record"
    >
        <i class="fas fa-exchange-alt"></i> {{ __('Transfer to Physical') }}
    </button>
</div>
