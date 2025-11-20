@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-info-circle"></i> {{ __('Détails du prêt') }} #{{ $loan->id }}</h1>
        <a href="{{ route('library.loans.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Retour') }}
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>{{ __('Informations sur le prêt') }}</h5>
                    <table class="table table-borderless">
                        <tr>
                            <th>{{ __('Statut') }}</th>
                            <td>
                                <span class="badge bg-{{ $loan->status === 'active' ? 'success' : ($loan->status === 'overdue' ? 'danger' : 'secondary') }}">
                                    {{ $loan->status_label }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('Date de prêt') }}</th>
                            <td>{{ $loan->loan_date->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Date de retour prévue') }}</th>
                            <td>{{ $loan->due_date->format('d/m/Y') }}</td>
                        </tr>
                        @if($loan->return_date)
                        <tr>
                            <th>{{ __('Date de retour effectif') }}</th>
                            <td>{{ $loan->return_date->format('d/m/Y') }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>{{ __('Bibliothécaire') }}</th>
                            <td>{{ $loan->librarian->name ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5>{{ __('Informations sur l\'ouvrage') }}</h5>
                    <p><strong>{{ __('Titre') }}:</strong> {{ $loan->copy->book->title ?? 'N/A' }}</p>
                    <p><strong>{{ __('Auteur') }}:</strong> {{ $loan->copy->book->author->name ?? 'N/A' }}</p>
                    <p><strong>{{ __('Code-barres') }}:</strong> {{ $loan->copy->barcode }}</p>

                    <h5 class="mt-4">{{ __('Informations sur l\'emprunteur') }}</h5>
                    <p><strong>{{ __('Nom') }}:</strong> {{ $loan->borrower->name }}</p>
                    <p><strong>{{ __('Email') }}:</strong> {{ $loan->borrower->email }}</p>
                </div>
            </div>

            @if($loan->status === 'active' || $loan->status === 'overdue')
            <hr>
            <div class="d-flex justify-content-end">
                <form action="{{ route('library.loans.return', $loan->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> {{ __('Marquer comme retourné') }}
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
