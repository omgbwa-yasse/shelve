@extends('layouts.app')

@section('content')

    <div class="container"><h1>Record Details</h1>
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item"><a class="nav-link active" id="identification-tab" data-toggle="tab"
                                    href="#identification" role="tab" aria-controls="identification"
                                    aria-selected="true">Identification</a></li>
            <li class="nav-item"><a class="nav-link" id="contexte-tab" data-toggle="tab" href="#contexte" role="tab"
                                    aria-controls="contexte" aria-selected="false">Contexte</a></li>
            <li class="nav-item"><a class="nav-link" id="contenu-tab" data-toggle="tab" href="#contenu" role="tab"
                                    aria-controls="contenu" aria-selected="false">Contenu</a></li>
            <li class="nav-item"><a class="nav-link" id="condition-tab" data-toggle="tab" href="#condition" role="tab"
                                    aria-controls="condition" aria-selected="false">Condition d'accès</a></li>
            <li class="nav-item"><a class="nav-link" id="sources-tab" data-toggle="tab" href="#sources" role="tab"
                                    aria-controls="sources" aria-selected="false">Sources complémentaires</a></li>
            <li class="nav-item"><a class="nav-link" id="notes-tab" data-toggle="tab" href="#notes" role="tab"
                                    aria-controls="notes" aria-selected="false">Notes</a></li>
            <li class="nav-item"><a class="nav-link" id="controle-tab" data-toggle="tab" href="#controle" role="tab"
                                    aria-controls="controle" aria-selected="false">Contrôle de description</a></li>
            <li class="nav-item"><a class="nav-link" id="indexation-tab" data-toggle="tab" href="#indexation" role="tab"
                                    aria-controls="indexation" aria-selected="false">Indexation</a></li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="identification" role="tabpanel"
                 aria-labelledby="identification-tab">
                <table class="table">
                    <tbody>
                    <tr>
                        <th>Code</th>
                        <td>{{ $record->code }}</td>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <td>{{ $record->name }}</td>
                    </tr>
                    <tr>
                        <th>Date Format</th>
                        <td>{{ $record->date_format }}</td>
                    </tr>
                    <tr>
                        <th>Date Start</th>
                        <td>{{ $record->date_start }}</td>
                    </tr>
                    <tr>
                        <th>Date End</th>
                        <td>{{ $record->date_end }}</td>
                    </tr>
                    <tr>
                        <th>Date Exact</th>
                        <td>{{ $record->date_exact }}</td>
                    </tr>
                    <tr>
                        <th>Level</th>
                        <td>{{ $record->level->name }}</td>
                    </tr>
                    <tr>
                        <th>Width</th>
                        <td>{{ $record->width }}</td>
                    </tr>
                    <tr>
                        <th>Width Description</th>
                        <td>{{ $record->width_description }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="tab-pane fade" id="contexte" role="tabpanel" aria-labelledby="contexte-tab">
                <table class="table">
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
                    </tbody>
                </table>
            </div>
            <div class="tab-pane fade" id="contenu" role="tabpanel" aria-labelledby="contenu-tab">
                <table class="table">
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
            <div class="tab-pane fade" id="condition" role="tabpanel" aria-labelledby="condition-tab">
                <table class="table">
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
            <div class="tab-pane fade" id="sources" role="tabpanel" aria-labelledby="sources-tab">
                <table class="table">
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
            <div class="tab-pane fade" id="notes" role="tabpanel" aria-labelledby="notes-tab">
                <table class="table">
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
            <div class="tab-pane fade" id="controle" role="tabpanel" aria-labelledby="controle-tab">
                <table class="table">
                    <tbody>
                    <tr>
                        <th>Rule Convention</th>
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
                        <th>Activity</th>
                        <td>{{ $record->activity->name }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="tab-pane fade" id="indexation" role="tabpanel" aria-labelledby="indexation-tab">
                <table class="table">
                    <tbody>
                    <tr>
                        <th>Parent</th>
                        <td>{{ $record->parent ? $record->parent->name : '' }}</td>
                    </tr>
                    <tr>
                        <th>Container</th>
                        <td>{{ $record->container ? $record->container->name : '' }}</td>
                    </tr>
                    <tr>
                        <th>Accession</th>
                        <td>{{ $record->accession ? $record->accession->name : '' }}</td>
                    </tr>
                    <tr>
                        <th>User</th>
                        <td>{{ $record->user->name }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <a href="{{ route('records.index') }}" class="btn btn-secondary">Back</a></div>
@endsection
