<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventaire Séries d'Éditeur - {{ $dolly->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; }
        h1 { text-align: center; color: #333; font-size: 18pt; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f0f0f0; padding: 8px; text-align: left; border: 1px solid #ddd; }
        td { padding: 6px; border: 1px solid #ddd; }
        .header { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Inventaire des Séries d'Éditeur</h1>
        <p><strong>Chariot:</strong> {{ $dolly->name }}</p>
        <p><strong>Date:</strong> {{ date('d/m/Y H:i') }}</p>
        <p><strong>Nombre total:</strong> {{ $series->count() }} séries</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="30%">Nom de la série</th>
                <th width="25%">Éditeur</th>
                <th width="15%">ISSN</th>
                <th width="15%">Nb. volumes</th>
                <th width="15%">Date création</th>
            </tr>
        </thead>
        <tbody>
            @foreach($series as $s)
            <tr>
                <td>{{ $s->name }}</td>
                <td>{{ $s->publisher->name ?? '-' }}</td>
                <td>{{ $s->issn ?? '-' }}</td>
                <td>{{ $s->books_count ?? 0 }}</td>
                <td>{{ $s->created_at->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
