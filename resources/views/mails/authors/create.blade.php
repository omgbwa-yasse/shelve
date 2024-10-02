@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="">
                <div class="">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0">Add New Author</h2>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('mail-author.store') }}" method="POST" id="authorForm">
                            @csrf
                            <div class="mb-3">
                                <label for="type_id" class="form-label">Type</label>
                                <select id="type_id" name="type_id" class="form-select" required>
                                    <option value="" disabled selected>Enter the type</option>
                                    @foreach ($authorTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" id="name" name="name" class="form-control" data-field="name" required>
                            </div>

                            <div class="mb-3">
                                <label for="parallel_name" class="form-label">Parallel Name</label>
                                <input type="text" id="parallel_name" name="parallel_name" class="form-control" data-field="parallel_name">
                            </div>

                            <div class="mb-3">
                                <label for="other_name" class="form-label">Other Name</label>
                                <input type="text" id="other_name" name="other_name" class="form-control" data-field="other_name">
                            </div>

                            <div class="mb-3">
                                <label for="lifespan" class="form-label">Lifespan</label>
                                <input type="text" id="lifespan" name="lifespan" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label for="locations" class="form-label">Locations</label>
                                <input type="text" id="locations" name="locations" class="form-control" data-field="locations">
                            </div>

                            <div class="mb-3">
                                <label for="parent_id" class="form-label">Parent Author</label>
                                <div class="input-group">
                                    <input type="text" id="parent_name" class="form-control" readonly>
                                    <input type="hidden" id="parent_id" name="parent_id">
                                    <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#authorModal">
                                        Select Author
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Create Author</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Author Selection Modal -->
    <div class="modal fade" id="authorModal" tabindex="-1" aria-labelledby="authorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="authorModalLabel">Select Parent Author</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="authorSearch" class="form-control mb-3" placeholder="Search authors...">
                    <ul id="authorList" class="list-group">
                        @foreach ($parents as $parent)
                            <li class="list-group-item" data-id="{{ $parent->id }}" data-name="{{ $parent->name }}">
                                {{ $parent->name }} <small class="text-muted">({{ $parent->authorType->name }})</small>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveAuthor">Save</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const authorSearch = document.getElementById('authorSearch');
            const authorList = document.getElementById('authorList');
            const parentIdInput = document.getElementById('parent_id');
            const parentNameInput = document.getElementById('parent_name');
            const saveAuthorBtn = document.getElementById('saveAuthor');
            const authorModal = new bootstrap.Modal(document.getElementById('authorModal'));

            let selectedAuthor = null;

            authorSearch.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const authors = authorList.querySelectorAll('li');

                authors.forEach(author => {
                    const authorName = author.textContent.toLowerCase();
                    if (authorName.includes(searchTerm)) {
                        author.style.display = '';
                    } else {
                        author.style.display = 'none';
                    }
                });
            });

            authorList.addEventListener('click', function(e) {
                if (e.target.tagName === 'LI') {
                    authorList.querySelectorAll('li').forEach(li => li.classList.remove('active'));
                    e.target.classList.add('active');
                    selectedAuthor = {
                        id: e.target.dataset.id,
                        name: e.target.dataset.name
                    };
                }
            });

            saveAuthorBtn.addEventListener('click', function() {
                if (selectedAuthor) {
                    parentIdInput.value = selectedAuthor.id;
                    parentNameInput.value = selectedAuthor.name;
                    authorModal.hide();
                }
            });
        });
    </script>
@endpush
