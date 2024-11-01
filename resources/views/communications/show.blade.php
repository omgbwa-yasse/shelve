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
                        <p><strong>{{ __('Status') }}:</strong> {{ $communication->status->name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light">
                <div class="btn-group">
                    <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary"> {{ __('Back') }}</a>
                    <a href="{{ route('transactions.edit', $communication->id) }}" class="btn btn-warning"> {{ __('Edit') }}</a>
                    <form action="{{ route('transactions.destroy', $communication->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('{{ __('Are you sure you want to delete this communication?') }}')">{{ __('Delete') }}</button>
                    </form>
                </div>
                <div class="btn-group ms-2">
                    @if($communication->return_effective == NULL)
                        <a href="{{ route('return-effective') }}?id={{$communication->id}}" class="btn btn-success">{{ __('Effective Return') }}</a>
                    @else
                        <a href="{{ route('return-cancel') }}?id={{$communication->id}}" class="btn btn-danger">{{ __('Cancel Effective Return') }}</a>
                    @endif
                    <a href="{{ route('record-transmission') }}?id={{$communication->id}}" class="btn btn-primary">{{ __('Transmit Documents') }}</a>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('Documents') }}</h5>
                <a href="{{ route('transactions.records.create', $communication->id) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i>{{ __('Add Documents') }}
                </a>
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
                                <a href="{{ route('transactions.records.show', [$communication, $record]) }}" class="btn btn-outline-secondary btn-sm">{{ __('View') }}</a>
                                @if($record->return_effective == NULL && !$record->is_original)
                                    <a href="{{ route('record-return-effective') }}?id={{ $record->id }}" class="btn btn-success btn-sm">{{ __('Return') }}</a>
                                @elseif($record->return_effective != NULL && $record->is_original)
                                    <a href="{{ route('record-return-cancel') }}?id={{ $record->id }}" class="btn btn-danger btn-sm">{{ __('Cancel Return') }}</a>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
            document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('exportBtn').addEventListener('click', function(e) {
                e.preventDefault();
                window.location.href = "{{ route('communications.export', $communication->id) }}";
            });

            document.getElementById('printBtn').addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = "{{ route('communications.print', $communication->id) }}";
        });
        });
    </script>

@endpush
