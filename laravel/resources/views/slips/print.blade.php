<!-- resources/views/slips/print.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bordereau {{ $slip->code }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            font-size: 12px;
        }
        .page-break {
            page-break-before: always;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            max-height: 80px;
            margin-bottom: 20px;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            margin: 20px 0;
        }
        .subtitle {
            font-size: 18px;
            margin: 10px 0;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            background-color: #f0f0f0;
            padding: 8px;
            margin-bottom: 15px;
            font-weight: bold;
            font-size: 14px;
        }
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            width: 30%;
            font-weight: bold;
            padding: 5px;
            border-bottom: 1px solid #ddd;
        }
        .info-value {
            display: table-cell;
            padding: 5px;
            border-bottom: 1px solid #ddd;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            font-size: 11px;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .status {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
        }
        .status-approved { background-color: #dff0d8; }
        .status-pending { background-color: #fcf8e3; }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
        }
        .summary-item {
            margin-bottom: 8px;
        }
        .records-table {
            margin-top: 20px;
        }
        .records-table th {
            background-color: #e9ecef;
            font-size: 12px;
        }
        .records-table td {
            font-size: 11px;
            vertical-align: top;
        }
        .date-column {
            width: 12%;
        }
        .code-column {
            width: 15%;
        }
        .title-column {
            width: 35%;
        }
        .container-column {
            width: 15%;
        }
        .observation-column {
            width: 23%;
        }
        .signature-section {
            margin-top: 40px;
            margin-bottom: 30px;
        }
        .signature-row {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .signature-cell {
            display: table-cell;
            width: 48%;
            vertical-align: top;
            padding: 0 10px;
        }
        .signature-cell:first-child {
            padding-left: 0;
        }
        .signature-cell:last-child {
            padding-right: 0;
        }
        .signature-box {
            border: 1px solid #ddd;
            padding: 20px;
            min-height: 120px;
            text-align: center;
        }
        .signature-title {
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 13px;
        }
        .signature-name {
            margin-bottom: 15px;
            font-size: 12px;
        }
        .signature-line {
            border-bottom: 1px solid #333;
            width: 200px;
            margin: 0 auto 10px auto;
        }
        .signature-label {
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Page de sommaire -->
    <div class="header">
        <div class="title">BORDEREAU DE VERSEMENT</div>
        <div class="subtitle">{{ $slip->code }}: {{ $slip->name }}</div>
        <div style="margin-top: 30px;">
            <strong>Service d'archives :</strong> {{ $slip->officerOrganisation->name }}<br>
            <strong>Service versant :</strong> {{ $slip->userOrganisation->name }}
        </div>
    </div>

    <div class="section">
        <div class="section-title">SOMMAIRE</div>

        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Code du bordereau</div>
                <div class="info-value">{{ $slip->code }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Titre</div>
                <div class="info-value">{{ $slip->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Description</div>
                <div class="info-value">{{ $slip->description ?? 'Aucune description' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Service versant</div>
                <div class="info-value">{{ $slip->userOrganisation->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Responsable versement</div>
                <div class="info-value">{{ $slip->user ? $slip->user->name : 'Non défini' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Service d'archives</div>
                <div class="info-value">{{ $slip->officerOrganisation->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Responsable archives</div>
                <div class="info-value">{{ $slip->officer->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Statut</div>
                <div class="info-value">{{ $slip->slipStatus ? $slip->slipStatus->name : 'Sans statut' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Date de création</div>
                <div class="info-value">{{ $slip->created_at->format('d/m/Y H:i') }}</div>
            </div>
            @if($slip->received_date)
            <div class="info-row">
                <div class="info-label">Date de réception</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($slip->received_date)->format('d/m/Y') }}</div>
            </div>
            @endif
            @if($slip->approved_date)
            <div class="info-row">
                <div class="info-label">Date d'approbation</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($slip->approved_date)->format('d/m/Y') }}</div>
            </div>
            @endif
            <div class="info-row">
                <div class="info-label">Nombre de documents</div>
                <div class="info-value">{{ $slip->records->count() }} document(s)</div>
            </div>
        </div>
    </div>

    <!-- Zones de signature -->
    <div class="signature-section">
        <div class="signature-row">
            <div class="signature-cell">
                <div class="signature-box">
                    <div class="signature-title">SERVICE VERSANT</div>
                    <div class="signature-name">{{ $slip->userOrganisation->name }}</div>
                    @if($slip->user)
                        <div class="signature-name">{{ $slip->user->name }}</div>
                    @endif
                    <div class="signature-line"></div>
                    <div class="signature-label">Signature et cachet</div>
                </div>
            </div>
            <div class="signature-cell">
                <div class="signature-box">
                    <div class="signature-title">SERVICE D'ARCHIVES</div>
                    <div class="signature-name">{{ $slip->officerOrganisation->name }}</div>
                    <div class="signature-name">{{ $slip->officer->name }}</div>
                    <div class="signature-line"></div>
                    <div class="signature-label">Signature et cachet</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Saut de page -->
    <div class="page-break"></div>

    <!-- Tableau des versements -->
    <div class="header">
        <div class="title">LISTE DES VERSEMENTS</div>
        <div class="subtitle">{{ $slip->code }}: {{ $slip->name }}</div>
    </div>

    <div class="section">
        @if($slip->records->isNotEmpty())
        <table class="records-table">
            <thead>
                <tr>
                    <th class="code-column">Cote</th>
                    <th class="title-column">Titre</th>
                    <th class="date-column">Date</th>
                    <th class="container-column">Code Container</th>
                    <th class="observation-column">Observation</th>
                </tr>
            </thead>
            <tbody>
                @foreach($slip->records as $record)
                <tr>
                    <td class="code-column">{{ $record->code }}</td>
                    <td class="title-column">{{ $record->name }}</td>
                    <td class="date-column">
                        @if($record->date_exact)
                            {{ \Carbon\Carbon::parse($record->date_exact)->format('d/m/Y') }}
                        @elseif($record->date_start && $record->date_end)
                            {{ \Carbon\Carbon::parse($record->date_start)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($record->date_end)->format('d/m/Y') }}
                        @elseif($record->date_start)
                            Depuis {{ \Carbon\Carbon::parse($record->date_start)->format('d/m/Y') }}
                        @else
                            Non définie
                        @endif
                    </td>
                    <td class="container-column">
                        @if($record->containers && $record->containers->isNotEmpty())
                            @foreach($record->containers as $container)
                                {{ $container->code }}@if(!$loop->last), @endif
                            @endforeach
                        @else
                            N/A
                        @endif
                    </td>
                    <td class="observation-column">
                        {{ $record->content ?? 'Aucune observation' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div style="text-align: center; padding: 40px; color: #666;">
            <p>Aucun document associé à ce bordereau de versement.</p>
        </div>
        @endif
    </div>

    <div class="footer">
        Bordereau de versement {{ $slip->code }} - Généré le {{ now()->format('d/m/Y à H:i') }}
    </div>
</body>
</html>
