<!-- Author Modal -->
<div class="modal fade" id="authorModal" tabindex="-1" aria-labelledby="authorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authorModalLabel">{{ __('manage_authors') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="select-tab" data-bs-toggle="tab" data-bs-target="#select-authors" type="button" role="tab">
                            {{ __('select_authors') }}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="add-tab" data-bs-toggle="tab" data-bs-target="#add-author" type="button" role="tab">
                            {{ __('add_new_author') }}
                        </button>
                    </li>
                </ul>

                <!-- Tab content -->
                <div class="tab-content">
                    <!-- Select Authors Tab -->
                    <div class="tab-pane fade show active" id="select-authors" role="tabpanel">
                        <div class="mb-3">
                            <div class="input-group">
                                <input type="text" id="author-search" class="form-control" placeholder="{{ __('search') }}">
                            </div>
                        </div>
                        <div class="list-group" id="author-list">
                            @foreach ($authors as $author)
                                <a href="#" class="list-group-item list-group-item-action" data-id="{{ $author->id }}">
                                    {{ $author->name }}
                                    <small class="text-muted">
                                        @if($author->authorType)
                                            ({{ $author->authorType->name }})
                                        @endif
                                    </small>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Add Author Tab -->
                    <div class="tab-pane fade" id="add-author" role="tabpanel">
                        <form id="author-form" action="{{ route('record-author.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="type_id" class="form-label">{{ __('type') }}</label>
                                <select id="type_id" name="type_id" class="form-control" required>
                                    @foreach ($authorTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">{{ __('name') }}</label>
                                <input type="text" id="name" name="name" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="parallel_name" class="form-label">{{ __('parallel_name') }}</label>
                                <input type="text" id="parallel_name" name="parallel_name" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label for="other_name" class="form-label">{{ __('other_name') }}</label>
                                <input type="text" id="other_name" name="other_name" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label for="lifespan" class="form-label">{{ __('lifespan') }}</label>
                                <input type="text" id="lifespan" name="lifespan" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label for="locations" class="form-label">{{ __('locations') }}</label>
                                <input type="text" id="locations" name="locations" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label for="parent_id" class="form-label">{{ __('parent_author') }}</label>
                                <select id="parent_id" name="parent_id" class="form-control">
                                    <option value="">{{ __('none') }}</option>
                                    @foreach ($parents as $parent)
                                        <option value="{{ $parent->id }}">
                                            {{ $parent->name }}
                                            @if($parent->authorType)
                                                ({{ $parent->authorType->name }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary">{{ __('create') }}</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('close') }}</button>
                <button type="button" class="btn btn-primary" id="save-authors">{{ __('save_selection') }}</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Author search functionality
    const authorSearch = document.getElementById('author-search');
    const authorList = document.getElementById('author-list');
    const authorItems = authorList.querySelectorAll('.list-group-item');

    authorSearch.addEventListener('input', function() {
        const searchText = this.value.toLowerCase();
        authorItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(searchText) ? '' : 'none';
        });
    });

    // Author selection
    authorItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            this.classList.toggle('active');
        });
    });

    // Save selected authors
    const saveAuthorsBtn = document.getElementById('save-authors');
    const selectedAuthorsDisplay = document.getElementById('selected-authors-display');
    const authorIdsInput = document.getElementById('author-ids');

    saveAuthorsBtn.addEventListener('click', function() {
        const selectedItems = authorList.querySelectorAll('.list-group-item.active');
        const selectedNames = Array.from(selectedItems).map(item => item.textContent.trim());
        const selectedIds = Array.from(selectedItems).map(item => item.dataset.id);

        selectedAuthorsDisplay.value = selectedNames.join('; ');
        authorIdsInput.value = selectedIds.join(',');

        bootstrap.Modal.getInstance(document.getElementById('authorModal')).hide();
    });

    // Handle author form submission
    const authorForm = document.getElementById('author-form');
    if (authorForm) {
        authorForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: new FormData(this),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    const result = await response.json();

                    // Reset form
                    this.reset();

                    // Refresh author list
                    await refreshAuthorList();

                    // Switch to select tab
                    const selectTab = document.getElementById('select-tab');
                    bootstrap.Tab.getInstance(selectTab).show();

                    // Show success message
                    alert('Author created successfully');
                } else {
                    const error = await response.json();
                    alert(error.message || 'Error creating author');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error creating author');
            }
        });
    }
});

async function refreshAuthorList() {
    try {
        const response = await fetch('{{ route("record-author.list") }}');
        const authors = await response.json();

        const authorList = document.getElementById('author-list');
        authorList.innerHTML = authors.map(author => `
            <a href="#" class="list-group-item list-group-item-action" data-id="${author.id}">
                ${author.name}
                ${author.author_type ? `<small class="text-muted">(${author.author_type.name})</small>` : ''}
            </a>
        `).join('');

        // Reattach event listeners
        const items = authorList.querySelectorAll('.list-group-item');
        items.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                this.classList.toggle('active');
            });
        });
    } catch (error) {
        console.error('Error refreshing author list:', error);
    }
}
</script>
