@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Record Details</h1>
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
            <tr>
                <th>Note</th>
                <td>{{ $record->note }}</td>
            </tr>
            <tr>
                <th>Archivist Note</th>
                <td>{{ $record->archivist_note }}</td>
            </tr>
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
    <a href="{{ route('records.index') }}" class="btn btn-secondary">Back</a>
</div>
@endsection
