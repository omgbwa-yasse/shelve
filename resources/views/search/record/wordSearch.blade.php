@extends('layouts.app')
@section('content')
<div class="container">
    <h1>Sélectionner un terme du thésaurus</h1>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Notation</th>
                <th>URI</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($terms as $term)
                <tr>
                    <td>{{ $term->id }}</td>
                    <td>
                        <a href="{{ route('records.sort')}}?categ=term&id={{ $term->id }}">
                            {{ $term->preferred_label ?? $term->uri }}
                        </a>
                    </td>
                    <td>{{ $term->notation ?? '-' }}</td>
                    <td>{{ Str::limit($term->uri, 50) }}</td>
                    <td>{{ $term->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pagination -->
    <nav aria-label="Page navigation">
        <ul class="pagination">
            <li class="page-item {{ $terms->onFirstPage() ? 'disabled' : '' }}">
                <a class="page-link" href="{{ $terms->previousPageUrl() }}" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            @for ($i = 1; $i <= $terms->lastPage(); $i++)
                <li class="page-item {{ $terms->currentPage() == $i ? 'active' : '' }}">
                    <a class="page-link" href="{{ $terms->url($i) }}">{{ $i }}</a>
                </li>
            @endfor
            <li class="page-item {{ $terms->hasMorePages() ? '' : 'disabled' }}">
                <a class="page-link" href="{{ $terms->nextPageUrl() }}" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>

</div>
@endsection
