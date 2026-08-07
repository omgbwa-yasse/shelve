<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Courriers</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            margin-bottom: 30px;
        }
        .header-title {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 24px;
        }
        .summary-box {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        .summary-item {
            margin-bottom: 5px;
        }
        .summary-label {
            font-weight: bold;
            color: #495057;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            color: #2c3e50;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 10px;
            color: #6c757d;
        }
        @page {
            margin: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="header-title">Liste des Courriers</h1>

        <div class="summary-box">
            <div class="summary-grid">
                <div class="summary-item">
                    <span class="summary-label">Date d'extraction :</span>
                    {{ $generatedAt }}
                </div>
                <div class="summary-item">
                    <span class="summary-label">Nombre total de courriers :</span>
                    {{ $totalCount }}
                </div>
                <div class="summary-item">
                    <span class="summary-label">Types de documents :</span>
                    {{ $transactions->pluck('documentType.name')->unique()->filter()->implode(', ') ?: 'N/A' }}
                </div>
                <div class="summary-item">
                    <span class="summary-label">Période :</span>
                    @php
                        $dates = $transactions->pluck('date_creation')->filter();
                        $startDate = $dates->min() ? \Carbon\Carbon::parse($dates->min())->format('d/m/Y') : 'N/A';
                        $endDate = $dates->max() ? \Carbon\Carbon::parse($dates->max())->format('d/m/Y') : 'N/A';
                    @endphp
                    {{ $startDate }} - {{ $endDate }}
                </div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Nom du Courrier</th>
                <th>Action</th>
                <th>Description</th>
                <th>Envoyé par</th>
                <th>Reçu par</th>
                <th>Type de document</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->code ?? 'N/A' }}</td>
                    <td>{{ $transaction->mail->name ?? 'N/A' }}</td>
                    <td>{{ $transaction->action->name ?? 'N/A' }}</td>
                    <td>{{ Str::limit($transaction->description ?? 'N/A', 100) }}</td>
                    <td>
                        {{ $transaction->userSend->name ?? 'N/A' }}
                        <br>
                        <small>{{ $transaction->organisationSend->name ?? 'N/A' }}</small>
                    </td>
                    <td>
                        {{ $transaction->userReceived->name ?? 'N/A' }}
                        <br>
                        <small>{{ $transaction->organisationReceived->name ?? 'N/A' }}</small>
                    </td>
                    <td>{{ $transaction->documentType->name ?? 'N/A' }}</td>
                    <td>{{ $transaction->date_creation ? \Carbon\Carbon::parse($transaction->date_creation)->format('d/m/Y') : 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center;">Aucun courrier trouvé</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Document généré le {{ $generatedAt }} | Page {PAGENO}/{nb}</p>
    </div>
</body>
</html>
