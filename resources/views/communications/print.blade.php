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
        .record { margin-left: 20px; border-left: 2px solid #eee; padding-left: 10px; }
    </style>
</head>
<body>
<h1>Fiches de communication</h1>
@foreach($communications as $communication)
    <div class="communication">
        <h2>{{ $communication->code }} : {{ $communication->name }}</h2>
        <p><strong>Contenu :</strong> {{ $communication->content }}</p>
        <p><strong>Demandeur :</strong> {{ $communication->user->name??'N/A' }} ({{ $communication->userOrganisation->name??'N/A' }})</p>
        <p><strong>Opérateur :</strong> {{ $communication->operator->name??'N/A' }} ({{ $communication->operatorOrganisation->name ??'N/A'}})</p>
        <p><strong>Date de retour :</strong> {{ $communication->return_date??'N/A' }}</p>
        <p><strong>Date de retour effectif :</strong> {{ $communication->return_effective??'N/A' }}</p>
        <p><strong>Statut :</strong> {{ $communication->status->name??'N/A' }}</p>

        @if($communication->records->count() > 0)
            <h3>Enregistrements associés :</h3>
            @foreach($communication->records as $communicationRecord)
                <div class="record">
                    <h4>{{ $communicationRecord->record->code??'N/A' }} : {{ $communicationRecord->record->name??'N/A' }}</h4>
                    <p><strong>Contenu :</strong> {{ $communicationRecord->content??'N/A' }}</p>
                    <p><strong>Est original :</strong> {{ $communicationRecord->is_original ? 'Oui' : 'Non' }}</p>
                    <p><strong>Niveau :</strong> {{ $communicationRecord->record->level->name ?? 'N/A' }}</p>
                    <p><strong>Support :</strong> {{ $communicationRecord->record->support->name ?? 'N/A' }}</p>
                    <p><strong>Statut :</strong> {{ $communicationRecord->record->status->name ?? 'N/A' }}</p>

                </div>
            @endforeach
        @endif
    </div>
@endforeach
</body>
</html>
