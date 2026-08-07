@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1><i class="bi bi-search"></i> Dossiers éligibles à l'élimination</h1>
        <p class="text-muted">Sort final = Élimination, délai de rétention écoulé, non présents dans une liste de déclassement en cours.</p>

        <form method="GET" class="row mb-3">
            <div class="col-md-4">
                <select name="activity_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Toutes les activités</option>
                    @foreach($activities as $activity)
                        <option value="{{ $activity->id }}" {{ (string) $activityId === (string) $activity->id ? 'selected' : '' }}>
                            {{ $activity->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Nom</th>
                        <th>Activité</th>
                        <th>Dates</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        <tr>
                            <td>{{ $record->code }}</td>
                            <td>{{ $record->name }}</td>
                            <td>{{ $record->activity->name ?? '' }}</td>
                            <td>{{ $record->date_start }} - {{ $record->date_end }}</td>
                            <td>{{ $record->status->name ?? '' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Aucun dossier éligible.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $records->links() }}
    </div>
@endsection
