<div class="row mb-4">
    <div class="col-md-12">
        <div class="card action-card">
            <div class="card-body text-center">
                <i class="bi bi-file-earmark-text action-icon text-success"></i>
                <h5 class="card-title">Ajouter des Documents Numériques</h5>
                <p class="card-text">Ajoutez des documents numériques à ce chariot</p>
                <form action="{{ route('dolly.add-digital-document', $dolly) }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <select name="document_id" class="form-select" required>
                            <option value="">-- Sélectionner un document --</option>
                            @foreach($digitalDocuments as $document)
                                <option value="{{ $document->id }}">
                                    {{ $document->code }} - {{ $document->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success btn-action">
                        <i class="bi bi-plus-circle"></i> Ajouter
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
