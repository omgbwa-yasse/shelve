@extends('opac.layouts.app')

@section('title', __('My Document Requests') . ' - OPAC')

@section('content')
<div class="container my-5">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1">{{ __('My Document Requests') }}</h1>
            <p class="text-muted mb-0">{{ __('Track the status of your submitted requests') }}</p>
        </div>
        <a href="{{ route('opac.document-requests.create') }}" class="btn btn-opac-primary">
            <i class="fas fa-plus me-2"></i>{{ __('New Request') }}
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
    @endif

    <div class="opac-card">
        @if($requests->count())
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Reason') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $req)
                            <tr>
                                <td><span class="badge bg-secondary">{{ ucfirst($req->request_type) }}</span></td>
                                <td>{{ Str::limit($req->reason, 60) }}</td>
                                <td>
                                    <span class="badge bg-{{ match($req->status) {
                                        'pending'   => 'warning',
                                        'approved'  => 'success',
                                        'rejected'  => 'danger',
                                        'cancelled' => 'secondary',
                                        default     => 'secondary'
                                    } }} text-dark">{{ ucfirst($req->status) }}</span>
                                </td>
                                <td class="text-muted small">{{ $req->created_at->format('d/m/Y') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('opac.document-requests.show', $req) }}" class="btn btn-sm btn-opac-outline">
                                        <i class="fas fa-eye me-1"></i>{{ __('View') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3 d-flex justify-content-center">
                {{ $requests->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-file-alt fa-4x text-muted mb-3 d-block opacity-25"></i>
                <h5>{{ __('No requests yet') }}</h5>
                <p class="text-muted mb-4">{{ __('Submit your first document request to get started.') }}</p>
                <a href="{{ route('opac.document-requests.create') }}" class="btn btn-opac-primary">
                    <i class="fas fa-plus me-2"></i>{{ __('New Request') }}
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
