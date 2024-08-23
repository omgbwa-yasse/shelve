@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h1 class="h2 mb-0"><b>Plan de classement</b></h1>
            </div>
            <div class="col-auto">
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                        <tr>
                            <th scope="col">Code</th>
                            <th scope="col">Nom</th>
                            <th scope="col">Observation</th>
                            <th scope="col">Parent</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($activities as $activity)
                            <tr>
                                <td>{{ $activity->code }}</td>
                                <td>{{ $activity->name }}</td>
                                <td>{{ $activity->observation }}</td>
                                <td>
                                    @if($activity->parent)
                                        <span class="badge bg-secondary">{{ $activity->parent->code }}</span>
                                        {{ $activity->parent->name }}
                                    @else
                                        <span class="badge bg-primary">Mission</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('activities.show', $activity->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-gear me-1"></i>Paramètres
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <p class="text-muted mb-0">Aucune activité trouvée.</p>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
