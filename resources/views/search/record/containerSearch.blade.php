@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Tous les contenants d'archives</h1>
        <div id="containerList">
            @foreach ($containers as $container)
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-9">
                                <h5 class="card-title mb-2">
                                    <b>{{ $container->code ?? 'N/A' }}</b> - Shelf: {{ $container->shelf->code ?? 'N/A' }}
                                </h5>
                                <p class="card-text mb-1">
                                    <i class="bi bi-flag"></i> <strong>Status:</strong> {{ $container->status->name ?? 'N/A' }}<br>
                                    <i class="bi bi-building"></i> <strong>Property:</strong> {{ $container->property->name ?? 'N/A' }}
                                </p>
                            </div>
                            <div class="col-md-3 text-md-end text-center">
                                <div class="d-flex justify-content-md-end justify-content-center align-items-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('records.sort') }}?categ=container&id={{ $container->id }}" class="btn btn-sm btn-outline-secondary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
