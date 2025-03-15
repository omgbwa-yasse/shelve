@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('Thesaurus Branches') }}</h1>

    <a href="{{ route('term-categories.create') }}" class="btn btn-primary btn-sm">{{ __('Add Branch') }}</a>

    <div class="mt-3">
        <input type="text" id="search" class="form-control" placeholder="{{ __('Search...') }}">
    </div>

    <div class="mt-3">
        <div id="tree">
            @foreach($categories as $category)
                @include('thesaurus.categories.partials.category-item', ['category' => $category])
            @endforeach
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Description') }}</th>
                <th>{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $category)
                <tr>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->description }}</td>
                    <td>
                        <a href="{{ route('term-categories.show', $category) }}" class="btn btn-primary btn-sm">{{ __('View') }}</a>
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
