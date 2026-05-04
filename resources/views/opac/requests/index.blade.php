@extends('opac.layouts.app')

@section('title', __('My Requests') . ' - OPAC')

@section('content')
<div class="container my-5">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1">{{ __('My Requests') }}</h1>
            <p class="text-muted mb-0">{{ __('Track your document requests') }}</p>
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
                            <th>{{ __('Description') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $req)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $req->type ?? '—' }}</span></td>
                                <td>{{ Str::limit($req->title ?? $req->reason ?? '—', 60) }}</td>
                                <td><span class="badge bg-warning text-dark">{{ $req->status ?? 'pending' }}</span></td>
                                <td class="text-muted small">{{ $req->created_at->format('d/m/Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-file-alt fa-4x text-muted mb-3 d-block opacity-25"></i>
                <h5>{{ __('No requests yet') }}</h5>
                <p class="text-muted mb-4">{{ __('Submit a request for any document you need.') }}</p>
                <a href="{{ route('opac.document-requests.create') }}" class="btn btn-opac-primary">
                    <i class="fas fa-plus me-2"></i>{{ __('New Request') }}
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
