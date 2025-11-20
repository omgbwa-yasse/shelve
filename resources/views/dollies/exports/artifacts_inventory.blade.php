<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventaire Artefacts - {{ $dolly->name }}</title>
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
        <h1>Inventaire des Artefacts</h1>
        <p><strong>Chariot:</strong> {{ $dolly->name }}</p>
        <p><strong>Description:</strong> {{ $dolly->description }}</p>
        <p><strong>Date:</strong> {{ date('d/m/Y H:i') }}</p>
        <p><strong>Nombre total:</strong> {{ $artifacts->count() }} artefacts</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="10%">Code</th>
                <th width="30%">Nom</th>
                <th width="15%">Type</th>
                <th width="30%">Description</th>
                <th width="15%">Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($artifacts as $artifact)
            <tr>
                <td>{{ $artifact->code }}</td>
                <td>{{ $artifact->name }}</td>
                <td>{{ $artifact->type ?? '-' }}</td>
                <td>{{ $artifact->description ?? '-' }}</td>
                <td>{{ $artifact->created_at->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
