@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Bordereau de versement </h1>
        <a href="{{ route('slips.create') }}" class="btn btn-primary mb-3">
            <i class="bi bi-plus-circle"></i> Nouveau bordereau
        </a>

        <div id="slipList">
            @foreach ($slips as $slip)
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-9">
                                <h5 class="card-title mb-2">
                                    <b>{{ $slip->code }}</b> - {{ $slip->name }}
                                </h5>
                                <p class="card-text mb-1">
                                    <strong>Description:</strong> {{ $slip->description }}<br>
                                </p>
                            </div>
                            <div class="col-md-3 text-md-end text-center">
                                <div class="d-flex justify-content-md-end justify-content-center align-items-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('slips.show', $slip->id) }}" class="btn btn-sm btn-outline-secondary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('slips.edit', $slip->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete({{ $slip->id }})" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
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
