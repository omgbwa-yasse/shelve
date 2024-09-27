<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impression des communications</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .communication { margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        .communication h2 { color: #333; }
        .communication p { margin: 5px 0; }
    </style>
</head>
<body>
<h1>Fiches de communication</h1>
@foreach($communications as $communication)
    <div class="communication">
        <h2>{{ $communication->code }} : {{ $communication->name }}</h2>
        <p><strong>Contenu :</strong> {{ $communication->content }}</p>
        <p><strong>Demandeur :</strong> {{ $communication->user->name }} ({{ $communication->userOrganisation->name }})</p>
        <p><strong>Opérateur :</strong> {{ $communication->operator->name }} ({{ $communication->operatorOrganisation->name }})</p>
        <p><strong>Date de retour :</strong> {{ $communication->return_date }}</p>
        <p><strong>Date de retour effectif :</strong> {{ $communication->return_effective }}</p>
        <p><strong>Statut :</strong> {{ $communication->status->name }}</p>
        @if($communication->records->count() > 0)
            <h3>Enregistrements associés :</h3>
            <ul>
                @foreach($communication->records as $record)
                    <li>
                        {{ $record->content }}
                        @if($record->is_original) (Original) @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endforeach
</body>
</html>
