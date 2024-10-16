@extends('layouts.app')


@section('content')
    <div class="container-fluid py-4">
        <div class="">
            <div class=" d-flex justify-content-between align-items-center py-3">
                <h2 class="mb-0 font-weight-bold">Auteurs</h2>
                <div class="input-group input-group-merge" style="width: 300px;">
                    <input type="text" id="searchInput" class="form-control" placeholder="Rechercher des auteurs...">
                    <div class="input-group-append">
                        <span class="input-group-text bg-transparent border-0">
                            <i class="bi bi-search search-icon"></i>
                        </span>
                    </div>
                </div>
            </div>
            <hr>
            <div class="card-body">
                <div class="mb-4">
                    <a href="{{ route('mail-author.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i> Ajouter un nouvel Auteur
                    </a>
                </div>

                @if ($message = Session::get('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ $message }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="row g-4" id="authorList">
                    @foreach ($authors as $author)
                        <div class="col-md-4 mb-4 author-card" data-name="{{ $author->name }}">
                            <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar bg-primary text-white rounded-circle d-flex justify-content-center align-items-center me-3"
                                             style="width: 60px; height: 60px; font-size: 24px;">
                                            {{ strtoupper(substr($author->name, 0, 1)) }}
                                            @if ($author->parallel_name)
                                                {{ strtoupper(substr($author->parallel_name, 0, 1)) }}
                                            @endif
                                        </div>
                                        <div>
                                            <h5 class="card-title mb-0">{{ $author->name }}</h5>
                                            <small class="text-muted">{{ $author->parallel_name ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                    <p class="card-text">
                                        <strong>Autre nom :</strong> {{ $author->other_name ?? 'N/A' }}<br>
                                        <strong>Durée de vie :</strong> {{ $author->lifespan ?? 'N/A' }}<br>
                                        <strong>Lieux :</strong> {{ $author->locations ?? 'N/A' }}
                                    </p>
                                </div>
                                <div class="card-footer bg-transparent border-top-0">
                                    <div class="d-flex justify-content-between">
                                        <a href="{{ route('mail-author.show', $author) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i> Voir
                                        </a>
                                        <a href="{{ route('mails.sort') }}?categ=author&id={{$author->id}}" class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-envelope me-1"></i> Voir les Mails
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const authorList = document.getElementById('authorList');

            // Search functionality
            searchInput.addEventListener('input', function() {
                const filter = this.value.toUpperCase();
                const cards = authorList.getElementsByClassName('author-card');

                for (let card of cards) {
                    const txtValue = card.getAttribute('data-name');
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        card.style.display = "";
                    } else {
                        card.style.display = "none";
                    }
                }
            });
        });
    </script>
@endpush
