@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-arrow-return-left"></i> {{ __('Retour de prêt') }}</h1>
        <a href="{{ route('library.loans.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Liste des prêts') }}
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('library.loans.store-return') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="copy_barcode" class="form-label">{{ __('Numéro Inventaire Livre (Code-barres)') }}</label>
                            <div class="input-group input-group-lg">
                                <input type="text" class="form-control @error('copy_barcode') is-invalid @enderror"
                                       id="copy_barcode" name="copy_barcode" value="{{ old('copy_barcode') }}"
                                       required placeholder="Scannez le code-barres ici..." autofocus>
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-box-arrow-in-down"></i> {{ __('Retourner') }}
                                </button>
                            </div>
                            <div id="loan_feedback" class="mt-3"></div>
                            @error('copy_barcode')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </form>

                    <div id="loan_details" class="d-none">
                        <div class="alert alert-info">
                            <h5 class="alert-heading"><i class="bi bi-info-circle"></i> {{ __('Détails du prêt trouvé') }}</h5>
                            <hr>
                            <dl class="row mb-0">
                                <dt class="col-sm-4">{{ __('Titre') }}</dt>
                                <dd class="col-sm-8" id="detail_title"></dd>

                                <dt class="col-sm-4">{{ __('Emprunteur') }}</dt>
                                <dd class="col-sm-8" id="detail_borrower"></dd>

                                <dt class="col-sm-4">{{ __('Date de prêt') }}</dt>
                                <dd class="col-sm-8" id="detail_loan_date"></dd>

                                <dt class="col-sm-4">{{ __('Date de retour prévue') }}</dt>
                                <dd class="col-sm-8" id="detail_due_date"></dd>
                            </dl>
                        </div>
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
    const barcodeInput = document.getElementById('copy_barcode');
    const feedbackDiv = document.getElementById('loan_feedback');
    const detailsDiv = document.getElementById('loan_details');

    // Fields to populate
    const titleField = document.getElementById('detail_title');
    const borrowerField = document.getElementById('detail_borrower');
    const loanDateField = document.getElementById('detail_loan_date');
    const dueDateField = document.getElementById('detail_due_date');

    let timeout = null;

    barcodeInput.addEventListener('input', function() {
        const barcode = this.value;

        // Clear previous timeout
        if (timeout) clearTimeout(timeout);

        // Hide details if empty
        if (!barcode) {
            feedbackDiv.innerHTML = '';
            detailsDiv.classList.add('d-none');
            barcodeInput.classList.remove('is-valid', 'is-invalid');
            return;
        }

        // Wait for user to stop typing (or scanner to finish)
        timeout = setTimeout(() => {
            fetch(`{{ route('library.loans.check-active-loan') }}?barcode=${barcode}`)
                .then(response => response.json())
                .then(data => {
                    if(data.found) {
                        // Show success feedback
                        let statusHtml = `<div class="text-success fw-bold"><i class="bi bi-check-circle"></i> {{ __('Prêt actif trouvé') }}</div>`;

                        if(data.is_overdue) {
                            statusHtml += `<div class="text-danger mt-1"><i class="bi bi-exclamation-circle"></i> {{ __('En retard de') }} ${data.days_overdue} {{ __('jours') }}</div>`;
                        }

                        feedbackDiv.innerHTML = statusHtml;
                        barcodeInput.classList.remove('is-invalid');
                        barcodeInput.classList.add('is-valid');

                        // Populate details
                        titleField.textContent = data.title;
                        borrowerField.textContent = data.borrower_name;
                        loanDateField.textContent = data.loan_date;
                        dueDateField.textContent = data.due_date;

                        detailsDiv.classList.remove('d-none');
                    } else {
                        // Show error feedback
                        feedbackDiv.innerHTML = `<div class="text-danger"><i class="bi bi-x-circle"></i> ${data.message || '{{ __('Aucun prêt trouvé') }}'}</div>`;
                        barcodeInput.classList.add('is-invalid');
                        barcodeInput.classList.remove('is-valid');
                        detailsDiv.classList.add('d-none');
                    }
                })
                .catch(error => console.error('Error:', error));
        }, 500); // 500ms delay
    });
});
</script>
@endpush
