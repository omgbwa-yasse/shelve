@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Fiche descriptive</h1>
    <h2> {{ $record->code }} :  {{ $record->name }} ({{ $record->level->name }})</h2>

    <div class="accordion" id="recordDetailsAccordion">

        <div class="accordion-item">
            <h2 class="accordion-header" id="identificationHeading">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#identificationCollapse" aria-expanded="true" aria-controls="identificationCollapse">
                    Identification
                </button>
            </h2>
            <div id="identificationCollapse" class="accordion-collapse collapse show" aria-labelledby="identificationHeading" data-bs-parent="#recordDetailsAccordion">
                <div class="accordion-body">
                    <table class="table table-sm">
                        <tbody>
                            <table class="table table-sm">
                                <tbody>
                                    <tr>
                                        <th>cote</th>
                                        <td>{{ $record->code }}</td>
                                    </tr>
                                    <tr>
                                        <th>Intitulé</th>
                                        <td>{{ $record->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Date Format</th>
                                        <td>{{ $record->date_format }}</td>
                                    </tr>
                                    <tr>
                                        <th>Date de début</th>
                                        <td>{{ $record->date_start }}</td>
                                    </tr>
                                    <tr>
                                        <th>Date fin</th>
                                        <td>{{ $record->date_end }}</td>
                                    </tr>
                                    <tr>
                                        <th>Date Exact</th>
                                        <td>{{ $record->date_exact }}</td>
                                    </tr>
                                    <tr>
                                        <th>Niveau de description </th>
                                        <td>{{ $record->level->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Epaisseur </th>
                                        <td>{{ $record->width }} cm </td>
                                    </tr>
                                    <tr>
                                        <th>Description importance matériel </th>
                                        <td>{{ $record->width_description }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="contextHeading">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#contextCollapse" aria-expanded="false" aria-controls="contextCollapse">
                    Contexte
                </button>
            </h2>
            <div id="contextCollapse" class="accordion-collapse collapse" aria-labelledby="contextHeading" data-bs-parent="#recordDetailsAccordion">
                <div class="accordion-body">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <th>Biographical History</th>
                                <td>{{ $record->biographical_history }}</td>
                            </tr>
                            <tr>
                                <th>Archival History</th>
                                <td>{{ $record->archival_history }}</td>
                            </tr>
                            <tr>
                                <th>Acquisition Source</th>
                                <td>{{ $record->acquisition_source }}</td>
                            </tr>
                            <tr>
                                <th>Authors</th>
                                <td>
                                    @foreach ($record->authors as $author)
                                        <span class="badge badge-secondary">{{ $author->name }}</span>
                                    @endforeach
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>




        <div class="accordion-item">
            <h2 class="accordion-header" id="contentHeading">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#contentCollapse" aria-expanded="false" aria-controls="contentCollapse">
                    Contenu
                </button>
            </h2>
            <div id="contentCollapse" class="accordion-collapse collapse" aria-labelledby="contentHeading" data-bs-parent="#recordDetailsAccordion">
                <div class="accordion-body">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <th>Content</th>
                                <td>{{ $record->content }}</td>
                            </tr>
                            <tr>
                                <th>Appraisal</th>
                                <td>{{ $record->appraisal }}</td>
                            </tr>
                            <tr>
                                <th>Accrual</th>
                                <td>{{ $record->accrual }}</td>
                            </tr>
                            <tr>
                                <th>Arrangement</th>
                                <td>{{ $record->arrangement }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>




        <div class="accordion-item">
            <h2 class="accordion-header" id="accessConditionsHeading">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accessConditionsCollapse" aria-expanded="false" aria-controls="accessConditionsCollapse">
                    Condition d'accès
                </button>
            </h2>
            <div id="accessConditionsCollapse" class="accordion-collapse collapse" aria-labelledby="accessConditionsHeading" data-bs-parent="#recordDetailsAccordion">
                <div class="accordion-body">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <th>Access Conditions</th>
                                <td>{{ $record->access_conditions }}</td>
                            </tr>
                            <tr>
                                <th>Reproduction Conditions</th>
                                <td>{{ $record->reproduction_conditions }}</td>
                            </tr>
                            <tr>
                                <th>Language Material</th>
                                <td>{{ $record->language_material }}</td>
                            </tr>
                            <tr>
                                <th>Characteristic</th>
                                <td>{{ $record->characteristic }}</td>
                            </tr>
                            <tr>
                                <th>Finding Aids</th>
                                <td>{{ $record->finding_aids }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


        <div class="accordion-item">
            <h2 class="accordion-header" id="additionalSourcesHeading">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#additionalSourcesCollapse" aria-expanded="false" aria-controls="additionalSourcesCollapse">
                    Sources complémentaires
                </button>
            </h2>
            <div id="additionalSourcesCollapse" class="accordion-collapse collapse" aria-labelledby="additionalSourcesHeading" data-bs-parent="#recordDetailsAccordion">
                <div class="accordion-body">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <th>Location Original</th>
                                <td>{{ $record->location_original }}</td>
                            </tr>
                            <tr>
                                <th>Location Copy</th>
                                <td>{{ $record->location_copy }}</td>
                            </tr>
                            <tr>
                                <th>Related Unit</th>
                                <td>{{ $record->related_unit }}</td>
                            </tr>
                            <tr>
                                <th>Publication Note</th>
                                <td>{{ $record->publication_note }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>



        <div class="accordion-item">
            <h2 class="accordion-header" id="notesHeading">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#notesCollapse" aria-expanded="false" aria-controls="notesCollapse">
                    Notes
                </button>
            </h2>
            <div id="notesCollapse" class="accordion-collapse collapse" aria-labelledby="notesHeading" data-bs-parent="#recordDetailsAccordion">
                <div class="accordion-body">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <th>Note</th>
                                <td>{{ $record->note }}</td>
                            </tr>
                            <tr>
                                <th>Archivist Note</th>
                                <td>{{ $record->archivist_note }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


        <div class="accordion-item">
            <h2 class="accordion-header" id="descriptionControlHeading">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#descriptionControlCollapse" aria-expanded="false" aria-controls="descriptionControlCollapse">
                    Contrôle de description
                </button>
            </h2>
            <div id="descriptionControlCollapse" class="accordion-collapse collapse" aria-labelledby="descriptionControlHeading" data-bs-parent="#recordDetailsAccordion">
                <div class="accordion-body">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <th>Règle et convention</th>
                                <td>{{ $record->rule_convention }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>{{ $record->status->name }}</td>
                            </tr>
                            <tr>
                                <th>Support</th>
                                <td>{{ $record->support->name }}</td>
                            </tr>
                            <tr>
                                <th>Classe</th>
                                <td>{{ $record->activity->name }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>




        <div class="accordion-item">
            <h2 class="accordion-header" id="indexingHeading">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#indexingCollapse" aria-expanded="false" aria-controls="indexingCollapse">
                    Indexation
                </button>
            </h2>
            <div id="indexingCollapse" class="accordion-collapse collapse" aria-labelledby="indexingHeading" data-bs-parent="#recordDetailsAccordion">
                <div class="accordion-body">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <th>Fiche parent</th>
                                <td>{{ $record->parent ? $record->parent->name : '' }}</td>
                            </tr>
                            <tr>
                                <th>Boite de conservation</th>
                                <td>{{ $record->container ? $record->container->name : '' }}</td>
                            </tr>
                            <tr>
                                <th>Versement de provenance</th>
                                <td>{{ $record->accession ? $record->accession->name : '' }}</td>
                            </tr>
                            <tr>
                                <th>Créer par </th>
                                <td>{{ $record->user->name }}</td>
                            </tr>
                            <tr>
                                <th>Vedettes</th>
                                <td>
                                    @foreach ($record->terms as $term)
                                      <span class="badge badge-secondary"> {{ $term->name ?? '' }} </span>
                                    @endforeach
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>



        </div>

    <a href="{{ route('records.index') }}" class="btn btn-secondary btn-sm">Back</a>
    <a href="{{ route('records.edit', $record) }}" class="btn btn-sm btn-warning">Edit</a>
    <form action="{{ route('records.destroy', $record) }}" method="POST" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this record?')">Delete</button>
    </form>
    <hr>
    <a href="{{ route('records.index') }}" class="btn btn-secondary btn-sm">inserer dans une boite</a>
    <a href="{{ route('records.index') }}" class="btn btn-secondary btn-sm">Ajouter un notice fille </a>
</div>
@endsection
