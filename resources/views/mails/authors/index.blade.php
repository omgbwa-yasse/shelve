@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="card border-0 shadow-sm rounded-lg">
            <div class="card-header bg-primary text-white rounded-top d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Authors</h2>
                <!-- Search Input -->
                <div class="input-group" style="width: 300px;">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search authors...">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                </div>
            </div>
            <div class="card-body">
                <a href="{{ route('mail-author.create') }}" class="btn btn-primary mb-3">
                    <i class="bi bi-plus-circle"></i> Add New Author
                </a>

                @if ($message = Session::get('success'))
                    <div class="alert alert-success">
                        {{ $message }}
                    </div>
                @endif

                <div class="row" id="authorList">
                    @foreach ($authors as $author)
                        <div class="col-md-4 mb-4 author-card" data-name="{{ $author->name }}">
                            <div class="card h-100">
                                <div class="card-header d-flex align-items-center">
                                    <div class="avatar bg-primary text-white rounded-circle d-flex justify-content-center align-items-center mr-3"
                                         style="width: 50px; height: 50px; font-size: 24px;">
                                        {{ strtoupper(substr($author->name, 0, 1)) }}
                                        @if ($author->parallel_name)
                                            {{ strtoupper(substr($author->parallel_name, 0, 1)) }}
                                        @endif

                                    </div>
                                    <h5 class="card-title mb-0">{{ $author->name }}</h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">
                                        <strong>Parallel Name: </strong>{{ $author->parallel_name ?? 'N/A' }}<br>
                                        <strong>Other Name: </strong>{{ $author->other_name ?? 'N/A' }}<br>
                                        <strong>Lifespan: </strong>{{ $author->lifespan ?? 'N/A' }}<br>
                                        <strong>Locations: </strong>{{ $author->locations ?? 'N/A' }}<br>
                                    </p>
                                </div>
                                <div class="card-footer d-flex justify-content-between">
                                    <a href="{{ route('mail-author.show', $author) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> Show
                                    </a>
                                    <a href="{{ route('mails.sort') }}?categ=author&id={{$author->id}}" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-eye"></i> View Mails
                                    </a>
                                    <a href="{{ route('mail-author.edit', $author) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <form action="{{ route('mail-author.destroy', $author) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script>
        // JavaScript for search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            var input, filter, cards, card, i, txtValue;
            input = document.getElementById('searchInput');
            filter = input.value.toUpperCase();
            cards = document.getElementById('authorList').getElementsByClassName('author-card');

            for (i = 0; i < cards.length; i++) {
                card = cards[i];
                txtValue = card.getAttribute('data-name');
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    card.style.display = "";
                } else {
                    card.style.display = "none";
                }
            }
        });
    </script>
@endsection
