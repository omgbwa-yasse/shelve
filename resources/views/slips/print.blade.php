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
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            background-color: #f0f0f0;
            padding: 5px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .status {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
        }
        .status-approved { background-color: #dff0d8; }
        .status-pending { background-color: #fcf8e3; }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            padding: 10px 0;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>Bordereau de versement</h1>
    <h2>{{ $slip->code }}: {{ $slip->name }}</h2>
</div>

<div class="section">
    <div class="section-title">Description</div>
    <p>{{ $slip->description }}</p>
</div>

<div class="section">
    <div class="section-title">Services</div>
    <table>
        <tr>
            <th>Service versant</th>
            <th>Service des archives</th>
        </tr>
        <tr>
            <td>
                <strong>{{ $slip->userOrganisation->name }}</strong><br>
                Intervenant: {{ $slip->user ? $slip->user->name : 'Aucun' }}
            </td>
            <td>
                <strong>{{ $slip->officerOrganisation->name }}</strong><br>
                Responsable: {{ $slip->officer->name }}
            </td>
        </tr>
    </table>
</div>

<div class="section">
    <div class="section-title">Statut du bordereau</div>
    <table>
        <tr>
            <th>Statut</th>
            <td>{{ $slip->slipStatus ? $slip->slipStatus->name : 'Sans statut' }}</td>
        </tr>
        <tr>
            <th>Réception</th>
            <td>
                Date: {{ $slip->received_date ?? 'Non reçu' }}<br>
                Par: {{ $slip->receivedAgent ? $slip->receivedAgent->name : 'N/A' }}
            </td>
        </tr>
        <tr>
            <th>Approbation</th>
            <td>
                Date: {{ $slip->approved_date ?? 'Non approuvé' }}<br>
                Par: {{ $slip->approvedAgent ? $slip->approvedAgent->name : 'N/A' }}
            </td>
        </tr>
        <tr>
            <th>Intégration</th>
            <td>
                Date: {{ $slip->integrated_date ?? 'Non intégré' }}<br>
                Par: {{ $slip->integratedAgent ? $slip->integratedAgent->name : 'N/A' }}
            </td>
        </tr>
    </table>
</div>

<div class="section">
    <div class="section-title">Documents associés</div>
    @if($slip->records->isNotEmpty())
        <table>
            <thead>
            <tr>
                <th>Code</th>
                <th>Nom</th>
                <th>Date</th>
                <th>Niveau</th>
                <th>Support</th>
            </tr>
            </thead>
            <tbody>
            @foreach($slip->records as $record)
                <tr>
                    <td>{{ $record->code }}</td>
                    <td>{{ $record->name }}</td>
                    <td>
                        @if(is_null($record->date_exact) && is_null($record->date_end))
                            {{ $record->date_start }}
                        @elseif(is_null($record->date_exact) && !is_null($record->date_end))
                            {{ $record->date_start }} - {{ $record->date_end }}
                        @else
                            {{ $record->date_exact }}
                        @endif
                    </td>
                    <td>{{ $record->level ? $record->level->name : 'N/A' }}</td>
                    <td>{{ $record->support ? $record->support->name : 'N/A' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <p>Aucun document associé à ce bordereau.</p>
    @endif
</div>

<div class="footer">
    Généré le {{ now()->format('d/m/Y H:i:s') }}
</div>
</body>
</html>
