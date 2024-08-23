@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Communications</h1>
        <a href="{{ route('transactions.create') }}" class="btn btn-primary mb-3">
            <i class="bi bi-plus-circle"></i> Create New Communication
        </a>

        <div class="row">
            @foreach ($communications as $communication)
                <div class="col-12 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="card-title">
                                        <i class="bi bi-person-badge"></i> ID: {{ $communication->id ?? 'N/A' }}
                                    </h5>
                                    <p class="card-text">
                                        <i class="bi bi-upc"></i> <strong>Code:</strong> {{ $communication->code ?? 'N/A' }}<br>
                                        <i class="bi bi-person"></i> <strong>Operator:</strong> {{ $communication->operator->name ?? 'N/A' }}<br>
                                        <i class="bi bi-people"></i> <strong>User:</strong> {{ $communication->user->name ?? 'N/A' }}
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="card-text">
                                        <i class="bi bi-building"></i> <strong>Operator Organisation:</strong> {{ $communication->operatorOrganisation->name ?? 'N/A' }}<br>
                                        <i class="bi bi-building"></i> <strong>User Organisation:</strong> {{ $communication->userOrganisation->name ?? 'N/A' }}<br>
                                        <i class="bi bi-calendar"></i> <strong>Return Date:</strong> {{ $communication->return_date ?? 'N/A' }}<br>
                                        <i class="bi bi-calendar-check"></i> <strong>Return Effective:</strong> {{ $communication->return_effective ?? 'N/A' }}<br>
                                        <i class="bi bi-flag"></i> <strong>Status:</strong> {{ $communication->status->name ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>
                            <a href="{{ route('transactions.show', $communication->id) }}" class="btn btn-info mt-3">
                                <i class="bi bi-eye"></i> Show
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
