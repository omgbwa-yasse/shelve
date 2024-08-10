<form action="{{ route('records.search') }}" method="GET" class="mb-4">
    <div class="row">
        <div class="col-md-6">
            <input type="text" name="code" class="form-control" placeholder="Recherche par code">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary">Rechercher</button>
        </div>
        <div class="col-md-3">
            <button type="button" class="btn btn-secondary" data-toggle="collapse" data-target="#advancedSearch">Recherche avancée</button>
        </div>
    </div>

    <div class="collapse mt-3" id="advancedSearch">
        <div class="card card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <input type="text" name="name" class="form-control" placeholder="Nom">
                </div>
                <div class="col-md-3 mb-3">
                    <input type="date" name="date_start" class="form-control" placeholder="Date de début">
                </div>
                <div class="col-md-3 mb-3">
                    <input type="date" name="date_end" class="form-control" placeholder="Date de fin">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <input type="text" name="description" class="form-control" placeholder="Description">
                </div>
                <div class="col-md-6 mb-3">
                    <input type="text" name="language" class="form-control" placeholder="Langue">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <input type="text" name="author" class="form-control" placeholder="Auteur">
                </div>
                <div class="col-md-6 mb-3">
                    <select name="status" class="form-control">
                        <option value="">Sélectionner un statut</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="term_search">Rechercher des termes</label>
                    <input type="text" id="term_search" class="form-control" placeholder="Taper pour rechercher...">
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <select id="term_id" class="form-select" multiple>
                        @foreach ($terms as $term)
                            <option value="{{ $term->id }}">{{ $term->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div id="selected-terms" class="mt-3"></div>
            <input type="hidden" name="term_ids" id="term-ids">
        </div>
    </div>
</form>
