<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fantôme - {{ $communication->code }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0 0 10px 0;
            color: #2c3e50;
            font-size: 18px;
            font-weight: bold;
        }

        .header-info {
            display: table;
            width: 100%;
        }

        .header-left, .header-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .header-right {
            text-align: right;
        }

        .info-row {
            margin-bottom: 5px;
        }

        .info-label {
            font-weight: bold;
            color: #2c3e50;
        }

        .summary {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 25px;
        }

        .summary h2 {
            margin: 0 0 15px 0;
            color: #2c3e50;
            font-size: 14px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 5px;
        }

        .summary-grid {
            display: table;
            width: 100%;
        }

        .summary-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .records-section h2 {
            color: #2c3e50;
            font-size: 14px;
            margin: 0 0 15px 0;
            border-bottom: 1px solid #2c3e50;
            padding-bottom: 5px;
        }

        .records-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .records-table th,
        .records-table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        .records-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
            font-size: 10px;
        }

        .records-table td {
            font-size: 9px;
        }

        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            color: white;
        }

        .status-success { background-color: #28a745; }
        .status-danger { background-color: #dc3545; }
        .status-warning { background-color: #ffc107; color: #212529; }
        .status-primary { background-color: #007bff; }
        .status-secondary { background-color: #6c757d; }

        .footer {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-size: 9px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
        }

        .page-number:before {
            content: counter(page);
        }

        .total-pages:before {
            content: counter(pages);
        }

        .records-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .text-truncate {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 200px;
        }
    </style>
</head>
<body>
    <!-- En-tête du document -->
    <div class="header">
        <h1>FANTÔME DE COMMUNICATION</h1>
        <div class="header-info">
            <div class="header-left">
                <div class="info-row">
                    <span class="info-label">Code :</span> {{ $communication->code }}
                </div>
                <div class="info-row">
                    <span class="info-label">Objet :</span> {{ $communication->name }}
                </div>
                <div class="info-row">
                    <span class="info-label">Demandeur :</span>
                    {{ $communication->user->name ?? 'N/A' }}
                    @if($communication->userOrganisation)
                        ({{ $communication->userOrganisation->name }})
                    @endif
                </div>
            </div>
            <div class="header-right">
                <div class="info-row">
                    <span class="info-label">Généré le :</span> {{ $generated_at->format('d/m/Y H:i:s') }}
                </div>
                <div class="info-row">
                    <span class="info-label">Opérateur :</span>
                    {{ $communication->operator->name ?? 'N/A' }}
                    @if($communication->operatorOrganisation)
                        ({{ $communication->operatorOrganisation->name }})
                    @endif
                </div>
                <div class="info-row">
                    <span class="info-label">Statut :</span>
                    <span class="status-badge status-{{ $communication->status->color() }}">
                        {{ $communication->status->label() }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Résumé -->
    <div class="summary">
        <h2>RÉSUMÉ DE LA COMMUNICATION</h2>
        <div class="summary-grid">
            <div class="summary-col">
                <div class="info-row">
                    <span class="info-label">Nombre de documents :</span> {{ $records->count() }}
                </div>
                <div class="info-row">
                    <span class="info-label">Date de communication :</span> {{ $communication->created_at->format('d/m/Y') }}
                </div>
                <div class="info-row">
                    <span class="info-label">Date de retour prévue :</span>
                    {{ $communication->return_date ? $communication->return_date->format('d/m/Y') : 'Non définie' }}
                </div>
            </div>
            <div class="summary-col">
                <div class="info-row">
                    <span class="info-label">Documents retournés :</span>
                    {{ $records->where('return_effective', '!=', null)->count() }} / {{ $records->count() }}
                </div>
                <div class="info-row">
                    <span class="info-label">Documents en retard :</span>
                    {{ $records->filter(function($record) {
                        return !$record['return_effective'] &&
                               $record['return_date'] &&
                               \Carbon\Carbon::parse($record['return_date'])->isPast();
                    })->count() }}
                </div>
                <div class="info-row">
                    <span class="info-label">Date de retour effective :</span>
                    {{ $communication->return_effective ? $communication->return_effective->format('d/m/Y') : 'Non retourné' }}
                </div>
            </div>
        </div>

        @if($communication->content)
        <div style="margin-top: 15px;">
            <div class="info-label">Contenu/Observations :</div>
            <div style="margin-top: 5px; padding: 8px; background-color: white; border: 1px solid #dee2e6; border-radius: 3px;">
                {{ $communication->content }}
            </div>
        </div>
        @endif
    </div>

    <!-- Liste des documents -->
    <div class="records-section">
        <h2>LISTE DES DOCUMENTS COMMUNIQUÉS</h2>

        @if($records->count() > 0)
        <table class="records-table">
            <thead>
                <tr>
                    <th style="width: 8%;">Code</th>
                    <th style="width: 25%;">Intitulé</th>
                    <th style="width: 20%;">Contenu</th>
                    <th style="width: 8%;">Largeur</th>
                    <th style="width: 12%;">Statut</th>
                    <th style="width: 10%;">Date retour</th>
                    <th style="width: 10%;">Retour effectif</th>
                    <th style="width: 7%;">Modifié le</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $record)
                <tr>
                    <td>{{ $record['code'] }}</td>
                    <td class="text-truncate">{{ $record['name'] }}</td>
                    <td class="text-truncate">{{ $record['content'] ?: '-' }}</td>
                    <td>{{ $record['width'] ?: '-' }}</td>
                    <td>
                        <span class="status-badge status-{{ $record['status']['class'] }}">
                            {{ $record['status']['label'] }}
                        </span>
                    </td>
                    <td>
                        {{ $record['return_date'] ? \Carbon\Carbon::parse($record['return_date'])->format('d/m/Y') : '-' }}
                    </td>
                    <td>
                        {{ $record['return_effective'] ? \Carbon\Carbon::parse($record['return_effective'])->format('d/m/Y') : '-' }}
                    </td>
                    <td>
                        {{ $record['last_modified'] ? \Carbon\Carbon::parse($record['last_modified'])->format('d/m/Y') : '-' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div style="text-align: center; padding: 20px; color: #6c757d; font-style: italic;">
            Aucun document associé à cette communication.
        </div>
        @endif
    </div>

    <!-- Pied de page -->
    <div class="footer">
        Document généré automatiquement le {{ $generated_at->format('d/m/Y à H:i:s') }} -
        Communication {{ $communication->code }} -
        Page <span class="page-number"></span> sur <span class="total-pages"></span>
    </div>
</body>
</html>
