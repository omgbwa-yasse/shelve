@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Import de thésaurus - Format RDF</h4>
                </div>

                <div class="card-body">
                    <div id="alert-container">
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
                    </div>

                    <form id="import-form" action="{{ route('thesaurus.import.rdf.process') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="file">Fichier RDF</label>
                            <input type="file" class="form-control" id="file" name="file" accept=".xml,.rdf,application/xml,application/rdf+xml,text/xml">
                            <small class="form-text text-muted">Choisissez un fichier RDF au format XML (extensions .xml ou .rdf)</small>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> L'import va traiter le fichier RDF et créer/mettre à jour les termes du thésaurus. Cette opération peut prendre un certain temps selon la taille du fichier.
                            <br>En cas de problème, consultez les logs dans <code>storage/logs/laravel.log</code> pour plus de détails.
                        </div>

                        <button type="submit" class="btn btn-primary" id="submit-btn">Importer</button>
                        <a href="{{ route('thesaurus.export-import') }}" class="btn btn-secondary">Annuler</a>
                    </form>

                    <hr>

                    <div class="mt-4">
                        <h5>Instructions</h5>
                        <p>
                            Pour importer un thésaurus au format RDF, vous devez préparer un fichier RDF qui respecte les conventions de vocabulaire contrôlé.
                            Le système traitera les concepts, leurs labels préférés et alternatifs, ainsi que les relations entre concepts.
                        </p>

                        <h6>Le système reconnaît les éléments suivants:</h6>
                        <ul>
                            <li><code>rdf:Description[rdf:type="skos:Concept"]</code> - Concepts/termes</li>
                            <li><code>skos:prefLabel</code> - Termes préférés (avec attribut xml:lang)</li>
                            <li><code>skos:altLabel</code> - Non-descripteurs/synonymes</li>
                            <li><code>skos:definition</code> - Définitions</li>
                            <li><code>skos:scopeNote</code> - Notes d'application</li>
                            <li><code>skos:historyNote</code> - Notes historiques</li>
                            <li><code>skos:editorialNote</code> - Notes éditoriales</li>
                            <li><code>skos:example</code> - Exemples d'utilisation</li>
                            <li><code>skos:broader</code> - Relation hiérarchique (terme générique)</li>
                            <li><code>skos:narrower</code> - Relation hiérarchique (terme spécifique)</li>
                            <li><code>skos:related</code> - Relation associative</li>
                            <li><code>skos:exactMatch, skos:closeMatch, etc.</code> - Alignements externes</li>
                        </ul>

                        <h6>Exemple de structure RDF:</h6>
                        <pre><code>&lt;?xml version="1.0" encoding="UTF-8"?>
&lt;rdf:RDF
  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
  xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
  xmlns:skos="http://www.w3.org/2004/02/skos/core#"
  xmlns:dc="http://purl.org/dc/elements/1.1/">

  &lt;rdf:Description rdf:about="http://example.com/concept/1">
    &lt;rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>
    &lt;skos:prefLabel xml:lang="fr">Terme préféré&lt;/skos:prefLabel>
    &lt;skos:altLabel xml:lang="fr">Non-descripteur&lt;/skos:altLabel>
    &lt;skos:definition xml:lang="fr">Définition du terme&lt;/skos:definition>
    &lt;skos:scopeNote xml:lang="fr">Note d'application&lt;/skos:scopeNote>
    &lt;skos:broader rdf:resource="http://example.com/concept/2"/>
    &lt;skos:narrower rdf:resource="http://example.com/concept/3"/>
    &lt;skos:related rdf:resource="http://example.com/concept/4"/>
    &lt;skos:exactMatch rdf:resource="http://external.com/concept/123"/>
  &lt;/rdf:Description>

  ...
&lt;/rdf:RDF></code></pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Nous utilisons maintenant l'approche traditionnelle non-AJAX -->
@endsection
