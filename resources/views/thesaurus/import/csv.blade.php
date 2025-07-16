@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Import de thésaurus - Format CSV</h4>
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

                    <form action="{{ route('thesaurus.import.csv.process') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="csv_file">Fichier CSV</label>
                            <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv,.txt">
                            <small class="form-text text-muted">Le fichier doit être au format CSV avec un en-tête</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="delimiter">Délimiteur</label>
                            <select class="form-control" id="delimiter" name="delimiter">
                                <option value="comma">Virgule (,)</option>
                                <option value="semicolon">Point-virgule (;)</option>
                                <option value="tab">Tabulation</option>
                            </select>
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
                            <a href="{{ route('thesaurus.index') }}" class="btn btn-secondary">
                                Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5>Format CSV attendu</h5>
                </div>
                <div class="card-body">
                    <p>Le fichier CSV doit contenir les colonnes suivantes :</p>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nom de colonne</th>
                                <th>Description</th>
                                <th>Obligatoire</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>ID</td>
                                <td>Identifiant unique du terme</td>
                                <td>Non</td>
                            </tr>
                            <tr>
                                <td>Terme préféré</td>
                                <td>Libellé principal du terme</td>
                                <td>Oui</td>
                            </tr>
                            <tr>
                                <td>Langue</td>
                                <td>Code de langue (fr, en, etc.)</td>
                                <td>Oui</td>
                            </tr>
                            <tr>
                                <td>Catégorie</td>
                                <td>Catégorie du terme</td>
                                <td>Non</td>
                            </tr>
                            <tr>
                                <td>Statut</td>
                                <td>approved, candidate ou deprecated</td>
                                <td>Non</td>
                            </tr>
                            <tr>
                                <td>Est Top Terme</td>
                                <td>Oui/Non ou 1/0</td>
                                <td>Non</td>
                            </tr>
                            <tr>
                                <td>Définition</td>
                                <td>Définition du terme</td>
                                <td>Non</td>
                            </tr>
                            <tr>
                                <td>Note d'application</td>
                                <td>Notes sur l'utilisation du terme</td>
                                <td>Non</td>
                            </tr>
                            <tr>
                                <td>Note historique</td>
                                <td>Notes sur l'historique du terme</td>
                                <td>Non</td>
                            </tr>
                            <tr>
                                <td>Note éditoriale</td>
                                <td>Notes pour les éditeurs du thésaurus</td>
                                <td>Non</td>
                            </tr>
                            <tr>
                                <td>Exemple</td>
                                <td>Exemples d'utilisation du terme</td>
                                <td>Non</td>
                            </tr>
                            <tr>
                                <td>Termes génériques</td>
                                <td>Liste de termes génériques séparés par ;</td>
                                <td>Non</td>
                            </tr>
                            <tr>
                                <td>Termes spécifiques</td>
                                <td>Liste de termes spécifiques séparés par ;</td>
                                <td>Non</td>
                            </tr>
                            <tr>
                                <td>Termes associés</td>
                                <td>Liste de termes associés séparés par ;</td>
                                <td>Non</td>
                            </tr>
                            <tr>
                                <td>Non-descripteurs</td>
                                <td>Liste de non-descripteurs séparés par ;</td>
                                <td>Non</td>
                            </tr>
                            <tr>
                                <td>Alignements externes</td>
                                <td>Format: "type: vocabulaire - uri" séparés par ;</td>
                                <td>Non</td>
                            </tr>
                        </tbody>
                    </table>

                    <p class="mt-3">Exemple de format pour les alignements externes : </p>
                    <pre class="bg-light p-2">exactMatch: wikidata - http://www.wikidata.org/entity/Q12345; closeMatch: geonames - http://www.geonames.org/123456</pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
