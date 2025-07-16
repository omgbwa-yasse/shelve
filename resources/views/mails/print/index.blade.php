<!DOCTYPE html>
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Impression des courriers</title>
    <style>
        body {
            font-family: dejavusans, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #000000;
            margin: 0;
            padding: 15px;
        }

        h1 {
            text-align: center;
            font-size: 14pt;
            color: #000000;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 0.5px solid #000000;
        }

        .mail {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 0.5px solid #cccccc;
            page-break-inside: avoid;
        }

        .mail h2 {
            background-color: #f5f5f5;
            padding: 5px;
            margin: 0 0 10px 0;
            font-size: 12pt;
            color: #000000;
        }

        .mail-info {
            margin-left: 10px;
        }

        .mail-info p {
            margin: 5px 0;
        }

        .mail-info strong {
            display: inline-block;
            width: 150px;
            font-weight: bold;
        }

        .header-info {
            margin-bottom: 20px;
            text-align: center;
            font-size: 9pt;
            color: #666666;
        }

        .summary {
            background-color: #f0f0f0;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #cccccc;
        }

        .summary h3 {
            margin: 0 0 10px 0;
            font-size: 12pt;
        }

        .page-break {
            page-break-before: always;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8pt;
            color: white;
            border-radius: 3px;
            margin-left: 5px;
        }

        .badge-danger { background-color: #dc3545; }
        .badge-primary { background-color: #007bff; }
        .badge-secondary { background-color: #6c757d; }
        .badge-success { background-color: #28a745; }
        .badge-warning { background-color: #ffc107; color: #000; }

        .text-muted {
            color: #666666;
        }

        .section {
            margin-bottom: 10px;
        }

        .flex-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .flex-column {
            flex: 1;
            margin-right: 20px;
        }

        .flex-column:last-child {
            margin-right: 0;
        }
    </style>
</head>
<body>
    <div class="header-info">
        <strong>Impression des courriers - {{ $generatedAt }}</strong><br>
        Total: {{ $totalCount }} courrier(s) sélectionné(s)
    </div>

    <h1>Liste des Courriers</h1>

    <div class="summary">
        <h3>Résumé</h3>
        <p><strong>Nombre total:</strong> {{ $totalCount }} courrier(s)</p>
        <p><strong>Date d'impression:</strong> {{ $generatedAt }}</p>
    </div>

    @foreach($mails as $index => $mail)
        <div class="mail {{ $index > 0 && $index % 3 == 0 ? 'page-break' : '' }}">
            <h2>
                {{ $mail->code ?? 'N/A' }} - {{ $mail->name ?? 'N/A' }}
                @if($mail->priority && $mail->priority->level === 'high')
                    <span class="badge badge-danger">URGENT</span>
                @endif
                @if($mail->action)
                    <span class="badge badge-danger">{{ $mail->action->name }}</span>
                @endif
                @if($mail->priority)
                    <span class="badge badge-{{ $mail->priority->color ?? 'secondary' }}">{{ $mail->priority->name }}</span>
                @endif
            </h2>

            <div class="mail-info">
                <div class="flex-row">
                    <div class="flex-column">
                        <div class="section">
                            <p><strong>Code:</strong> {{ $mail->code ?? 'N/A' }}</p>
                            <p><strong>Date:</strong> {{ $mail->date ? \Carbon\Carbon::parse($mail->date)->format('d/m/Y') : 'N/A' }}</p>
                            @if($mail->description)
                                <p><strong>Description:</strong> {{ Str::limit($mail->description, 100) }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex-column">
                        <div class="section">
                            @if($mail->sender)
                                <p><strong>Expéditeur:</strong> {{ $mail->sender->name ?? 'N/A' }}</p>
                                @if($mail->senderOrganisation)
                                    <p class="text-muted">Organisation: {{ $mail->senderOrganisation->name }}</p>
                                @endif
                            @elseif($mail->externalSender)
                                <p><strong>Expéditeur:</strong> {{ $mail->externalSender->first_name }} {{ $mail->externalSender->last_name }}</p>
                                @if($mail->externalSenderOrganization)
                                    <p class="text-muted">Organisation: {{ $mail->externalSenderOrganization->name }}</p>
                                @endif
                            @elseif($mail->externalSenderOrganization)
                                <p><strong>Expéditeur:</strong> {{ $mail->externalSenderOrganization->name }}</p>
                            @endif

                            @if($mail->recipient)
                                <p><strong>Destinataire:</strong> {{ $mail->recipient->name ?? 'N/A' }}</p>
                                @if($mail->recipientOrganisation)
                                    <p class="text-muted">Organisation: {{ $mail->recipientOrganisation->name }}</p>
                                @endif
                            @elseif($mail->externalRecipient)
                                <p><strong>Destinataire:</strong> {{ $mail->externalRecipient->first_name }} {{ $mail->externalRecipient->last_name }}</p>
                                @if($mail->externalRecipientOrganization)
                                    <p class="text-muted">Organisation: {{ $mail->externalRecipientOrganization->name }}</p>
                                @endif
                            @elseif($mail->externalRecipientOrganization)
                                <p><strong>Destinataire:</strong> {{ $mail->externalRecipientOrganization->name }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="section">
                    @if($mail->typology)
                        <p><strong>Typologie:</strong> {{ $mail->typology->name }}</p>
                    @endif

                    @if($mail->containers && $mail->containers->count() > 0)
                        <p><strong>Archives:</strong> {{ $mail->containers->count() }} {{ $mail->containers->count() > 1 ? 'copies archivées' : 'copie archivée' }}</p>
                    @endif

                    @if($mail->attachments && $mail->attachments->count() > 0)
                        <p><strong>Pièces jointes:</strong> {{ $mail->attachments->count() }} fichier(s)</p>
                    @endif
                </div>
            </div>
        </div>
    @endforeach

    <div style="margin-top: 30px; text-align: center; font-size: 8pt; color: #666666;">
        Document généré le {{ $generatedAt }} - {{ $totalCount }} courrier(s) au total
    </div>
</body>
</html>
