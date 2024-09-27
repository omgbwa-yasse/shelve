<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impression des enregistrements</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }
        .record {
            margin-bottom: 20px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }
        .record h2 {
            color: #333;
        }
        .record-info {
            margin-left: 20px;
        }
    </style>
</head>
<body>
<h1>Impression des enregistrements sélectionnés</h1>

@foreach($records as $record)
    <div class="record">
        <h2>{{ $record->code }} : {{ $record->name }}</h2>
        <div class="record-info">
            <p><strong>Contenu :</strong> {{ $record->content }}</p>
            <p><strong>Niveau de description :</strong> {{ $record->level->name ?? 'N/A' }}</p>
            <p><strong>Statut :</strong> {{ $record->status->name ?? 'N/A' }}</p>
            <p><strong>Support :</strong> {{ $record->support->name ?? 'N/A' }}</p>
            <p><strong>Activité :</strong> {{ $record->activity->name ?? 'N/A' }}</p>
            <p><strong>Dates :</strong> {{ $record->date_start ?? 'N/A' }} - {{ $record->date_end ?? 'N/A' }}</p>
            <p><strong>Contenant :</strong> {{ $record->container->name ?? 'Non conditionné' }}</p>
            <p><strong>Producteur :</strong> {{ $record->authors->pluck('name')->join(', ') ?? 'N/A' }}</p>
            <p><strong>Vedettes :</strong>
                @foreach($record->terms as $index => $term)
                    {{ $term->name ?? 'N/A' }}@if(!$loop->last), @endif
                @endforeach
            </p>
        </div>
    </div>
@endforeach
</body>
</html>
