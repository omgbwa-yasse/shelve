<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Liste des notices</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { font-size: 16px; margin-bottom: 5px; }
        .meta { color: #555; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>
    <h1>Liste des notices</h1>
    <div class="meta">Générée le {{ now()->format('d/m/Y H:i') }} — {{ count($records) }} notice(s)</div>

    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Nom</th>
                <th>Type</th>
                <th>Niveau</th>
                <th>Statut</th>
                <th>Activité</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
                <tr>
                    <td>{{ $record->code }}</td>
                    <td>{{ $record->name }}</td>
                    <td>{{ $record->type?->name }}</td>
                    <td>{{ $record->level?->name }}</td>
                    <td>{{ $record->status?->name }}</td>
                    <td>{{ $record->activity?->name }}</td>
                    <td>{{ $record->date_exact?->format('d/m/Y') ?: ($record->start_date?->format('d/m/Y') . ' — ' . $record->end_date?->format('d/m/Y')) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
