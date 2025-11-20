@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-plus-circle"></i> {{ __('Nouveau prêt') }}</h1>
        <a href="{{ route('library.loans.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Retour') }}
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('library.loans.store') }}">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="borrower_identifier" class="form-label">{{ __('Numéro Inventaire Lecteur (ID)') }}</label>
                        <div class="input-group">
                            <input type="text" class="form-control @error('borrower_identifier') is-invalid @enderror"
                                   id="borrower_identifier" name="borrower_identifier" value="{{ old('borrower_identifier') }}" required placeholder="Ex: 123">
                            <span class="input-group-text" id="borrower_name_display"><i class="bi bi-person"></i></span>
                        </div>
                        <div id="borrower_feedback" class="form-text text-muted"></div>
                        @error('borrower_identifier')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="copy_barcode" class="form-label">{{ __('Numéro Inventaire Livre (Code-barres)') }}</label>
                        <div class="input-group">
                            <input type="text" class="form-control @error('copy_barcode') is-invalid @enderror"
                                   id="copy_barcode" name="copy_barcode" value="{{ old('copy_barcode') }}" required placeholder="Ex: BOOK-001">
                            <span class="input-group-text" id="copy_title_display"><i class="bi bi-book"></i></span>
                        </div>
                        <div id="copy_feedback" class="form-text text-muted"></div>
                         @error('copy_barcode')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="loan_date" class="form-label">{{ __('Date de prêt') }}</label>
                        <input type="date" class="form-control @error('loan_date') is-invalid @enderror"
                               id="loan_date" name="loan_date" value="{{ date('Y-m-d') }}" required>
                        @error('loan_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="due_date" class="form-label">{{ __('Date de retour prévue') }}</label>
                        <input type="date" class="form-control @error('due_date') is-invalid @enderror"
                               id="due_date" name="due_date" required>
                        @error('due_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">{{ __('Notes') }}</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror"
                              id="notes" name="notes" rows="3"></textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> {{ __('Enregistrer le prêt') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Borrower Lookup
    const borrowerInput = document.getElementById('borrower_identifier');
    const borrowerFeedback = document.getElementById('borrower_feedback');
    const borrowerDisplay = document.getElementById('borrower_name_display');

    borrowerInput.addEventListener('blur', function() {
        const id = this.value;
        if(id) {
            fetch(`{{ route('library.loans.check-borrower') }}?identifier=${id}`)
                .then(response => response.json())
                .then(data => {
                    if(data.found) {
                        borrowerFeedback.innerHTML = `<span class="text-success"><i class="bi bi-check-circle"></i> ${data.name} (${data.email})</span>`;
                        borrowerInput.classList.remove('is-invalid');
                        borrowerInput.classList.add('is-valid');
                    } else {
                        borrowerFeedback.innerHTML = `<span class="text-danger"><i class="bi bi-x-circle"></i> {{ __('Lecteur non trouvé') }}</span>`;
                        borrowerInput.classList.add('is-invalid');
                    }
                })
                .catch(error => console.error('Error:', error));
        } else {
            borrowerFeedback.innerHTML = '';
            borrowerInput.classList.remove('is-valid', 'is-invalid');
        }
    });

    // Copy Lookup
    const copyInput = document.getElementById('copy_barcode');
    const copyFeedback = document.getElementById('copy_feedback');
    const copyDisplay = document.getElementById('copy_title_display');

    copyInput.addEventListener('blur', function() {
        const barcode = this.value;
        if(barcode) {
            fetch(`{{ route('library.loans.check-copy') }}?barcode=${barcode}`)
                .then(response => response.json())
                .then(data => {
                    if(data.found) {
                        let statusHtml = '';
                        if(data.is_available) {
                            statusHtml = `<span class="text-success"><i class="bi bi-check-circle"></i> ${data.title}</span>`;
                            copyInput.classList.remove('is-invalid');
                            copyInput.classList.add('is-valid');
                        } else {
                            statusHtml = `<span class="text-warning"><i class="bi bi-exclamation-triangle"></i> ${data.title} (${data.status_label})</span>`;
                            copyInput.classList.add('is-invalid'); // Warn user it's not available
                        }
                        copyFeedback.innerHTML = statusHtml;
                    } else {
                        copyFeedback.innerHTML = `<span class="text-danger"><i class="bi bi-x-circle"></i> {{ __('Exemplaire non trouvé') }}</span>`;
                        copyInput.classList.add('is-invalid');
                    }
                })
                .catch(error => console.error('Error:', error));
        } else {
            copyFeedback.innerHTML = '';
            copyInput.classList.remove('is-valid', 'is-invalid');
        }
    });
});
</script>
@endpush
