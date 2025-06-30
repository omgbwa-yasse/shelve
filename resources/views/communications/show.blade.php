@extends('layouts.app')

@section('content')
    <div class="container-fluid ">
        <h1 class="mb-4"><i class="bi bi-file-earmark-spreadsheet"></i> {{ __('Communication Form') }}</h1>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $communication->code ?? 'N/A' }} : {{ $communication->name ?? 'N/A' }}</h5>
                    <div class="btn-group">
                        <button class="btn btn-outline-secondary btn-sm" id="exportBtn">
                            <i class="bi bi-download me-1"></i>{{ __('Export') }}
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" id="printBtn">
                            <i class="bi bi-printer me-1"></i>{{ __('Print') }}
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-12">
                        <p class="card-text"><strong>{{ __('Content') }}:</strong> {{ $communication->content ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>{{ __('Requester') }}:</strong> {{ $communication->user->name ?? 'N/A' }}</p>
                        <p><small class="text-muted">({{ $communication->userOrganisation->name ?? 'N/A' }})</small></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>{{ __('Operator') }}:</strong> {{ $communication->operator->name ?? 'N/A' }}</p>
                        <p><small class="text-muted">({{ $communication->operatorOrganisation->name ?? 'N/A' }})</small></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <p><strong>{{ __('Return Date') }}:</strong> {{ $communication->return_date ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>{{ __('Effective Return Date') }}:</strong> {{ $communication->return_effective ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>{{ __('Status') }}:</strong> {{ $communication->status?->label() ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light">
                <div class="btn-group">
                    <a href="{{ route('communications.transactions.index') }}" class="btn btn-outline-secondary"> {{ __('Back') }}</a>
                    @if($communication->canBeEdited())
                        <a href="{{ route('communications.transactions.edit', $communication->id) }}" class="btn btn-warning"> {{ __('Edit') }}</a>
                        <form action="{{ route('communications.transactions.destroy', $communication->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('{{ __('Are you sure you want to delete this communication?') }}')">{{ __('Delete') }}</button>
                        </form>
                    @else
                        <span class="badge bg-success ms-2">{{ __('Returned') }} - {{ __('No modification allowed') }}</span>
                    @endif
                </div>
                <div class="btn-group ms-2">
                    <div class="dropdown">
                        <button class="btn btn-info dropdown-toggle" type="button" id="phantomDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-file-earmark-pdf"></i> {{ __('Phantom') }}
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="phantomDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('communications.phantom.generate', $communication->id) }}">
                                    <i class="bi bi-download"></i> {{ __('Download PDF') }}
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('communications.phantom.preview', $communication->id) }}" target="_blank">
                                    <i class="bi bi-eye"></i> {{ __('Preview') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="btn-group ms-2">
                    {{-- Actions basées sur le statut --}}
                    @if($communication->status_id == 1) {{-- Demande en cours --}}
                        <a href="{{ route('communications.actions.validate') }}?id={{$communication->id}}" class="btn btn-success">{{ __('Validate') }}</a>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">{{ __('Reject') }}</button>
                    @elseif($communication->status_id == 2 && !$communication->isReturned()) {{-- Validée --}}
                        <a href="{{ route('communications.actions.transmission') }}?id={{$communication->id}}" class="btn btn-primary">{{ __('Transmit Documents') }}</a>
                    @endif

                    {{-- Actions de retour --}}
                    @if($communication->return_effective == NULL)
                        <a href="{{ route('communications.actions.return-effective') }}?id={{$communication->id}}" class="btn btn-success">{{ __('Effective Return') }}</a>
                    @else
                        <a href="{{ route('communications.actions.return-cancel') }}?id={{$communication->id}}" class="btn btn-danger">{{ __('Cancel Effective Return') }}</a>
                    @endif
                </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('Documents') }}</h5>
                @if($communication->canBeEdited())
                    <a href="{{ route('communications.records.create', $communication->id) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i>{{ __('Add Documents') }}
                    </a>
                @else
                    <span class="badge bg-secondary">{{ __('Communication returned - No documents can be added') }}</span>
                @endif
            </div>
            <ul class="list-group list-group-flush">
                @foreach($communication->records as $record)
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">{{ $record->record->name }}</h6>
                                <p class="mb-1"><small>{{ $record->is_original ? __('Original') : __('Copy') }} | {{ __('Return Date') }}: {{ $record->return_date }}</small></p>
                                <p class="mb-1"><small>{{ __('Effective Return Date') }}: {{ $record->return_effective ?? __('Not returned') }}</small></p>
                                <p class="mb-0"><strong>{{ __('Content') }}:</strong> {{ $record->content ?? 'N/A' }}</p>
                            </div>
                            <div class="btn-group">
                                <a href="{{ route('communications.records.show', [$communication, $record]) }}" class="btn btn-outline-secondary btn-sm">{{ __('View') }}</a>
                                @if($communication->canBeEdited())
                                    @if($record->return_effective == NULL && !$record->is_original)
                                        <a href="{{ route('communications.records.actions.return-effective') }}?id={{ $record->id }}" class="btn btn-success btn-sm">{{ __('Return') }}</a>
                                    @elseif($record->return_effective != NULL && $record->is_original)
                                        <a href="{{ route('communications.records.actions.return-cancel') }}?id={{ $record->id }}" class="btn btn-danger btn-sm">{{ __('Cancel Return') }}</a>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- Modal de rejet --}}
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('communications.actions.reject') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" value="{{ $communication->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectModalLabel">{{ __('Reject Communication') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>{{ __('Are you sure you want to reject this communication?') }}</p>
                        <div class="mb-3">
                            <label for="reason" class="form-label">{{ __('Reason for rejection') }} ({{ __('optional') }})</label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" placeholder="{{ __('Enter the reason for rejection...') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-danger">{{ __('Reject') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const exportBtn = document.getElementById('exportBtn');
            const printBtn = document.getElementById('printBtn');


            if (exportBtn) {
                exportBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    window.location.href = "{{ route('communications.export.excel') }}?id={{ $communication->id }}";
                });
            }

            if (printBtn) {
                printBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    window.location.href = "{{ route('communications.export.print') }}?id={{ $communication->id }}";
                });
            }
        });
    </script>

@endpush
