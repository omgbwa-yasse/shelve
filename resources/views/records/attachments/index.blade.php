@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2 text-dark mb-1">{{ __('attachments_management') }}</h1>
                    <p class="text-muted mb-0">{{ __('attachments_management_description') }} : <strong>{{ $record->name }}</strong></p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('records.show', $record) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>{{ __('back_to_record') }}
                    </a>
                    <a href="{{ route('records.attachments.create', $record) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>{{ __('add_attachment') }}
                    </a>
                </div>
            </div>

            <!-- Main Content Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0 text-dark">
                                <i class="fas fa-paperclip me-2 text-primary"></i>
                                {{ __('attachments_list') }}
                            </h5>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-primary rounded-pill">{{ $attachments->count() }} {{ __('documents_count') }}</span>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    @if($attachments->isEmpty())
                        <!-- Empty State -->
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fas fa-file-upload fa-3x text-muted"></i>
                            </div>
                            <h5 class="text-muted mb-2">{{ __('no_attachments_found') }}</h5>
                            <p class="text-muted mb-3">{{ __('start_adding_documents') }}</p>
                            <a href="{{ route('records.attachments.create', $record) }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>{{ __('add_first_attachment') }}
                            </a>
                        </div>
                    @else
                        <!-- Attachments Table -->
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0 ps-4">
                                            <i class="fas fa-file me-2 text-muted"></i>{{ __('document_name') }}
                                        </th>
                                        <th class="border-0">
                                            <i class="fas fa-calendar me-2 text-muted"></i>{{ __('add_date') }}
                                        </th>
                                        <th class="border-0">
                                            <i class="fas fa-info-circle me-2 text-muted"></i>{{ __('information') }}
                                        </th>
                                        <th class="border-0 text-end pe-4">{{ __('actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($attachments as $attachment)
                                    <tr class="align-middle">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="attachment-icon me-3">
                                                    <i class="fas fa-file-pdf text-danger"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-medium text-dark">{{ $attachment->name }}</div>
                                                    <small class="text-muted">{{ Str::limit($attachment->description ?? __('no_description'), 50) }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted">
                                                {{ $attachment->created_at ? $attachment->created_at->format('d/m/Y H:i') : 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <small class="text-muted">
                                                    <i class="fas fa-user me-1"></i>
                                                    {{ $attachment->user->name ?? __('unknown_user') }}
                                                </small>
                                                @if($attachment->file_size)
                                                <small class="text-muted">
                                                    <i class="fas fa-weight-hanging me-1"></i>
                                                    {{ number_format($attachment->file_size / 1024, 2) }} KB
                                                </small>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group" role="group">
                                                <a href="#" class="btn btn-outline-primary btn-sm" title="{{ __('download') }}">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <a href="#" class="btn btn-outline-info btn-sm" title="{{ __('preview') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger btn-sm" title="{{ __('delete_attachment') }}" 
                                                        onclick="confirmDelete('{{ $attachment->id }}', '{{ $attachment->name }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Footer Actions -->
            @if(!$attachments->isEmpty())
            <div class="mt-3 text-center">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    {{ __('click_actions_to_manage') }}
                </small>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">{{ __('confirm_deletion') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('delete_attachment_confirmation') }} <strong id="attachmentName"></strong> ?</p>
                <p class="text-danger small">{{ __('irreversible_action') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cancel') }}</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">{{ __('delete') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function confirmDelete(attachmentId, attachmentName) {
    document.getElementById('attachmentName').textContent = attachmentName;
    document.getElementById('deleteForm').action = `{{ route('records.attachments.destroy', [$record, ':id']) }}`.replace(':id', attachmentId);
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>
@endsection

<style>
.card {
    border-radius: 0.75rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid #dee2e6;
}

.attachment-icon {
    width: 40px;
    height: 40px;
    background: #f8f9fa;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.table th {
    font-weight: 600;
    color: #495057;
    border-top: none;
}

.table td {
    vertical-align: middle;
    border-top: 1px solid #f8f9fa;
}

.btn-group .btn {
    border-radius: 0.375rem;
    margin: 0 2px;
}

.btn-outline-primary:hover,
.btn-outline-info:hover,
.btn-outline-danger:hover {
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

.badge {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .d-flex.justify-content-between {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }
    
    .btn-group {
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .btn-group .btn {
        margin: 0;
    }
}
</style>
