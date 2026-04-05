{{-- Modal Signature Électronique --}}
<div class="modal fade" id="signModal" tabindex="-1" aria-labelledby="signModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('documents.sign', $document) }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="signModalLabel">
                        <i class="bi bi-patch-check"></i> {{ __('electronic_signature') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>{{ __('warning') }}:</strong> {{ __('irreversible_action_warning') }}
                    </div>

                    {{-- Password Confirmation --}}
                    <div class="mb-3">
                        <label for="signature-password" class="form-label">{{ __('confirm_identity_password') }} <span class="text-danger">*</span></label>
                        <input type="password" name="signature_password" id="signature-password" class="form-control"
                               placeholder="{{ __('your_password') }}" required autofocus>
                    </div>

                    {{-- Signature Reason --}}
                    <div class="mb-3">
                        <label for="signature-reason" class="form-label">{{ __('signature_reason_optional') }}</label>
                        <input type="text" name="signature_reason" id="signature-reason" class="form-control"
                               placeholder="Ex: Validation technique, Approbation managériale...">
                    </div>

                    {{-- Info Signature --}}
                    <div class="card bg-light border-0">
                        <div class="card-body p-2">
                            <small class="text-muted">
                                <strong>{{ __('signature_info') }}:</strong><br>
                                <i class="bi bi-person"></i> {{ __('signer') }}: {{ Auth::user()->name }}<br>
                                <i class="bi bi-calendar-event"></i> {{ __('date') }}: {{ now()->format('d/m/Y à H:i') }}<br>
                                <i class="bi bi-hash"></i> {{ __('hash') }}: SHA256 ({{ __('calculated_automatically') }})
                            </small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cancel') }}</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-pencil-fill"></i> {{ __('sign_electronically') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
