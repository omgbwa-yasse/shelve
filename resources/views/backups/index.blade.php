@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Liste des Sauvegardes</h1>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Statut</th>
                    <th>Utilisateur</th>
                    <th>Taille</th>
                    <th>Fichier de sauvegarde</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($backups as $backup)
                    <tr>
                        <td>{{ $backup->date_time }}</td>
                        <td>{{ ucfirst($backup->type) }}</td>
                        <td>{{ $backup->description ?? 'Aucune description' }}</td>
                        <td>{{ ucfirst($backup->status) }}</td>
                        <td>{{ $backup->user ? $backup->user->name : 'Utilisateur inconnu' }}</td>
                        <td>{{ number_format($backup->size / 1024, 2) }} KB</td>
                        <td>
                            @if (Storage::exists($backup->backup_file))
                                <a href="{{ Storage::url($backup->backup_file) }}" target="_blank">{{ $backup->backup_file }}</a>
                            @else
                                Fichier non disponible
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('backups.show', $backup->id) }}" class="btn btn-sm btn-info">Voir</a>
                        </td>
                    </tr>
                    <!-- Afficher les fichiers et les plannings de la sauvegarde -->
                    @if($backup->backupFiles->isNotEmpty())
                        <tr>
                            <td colspan="8">
                                <strong>Fichiers de sauvegarde :</strong>
                                <ul>
                                    @foreach ($backup->backupFiles as $file)
                                        <li>{{ $file->path_original }} ({{ number_format($file->size / 1024, 2) }} KB)</li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                    @endif

                    @if($backup->backupPlannings->isNotEmpty())
                        <tr>
                            <td colspan="8">
                                <strong>Plannings de sauvegarde :</strong>
                                <ul>
                                    @foreach ($backup->backupPlannings as $planning)
                                        <li>Fréquence : {{ $planning->frequence }} - Jour de la semaine : {{ $planning->week_day ?? 'N/A' }} - Jour du mois : {{ $planning->month_day ?? 'N/A' }} - Heure : {{ $planning->hour ?? 'N/A' }}</li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
