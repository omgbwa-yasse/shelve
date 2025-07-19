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

@foreach($records as $record)
    <div class="record">
        <h2>{{ $record->code }} : {{ $record->name }}</h2>
        <table>
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
                <td>{{ $record->container->name ?? 'Non conditionné' }}</td>
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
