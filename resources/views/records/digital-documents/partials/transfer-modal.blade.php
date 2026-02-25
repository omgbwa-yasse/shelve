<!-- Transfer Modal - Digital Document -->
<div class="modal fade" id="transferModal" tabindex="-1" role="dialog" aria-labelledby="transferModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="transferModalLabel">
                    <i class="fas fa-exchange-alt"></i> {{ __('Transfer Digital Document to Physical Archive') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="transferForm" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="transferType" name="type" value="">
                    <input type="hidden" id="transferDigitalId" name="digital_id" value="">

                    <!-- Document Info -->
                    <div class="alert alert-info" role="alert">
                        <strong id="transferSourceInfo"></strong>
                        <p class="mb-0 small mt-2" id="transferSourceDetails"></p>
                    </div>

                    <!-- Physical Record Selection -->
                    <div class="mb-3">
                        <label for="physicalRecordSelect" class="form-label">
                            {{ __('Select Target Physical Record') }} <span class="text-danger">*</span>
                        </label>
                        <select
                            id="physicalRecordSelect"
                            name="physical_id"
                            class="form-select"
                            required
                            onchange="updatePhysicalRecordInfo(this)"
                        >
                            <option value="">{{ __('-- Select a physical record --') }}</option>
                        </select>
                        <small class="form-text text-muted">
                            {{ __('Search and select the physical archive record to associate this digital document with.') }}
                        </small>
                    </div>

                    <!-- Physical Record Info -->
                    <div id="physicalRecordInfo" class="alert alert-secondary d-none" role="alert">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>{{ __('Code:') }}</strong> <span id="physicalCode"></span></p>
                                <p class="mb-1"><strong>{{ __('Level:') }}</strong> <span id="physicalLevel"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>{{ __('Status:') }}</strong> <span id="physicalStatus"></span></p>
                                <p class="mb-1"><strong>{{ __('Organisation:') }}</strong> <span id="physicalOrganisation"></span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Transfer Notes -->
                    <div class="mb-3">
                        <label for="transferNotes" class="form-label">{{ __('Transfer Notes') }}</label>
                        <textarea
                            id="transferNotes"
                            name="notes"
                            class="form-control"
                            rows="3"
                            placeholder="{{ __('Optional: Add any notes about this transfer...') }}"
                        ></textarea>
                    </div>

                    <!-- Warning -->
                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>{{ __('Important:') }}</strong>
                        <p class="mb-0">
                            {{ __('After confirmation, this digital document will be associated with the physical record and permanently deleted from the digital storage system.') }}
                        </p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-danger" id="transferSubmitBtn">
                        <i class="fas fa-check"></i> {{ __('Confirm Transfer & Delete') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize select2 if available
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('#physicalRecordSelect').select2({
            dropdownParent: $('#transferModal'),
            allowClear: true,
            width: '100%'
        });
    }

    // Handle form submission
    document.getElementById('transferForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitTransfer();
    });
});

function initTransferModal(type, digitalId) {
    const form = document.getElementById('transferForm');
    document.getElementById('transferType').value = type;
    document.getElementById('transferDigitalId').value = digitalId;

    // Load transfer form data
    fetch('/api/v1/record-digital-transfer/form', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: new URLSearchParams({
            type: type,
            id: digitalId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const formData = data.data;

            // Update modal title and info
            document.getElementById('transferSourceInfo').textContent =
                (type === 'document' ? '📄 ' : '📁 ') + formData.digital_name;
            document.getElementById('transferSourceDetails').textContent =
                (formData.digital_code ? 'Code: ' + formData.digital_code : 'No code');

            // Populate physical records dropdown
            const select = document.getElementById('physicalRecordSelect');
            select.innerHTML = '<option value="">{{ __("-- Select a physical record --") }}</option>';

            formData.physical_records.forEach(record => {
                const option = document.createElement('option');
                option.value = record.id;
                option.textContent = record.reference;
                option.dataset.record = JSON.stringify(record);
                select.appendChild(option);
            });

            // Reset form
            form.reset();
            document.getElementById('physicalRecordInfo').classList.add('d-none');

            // Show modal
            new bootstrap.Modal(document.getElementById('transferModal')).show();
        } else {
            alert('{{ __("Failed to load transfer form") }}: ' + (data.message || ''));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('{{ __("An error occurred while loading the transfer form") }}');
    });
}

function updatePhysicalRecordInfo(select) {
    if (select.value) {
        const option = select.options[select.selectedIndex];
        const record = JSON.parse(option.dataset.record);

        document.getElementById('physicalCode').textContent = record.code;
        document.getElementById('physicalLevel').textContent = record.level;
        document.getElementById('physicalStatus').textContent = record.status;
        document.getElementById('physicalOrganisation').textContent = record.organisation || '—';

        document.getElementById('physicalRecordInfo').classList.remove('d-none');
    } else {
        document.getElementById('physicalRecordInfo').classList.add('d-none');
    }
}

function submitTransfer() {
    const type = document.getElementById('transferType').value;
    const digitalId = document.getElementById('transferDigitalId').value;
    const physicalId = document.getElementById('physicalRecordSelect').value;
    const notes = document.getElementById('transferNotes').value;
    const submitBtn = document.getElementById('transferSubmitBtn');

    if (!physicalId) {
        alert('{{ __("Please select a physical record") }}');
        return;
    }

    // Show loading state
    submitBtn.disabled = true;
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>{{ __("Processing...") }}';

    fetch('/api/v1/record-digital-transfer/', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            type: type,
            digital_id: parseInt(digitalId),
            physical_id: parseInt(physicalId),
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('transferModal')).hide();
            // Show success message
            alert('{{ __("Transfer completed successfully!") }}');
            // Reload page to reflect changes
            window.location.reload();
        } else {
            alert('{{ __("Transfer failed") }}: ' + (data.message || 'Unknown error'));
            if (data.errors && Array.isArray(data.errors)) {
                console.error('Errors:', data.errors);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        alert('{{ __("An error occurred during transfer") }}');
    });
}
</script>
