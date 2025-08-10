<!DOCTYPE html>
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Impression des enregistrements</title>
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

        .record {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 0.5px solid #cccccc;
            page-break-inside: avoid;
        }

        .record h2 {
            background-color: #f5f5f5;
            padding: 5px;
            margin: 0 0 10px 0;
            font-size: 12pt;
            color: #000000;
        }

        .record-info {
            margin-left: 10px;
        }

        .record-info p {
            margin: 5px 0;
        }

        .record-info strong {
            display: inline-block;
            width: 150px;
        }

        table {
            width: 100%;
            margin-bottom: 10px;
            border-collapse: collapse;
        }

        td {
            padding: 4px;
            vertical-align: top;
        }

        td:first-child {
            width: 150px;
            font-weight: bold;
        }

        @page {
            margin: 2cm 1.5cm;
            footer: html_footer;
        }

        .footer {
            text-align: center;
            font-size: 8pt;
            color: #666666;
            border-top: 0.5px solid #cccccc;
            padding-top: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<h1>Impression des enregistrements sélectionnés</h1>

@php($total = count($records))

<!-- Sommaire / Table des matières -->
<div style="margin-bottom:25px; page-break-after:avoid;">
    <h2 style="font-size:12pt; margin:0 0 8px 0;">Sommaire</h2>
    <table style="width:100%; border-collapse:collapse; font-size:9pt;">
        <thead>
            <tr style="background:#efefef;">
                <th style="text-align:left; padding:3px 4px;">Enregistrement</th>
                <th style="text-align:left; padding:3px 4px;">Niveau</th>
                <th style="text-align:right; padding:3px 4px;">Page</th>
            </tr>
        </thead>
        @foreach($records as $i => $record)
            <tr>
                <td style="padding:2px 4px; width:55%;">{{ $i+1 }}. {{ $record->code }} : {{ $record->name }}</td>
                <td style="padding:2px 4px; width:25%; color:#555;">{{ $record->level->name ?? '' }}</td>
                <td style="padding:2px 4px; width:20%; text-align:right;">→ p. <!-- page number placeholder (généré par moteur PDF non trivial) --></td>
            </tr>
        @endforeach
    </table>
</div>

<!-- Index simplifié (par code) -->
@php($byCode = $records->sortBy(fn($r) => $r->code)->values())
<div style="margin-bottom:30px; page-break-after:always;">
    <h2 style="font-size:12pt; margin:0 0 8px 0;">Index (Codes)</h2>
    <table style="width:100%; border-collapse:collapse; font-size:8.5pt;">
        <thead>
            <tr style="background:#efefef;">
                <th style="text-align:left; padding:3px 4px; width:25%;">Code</th>
                <th style="text-align:left; padding:3px 4px;">Titre</th>
            </tr>
        </thead>
        @foreach($byCode as $r)
            <tr>
                <td style="padding:1px 4px; width:25%;">{{ $r->code }}</td>
                <td style="padding:1px 4px;">{{ $r->name }}</td>
            </tr>
        @endforeach
    </table>
</div>

@foreach($records as $record)
    <div class="record" id="rec-{{ $record->id }}">
        <h2>{{ $record->code }} : {{ $record->name }}</h2>
        <table>
            <thead>
                <tr>
                    <th style="width:150px; text-align:left;">Champ</th>
                    <th style="text-align:left;">Valeur</th>
                </tr>
            </thead>
            <tr>
                <td>Contenu</td>
                <td>{{ $record->content ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Niveau de description</td>
                <td>{{ $record->level->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Statut</td>
                <td>{{ $record->status->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Support</td>
                <td>{{ $record->support->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Activité</td>
                <td>{{ $record->activity->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Dates</td>
                <td>
                    {{ optional($record->date_start)->format('d/m/Y') ?? 'N/A' }} -
                    {{ optional($record->date_end)->format('d/m/Y') ?? 'N/A' }}
                </td>
            </tr>
            <tr>
                <td>Contenant</td>
                <td>
                    @if($record->containers->isNotEmpty())
                        {{ $record->containers->pluck('name')->join(', ') }}
                    @else
                        Non conditionné
                    @endif
                </td>
            </tr>
            <tr>
                <td>Producteur</td>
                <td>{{ $record->authors->pluck('name')->join(', ') ?: 'N/A' }}</td>
            </tr>
            <tr>
                <td>Vedettes</td>
                <td>
                    @if($record->thesaurusConcepts->isNotEmpty())
                        {{ $record->thesaurusConcepts->pluck('preferred_label')->join(', ') }}
                    @else
                        N/A
                    @endif
                </td>
            </tr>
            @if($record->relationLoaded('attachments') && $record->attachments->isNotEmpty())
            <tr>
                <td>Pièces jointes</td>
                <td>
                    <table style="width:100%; font-size:8pt; border-collapse:collapse; margin:4px 0;">
                        <thead>
                            <tr style="background:#efefef;">
                                <th style="text-align:left; width:45%;">Nom</th>
                                <th style="text-align:left; width:20%;">Type</th>
                                <th style="text-align:left; width:15%;">Taille</th>
                                <th style="text-align:left; width:20%;">Date</th>
                            </tr>
                        </thead>
                        @foreach($record->attachments as $att)
                            <tr>
                                <td style="padding:2px 3px;">{{ $att->name }}</td>
                                <td style="padding:2px 3px;">{{ pathinfo($att->name, PATHINFO_EXTENSION) }}</td>
                                <td style="padding:2px 3px;">@if($att->size) {{ number_format($att->size/1024,1,',',' ') }} KB @else N/A @endif</td>
                                <td style="padding:2px 3px;">{{ optional($att->created_at)->format('d/m/Y') }}</td>
                            </tr>
                        @endforeach
                    </table>
                </td>
            </tr>
            @endif
        </table>
    </div>
@endforeach

<htmlpagefooter name="footer">
    <div class="footer">
        Page {PAGENO} / {nb}
        <br>
        Document généré le {{ now()->format('d/m/Y à H:i') }}
    </div>
</htmlpagefooter>
</body>
</html>
