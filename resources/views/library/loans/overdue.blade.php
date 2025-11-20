@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-exclamation-triangle"></i> {{ __('Prêts en retard') }}</h1>
        <a href="{{ route('library.loans.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Retour') }}
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('Lecteur') }}</th>
                            <th>{{ __('Ouvrage') }}</th>
                            <th>{{ __('Date prêt') }}</th>
                            <th>{{ __('Date retour prévue') }}</th>
                            <th>{{ __('Jours de retard') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loans as $loan)
                        <tr>
                            <td>{{ $loan->borrower->name }}</td>
                            <td>{{ $loan->copy->book->title ?? 'N/A' }}</td>
                            <td>{{ $loan->loan_date->format('d/m/Y') }}</td>
                            <td>{{ $loan->due_date->format('d/m/Y') }}</td>
                            <td class="text-danger fw-bold">{{ $loan->days_overdue }}</td>
                            <td>
                                <a href="{{ route('library.loans.show', $loan->id) }}" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                {{ __('Aucun prêt en retard') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $loans->links() }}
        </div>
    </div>
</div>
@endsection
