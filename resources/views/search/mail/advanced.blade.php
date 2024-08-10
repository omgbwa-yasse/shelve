<form action="{{ route('mails.search') }}" method="GET" class="mb-4">
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
                    <input type="text" name="name" class="form-control" placeholder="Objet">
                </div>
                <div class="col-md-6 mb-3">
                    <input type="text" name="author" class="form-control" placeholder="Auteur">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <input type="date" name="date" class="form-control" placeholder="Date">
                </div>
                <div class="col-md-6 mb-3">
                    <select name="mail_priority_id" class="form-control">
                        <option value="">Sélectionner une priorité</option>
                        @foreach($priorities as $priority)
                            <option value="{{ $priority->id }}">{{ $priority->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <select name="mail_type_id" class="form-control">
                        <option value="">Sélectionner un type</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <select name="mail_typology_id" class="form-control">
                        <option value="">Sélectionner une typologie</option>
                        @foreach($typologies as $typology)
                            <option value="{{ $typology->id }}">{{ $typology->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="author_search">Rechercher des auteurs</label>
                    <input type="text" id="author_search" class="form-control" placeholder="Taper pour rechercher...">
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <select id="author_id" class="form-select" multiple>
                        @foreach ($authors as $author)
                            <option value="{{ $author->id }}">{{ $author->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div id="selected-authors" class="mt-3"></div>
            <input type="hidden" name="author_ids" id="author-ids">
        </div>
    </div>
</form>
