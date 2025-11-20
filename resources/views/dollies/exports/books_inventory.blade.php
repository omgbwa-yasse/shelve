<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventaire Livres - {{ $dolly->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; }
        h1 { text-align: center; color: #333; font-size: 18pt; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f0f0f0; padding: 6px; text-align: left; border: 1px solid #ddd; font-size: 9pt; }
        td { padding: 5px; border: 1px solid #ddd; font-size: 8pt; }
        .header { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Inventaire des Livres</h1>
        <p><strong>Chariot:</strong> {{ $dolly->name }}</p>
        <p><strong>Date:</strong> {{ date('d/m/Y H:i') }}</p>
        <p><strong>Nombre total:</strong> {{ $books->count() }} livres</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="12%">ISBN</th>
                <th width="35%">Titre</th>
                <th width="20%">Auteur</th>
                <th width="18%">Éditeur</th>
                <th width="8%">Année</th>
                <th width="7%">Pages</th>
            </tr>
        </thead>
        <tbody>
            @foreach($books as $book)
            <tr>
                <td>{{ $book->isbn ?? '-' }}</td>
                <td>{{ $book->title }}</td>
                <td>{{ $book->author ?? '-' }}</td>
                <td>{{ $book->publisher ?? '-' }}</td>
                <td>{{ $book->publication_year ?? '-' }}</td>
                <td>{{ $book->pages ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
