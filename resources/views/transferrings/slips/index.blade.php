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
                                    <a href="{{ route('slips.show', $slip->id) }}"  title="View">
                                        <b>{{ $slip->code }} : {{ $slip->name }}</b>
                                        @if($slip->is_received  == FALSE && $slip->is_approved  == FALSE && $slip->is_integrated  == FALSE)
                                            <span class="badge rounded-pill text-bg-secondary">Projet</span>
                                        @elseif($slip->is_received  == TRUE && $slip->is_approved  == FALSE && $slip->is_integrated  == FALSE)
                                            <span class="badge rounded-pill text-bg-primary">Examen</span>
                                        @elseif ($slip->is_received  == TRUE && $slip->is_approved  == TRUE && $slip->is_integrated  == FALSE )
                                            <span class="badge rounded-pill text-bg-warning">Approuvé</span>
                                        @elseif ($slip->is_received  == TRUE && $slip->is_approved  == TRUE && $slip->is_integrated  == FALSE)
                                            <span class="badge rounded-pill text-bg-success">Intégré</span>
                                        @endif

                                    </a>
                                </h5>
                                <p class="card-text mb-1">
                                    Description : {{ $slip->description }}<br>
                                </p>
                                <p class="card-text mb-1">
                                     Du :<strong>
                                        <a href="{{ route('slips-sort')}}?categ=dates&date_exact={{ $slip->created_at->format('Y-m-d') }}">
                                            {{ $slip->created_at->format('Y-m-d') }}</strong>
                                        </a><br>
                                </p>
                                <p class="card-text mb-1">
                                     Service versant :
                                     <strong><a href="{{ route('slips-sort')}}?categ=user-organisation&id={{ $slip->userOrganisation->id }}">
                                        {{ $slip->userOrganisation->name }}<br>
                                    </a></strong>
                                </p>
                            </div>
                            <div class="col-md-3 text-md-end text-center">
                                <div class="d-flex justify-content-md-end justify-content-center align-items-center">
                                    <div class="btn-group" role="group">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>


    <footer class="bg-light py-3">
        <div class="container">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <li class="page-item {{ $slips->currentPage() == 1 ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $slips->previousPageUrl() }}" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    @for ($i = 1; $i <= $slips->lastPage(); $i++)
                        <li class="page-item {{ $slips->currentPage() == $i ? 'active' : '' }}">
                            <a class="page-link" href="{{ $slips->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor
                    <li class="page-item {{ $slips->currentPage() == $slips->lastPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $slips->nextPageUrl() }}" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </footer>



@endsection
