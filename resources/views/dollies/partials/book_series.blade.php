<div class="row mb-4">
    <div class="col-md-12">
        <div class="card action-card">
            <div class="card-body text-center">
                <i class="bi bi-collection action-icon text-secondary"></i>
                <h5 class="card-title">Ajouter des Séries d'Éditeur</h5>
                <p class="card-text">Ajoutez des séries de livres à ce chariot</p>
                <form action="{{ route('dolly.add-book-series', $dolly) }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <select name="series_id" class="form-select" required>
                            <option value="">-- Sélectionner une série --</option>
                            @foreach($bookSeries as $series)
                                <option value="{{ $series->id }}">
                                    {{ $series->publisher->name }} - {{ $series->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-secondary btn-action">
                        <i class="bi bi-plus-circle"></i> Ajouter
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
