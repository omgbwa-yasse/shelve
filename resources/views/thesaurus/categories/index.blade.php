@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Branches du thésaurus</h1>
        <div class="mb-3">
            <a href="{{ route('term-categories.create') }}" class="btn btn-primary btn-sm">Ajouter une branche</a>
        </div>
        <div class="mb-3">
            <input type="text" id="search" class="form-control" placeholder="Rechercher...">
        </div>
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="categories">
                @foreach ($categories as $category)
                    <tr>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->description ?? '' }}</td>
                        <td>
                            <a href="{{ route('term-categories.show', $category) }}" class="btn btn-primary btn-sm">Voir</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search');
            const categoriesTable = document.getElementById('categories');
            const rows = categoriesTable.getElementsByTagName('tr');

            searchInput.addEventListener('keyup', function() {
                const value = this.value.toLowerCase();

                for (let i = 0; i < rows.length; i++) {
                    const rowText = rows[i].textContent.toLowerCase();

                    if (rowText.indexOf(value) > -1) {
                        rows[i].style.display = '';
                    } else {
                        rows[i].style.display = 'none';
                    }
                }
            });
        });
    </script>
@endpush
