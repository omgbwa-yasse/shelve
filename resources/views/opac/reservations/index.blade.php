@extends('opac.layouts.app')

@section('title', __('My Reservations') . ' - OPAC')

@section('content')
<div class="container my-5">
    <div class="mb-4">
        <h1 class="h3 mb-1">{{ __('My Reservations') }}</h1>
        <p class="text-muted mb-0">{{ __('List of your reservation requests') }}</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
    @endif

    <div class="opac-card">
        @if($reservations->count())
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Record') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reservations as $reservation)
                            <tr>
                                <td>{{ $reservation->record->title ?? '—' }}</td>
                                <td><span class="badge bg-secondary">{{ $reservation->status ?? '—' }}</span></td>
                                <td class="text-muted small">{{ $reservation->created_at->format('d/m/Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-bookmark fa-4x text-muted mb-3 d-block opacity-25"></i>
                <h5>{{ __('No reservations yet') }}</h5>
                <p class="text-muted mb-4">{{ __('Browse the catalog and reserve documents that interest you.') }}</p>
                <a href="{{ route('opac.records.index') }}" class="btn btn-opac-primary">
                    <i class="fas fa-archive me-2"></i>{{ __('Browse Catalog') }}
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
