@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Import de thésaurus - Format SKOS</h4>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('thesaurus.import.skos.process') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="skos_file">Fichier SKOS (format RDF/XML)</label>
                            <input type="file" class="form-control" id="skos_file" name="skos_file" accept=".xml,.rdf">
                            <small class="form-text text-muted">Le fichier doit être au format SKOS/RDF (extension .xml ou .rdf)</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="import_mode">Mode d'import</label>
                            <select class="form-control" id="import_mode" name="import_mode">
                                <option value="add">Ajouter uniquement (ignorer les termes existants)</option>
                                <option value="update">Mettre à jour uniquement (ignorer les nouveaux termes)</option>
                                <option value="replace">Remplacer tout (supprimer le thésaurus existant)</option>
                            </select>
                        </div>

                        <div class="alert alert-warning">
                            <strong>Attention!</strong> Le mode "Remplacer tout" va supprimer l'ensemble des termes existants et leurs relations avant d'importer les nouveaux termes.
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-upload"></i> Importer
                            </button>
                            <a href="{{ route('terms.index') }}" class="btn btn-secondary">
                                Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5>Format SKOS/RDF attendu</h5>
                </div>
                <div class="card-body">
                    <p>Le fichier SKOS doit suivre la structure standard SKOS/RDF avec les éléments suivants :</p>
                    <pre class="bg-light p-3"><code>&lt;?xml version="1.0" encoding="UTF-8"?>
&lt;rdf:RDF
  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
  xmlns:skos="http://www.w3.org/2004/02/skos/core#"
  xmlns:dc="http://purl.org/dc/elements/1.1/">

  &lt;skos:Concept rdf:about="http://example.com/concept/1">
    &lt;skos:prefLabel xml:lang="fr">Terme préféré&lt;/skos:prefLabel>
    &lt;skos:altLabel xml:lang="fr">Non-descripteur&lt;/skos:altLabel>
    &lt;skos:definition xml:lang="fr">Définition du terme&lt;/skos:definition>
    &lt;skos:scopeNote xml:lang="fr">Note d'application&lt;/skos:scopeNote>
    &lt;skos:broader rdf:resource="http://example.com/concept/2"/>
    &lt;skos:narrower rdf:resource="http://example.com/concept/3"/>
    &lt;skos:related rdf:resource="http://example.com/concept/4"/>
    &lt;skos:exactMatch rdf:resource="http://external.com/concept/123"/>
    &lt;dc:type>approved&lt;/dc:type>
    &lt;dc:subject>Catégorie&lt;/dc:subject>
  &lt;/skos:Concept>

  ...
&lt;/rdf:RDF></code></pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
