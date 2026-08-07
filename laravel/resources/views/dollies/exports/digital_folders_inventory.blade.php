<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventaire Dossiers Numériques - {{ $dolly->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; }
        h1 { text-align: center; color: #333; font-size: 18pt; }
        h2 { color: #666; font-size: 14pt; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f0f0f0; padding: 8px; text-align: left; border: 1px solid #ddd; }
        td { padding: 6px; border: 1px solid #ddd; }
        .header { margin-bottom: 20px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8pt; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Inventaire des Dossiers Numériques</h1>
        <p><strong>Chariot:</strong> {{ $dolly->name }}</p>
        <p><strong>Description:</strong> {{ $dolly->description }}</p>
        <p><strong>Date:</strong> {{ date('d/m/Y H:i') }}</p>
        <p><strong>Nombre total:</strong> {{ $folders->count() }} dossiers</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="10%">Code</th>
                <th width="30%">Nom</th>
                <th width="40%">Description</th>
                <th width="20%">Date de création</th>
            </tr>
        </thead>
        <tbody>
            @foreach($folders as $folder)
            <tr>
                <td>{{ $folder->code }}</td>
                <td>{{ $folder->name }}</td>
                <td>{{ $folder->description ?? '-' }}</td>
                <td>{{ $folder->created_at->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Document généré le {{ date('d/m/Y à H:i') }} - Page {PAGENO} sur {nbpg}</p>
    </div>
</body>
</html>
