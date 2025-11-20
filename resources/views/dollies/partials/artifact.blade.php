<div class="row mb-4">
    <div class="col-md-12">
        <div class="card action-card">
            <div class="card-body text-center">
                <i class="bi bi-gem action-icon text-warning"></i>
                <h5 class="card-title">Ajouter des Artefacts</h5>
                <p class="card-text">Ajoutez des artefacts de musée à ce chariot</p>
                <form action="{{ route('dolly.add-artifact', $dolly) }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <select name="artifact_id" class="form-select" required>
                            <option value="">-- Sélectionner un artefact --</option>
                            @foreach($artifacts as $artifact)
                                <option value="{{ $artifact->id }}">
                                    {{ $artifact->code }} - {{ $artifact->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-warning btn-action">
                        <i class="bi bi-plus-circle"></i> Ajouter
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
