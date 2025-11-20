<div class="row mb-4">
    <div class="col-md-12">
        <div class="card action-card">
            <div class="card-body text-center">
                <i class="bi bi-folder-plus action-icon text-primary"></i>
                <h5 class="card-title">Ajouter des Dossiers Numériques</h5>
                <p class="card-text">Ajoutez des dossiers numériques à ce chariot</p>
                <form action="{{ route('dolly.add-digital-folder', $dolly) }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <select name="folder_id" class="form-select" required>
                            <option value="">-- Sélectionner un dossier --</option>
                            @foreach($digitalFolders as $folder)
                                <option value="{{ $folder->id }}">
                                    {{ $folder->code }} - {{ $folder->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-action">
                        <i class="bi bi-plus-circle"></i> Ajouter
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
