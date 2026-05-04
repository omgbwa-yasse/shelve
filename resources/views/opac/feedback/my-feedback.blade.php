@extends('opac.layouts.app')

@section('title', __('My Feedback') . ' - OPAC')

@section('content')
<div class="container my-5">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1">{{ __('My Feedback') }}</h1>
            <p class="text-muted mb-0">{{ __('All the feedback you have submitted') }}</p>
        </div>
        <a href="{{ route('opac.feedback.create') }}" class="btn btn-opac-primary">
            <i class="fas fa-plus me-2"></i>{{ __('New Feedback') }}
        </a>
    </div>

    <div class="opac-card">
        @if($feedback->count())
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Subject') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($feedback as $fb)
                            <tr>
                                <td><span class="badge bg-secondary">{{ ucfirst($fb->type ?? '—') }}</span></td>
                                <td>{{ Str::limit($fb->subject ?? $fb->title ?? '—', 60) }}</td>
                                <td>
                                    <span class="badge bg-{{ match($fb->status ?? '') {
                                        'resolved' => 'success',
                                        'pending'  => 'warning',
                                        default    => 'secondary'
                                    } }} text-dark">{{ ucfirst($fb->status ?? 'pending') }}</span>
                                </td>
                                <td class="text-muted small">{{ $fb->created_at->format('d/m/Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3 d-flex justify-content-center">
                {{ $feedback->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-comment-dots fa-4x text-muted mb-3 d-block opacity-25"></i>
                <h5>{{ __('No feedback yet') }}</h5>
                <p class="text-muted mb-4">{{ __('Share your thoughts with us — suggestions, questions, or complaints.') }}</p>
                <a href="{{ route('opac.feedback.create') }}" class="btn btn-opac-primary">
                    <i class="fas fa-comment me-2"></i>{{ __('Send Feedback') }}
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
