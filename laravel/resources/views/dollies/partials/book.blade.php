<div class="row mb-4">
    <div class="col-md-12">
        <div class="card action-card">
            <div class="card-body text-center">
                <i class="bi bi-book action-icon text-info"></i>
                <h5 class="card-title">Ajouter des Livres</h5>
                <p class="card-text">Ajoutez des livres à ce chariot</p>
                <form action="{{ route('dolly.add-book', $dolly) }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <select name="book_id" class="form-select" required>
                            <option value="">-- Sélectionner un livre --</option>
                            @foreach($books as $book)
                                <option value="{{ $book->id }}">
                                    {{ $book->isbn }} - {{ $book->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-info btn-action">
                        <i class="bi bi-plus-circle"></i> Ajouter
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
