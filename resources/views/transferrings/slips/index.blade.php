@extends('layouts.app')

@section('content')
    <div class="container-fluid ">
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h1 class="">
                    <i class="bi bi-file-earmark-text"></i> Bordereau de versement
                </h1>
            </div>
            <div class="col-auto">
                <a href="{{ route('slips.create') }}" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-circle"></i> Nouveau bordereau
                </a>
            </div>
        </div>

        <div id="slipList">
            @foreach ($slips as $slip)
                <div class="card mb-3 shadow-sm hover-shadow transition">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="card-title mb-2">
                                    <a href="{{ route('slips.show', $slip->id) }}" class="text-decoration-none text-dark" title="View">
                                        <strong>{{ $slip->code }} : {{ $slip->name }}</strong>
                                    </a>
                                </h5>
                                <p class="card-text mb-2">
                                    <i class="bi bi-info-circle text-muted"></i> {{ Str::limit($slip->description, 150) }}
                                </p>
                                <p class="card-text mb-0">
                                    <i class="bi bi-calendar-event text-muted"></i>
                                    <a href="{{ route('slips-sort')}}?categ=dates&date_exact={{ $slip->created_at->format('Y-m-d') }}" class="text-decoration-none">
                                        {{ $slip->created_at->format('d M Y') }}
                                    </a>
                                    <span class="mx-2">|</span>
                                    <i class="bi bi-building text-muted"></i>
                                    <a href="{{ route('slips-sort')}}?categ=user-organisation&id={{ $slip->userOrganisation->id }}" class="text-decoration-none">
                                        {{ $slip->userOrganisation->name }}
                                    </a>
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                @if($slip->is_received == FALSE && $slip->is_approved == FALSE && $slip->is_integrated == FALSE)
                                    <span class="badge bg-secondary">Projet</span>
                                @elseif($slip->is_received == TRUE && $slip->is_approved == FALSE && $slip->is_integrated == FALSE)
                                    <span class="badge bg-primary">Examen</span>
                                @elseif ($slip->is_received == TRUE && $slip->is_approved == TRUE && $slip->is_integrated == FALSE )
                                    <span class="badge bg-warning">Approuvé</span>
                                @elseif ($slip->is_received == TRUE && $slip->is_approved == TRUE && $slip->is_integrated == TRUE)
                                    <span class="badge bg-success">Intégré</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item {{ $slips->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $slips->previousPageUrl() }}" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                @foreach ($slips->getUrlRange(1, $slips->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $slips->currentPage() ? 'active' : '' }}">
                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                    </li>
                @endforeach
                <li class="page-item {{ $slips->hasMorePages() ? '' : 'disabled' }}">
                    <a class="page-link" href="{{ $slips->nextPageUrl() }}" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

@endsection

@push('styles')

@endpush
