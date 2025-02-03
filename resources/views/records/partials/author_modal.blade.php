<!-- Author Modal -->
<div class="modal fade" id="authorModal" tabindex="-1" aria-labelledby="authorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authorModalLabel">{{ __('select_authors') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="input-group">
                        <input type="text" id="author-search" class="form-control" placeholder="{{ __('search') }}">
                        <a href="{{ route('record-author.create') }}" class="btn btn-success" onclick="openAuthorForm(event)">
                            {{ __('add_new') }}
                        </a>
                    </div>
                </div>
                <div class="list-group" id="author-list">
                    @foreach ($authors as $author)
                        <a href="#" class="list-group-item list-group-item-action" data-id="{{ $author->id }}">
                            {{ $author->name }}
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('close') }}</button>
                <button type="button" class="btn btn-primary" id="save-authors">{{ __('save') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Add the following JavaScript to handle the author form -->
<script>
function openAuthorForm(event) {
    event.preventDefault();
    const url = event.target.href;

    // Store the current modal instance
    const currentModal = bootstrap.Modal.getInstance(document.getElementById('authorModal'));
    currentModal.hide();

    // Open the author form in a new modal
    const authorFormModal = new bootstrap.Modal(document.createElement('div'));

    fetch(url)
        .then(response => response.text())
        .then(html => {
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = html;

            // Extract the form content
            const formContent = tempContainer.querySelector('.container').innerHTML;

            // Create new modal with the form
            const modalHtml = `
                <div class="modal fade" id="authorFormModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">{{ __('add_new_author') }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                ${formContent}
                            </div>
                        </div>
                    </div>
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', modalHtml);

            // Show the form modal
            const formModal = new bootstrap.Modal(document.getElementById('authorFormModal'));
            formModal.show();

            // Handle form submission
            const form = document.querySelector('#authorFormModal form');
            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    if (response.ok) {
                        // Close the form modal
                        formModal.hide();

                        // Refresh the author list and show the author modal again
                        await refreshAuthorList();
                        currentModal.show();
                    } else {
                        // Handle errors
                        console.error('Form submission failed');
                    }
                } catch (error) {
                    console.error('Error submitting form:', error);
                }
            });
        });
}






async function refreshAuthorList() {
    try {
        const response = await fetch('{{ route("record-author.list") }}');
        const authors = await response.json();

        const authorList = document.getElementById('author-list');
        authorList.innerHTML = authors.map(author => `
            <a href="#" class="list-group-item list-group-item-action" data-id="${author.id}">
                ${author.name}
            </a>
        `).join('');

        // Reattach event listeners to new items
        const items = authorList.querySelectorAll('.list-group-item');
        items.forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                item.classList.toggle('active');
            });
        });
    } catch (error) {
        console.error('Error refreshing author list:', error);
    }
}
</script>
