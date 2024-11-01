<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('communication_sheets') }}</title>
    <style>
        @page {
            margin: 1.5cm;
            size: A4;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            padding: 10px 0;
            border-bottom: 1px solid #000;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 16pt;
            margin: 0;
            padding: 0;
        }

        .print-info {
            font-size: 8pt;
            color: #666;
            margin-top: 5px;
        }

        .communication {
            margin-bottom: 15px;
            border: 1px solid #ddd;
            page-break-inside: avoid;
        }

        .communication-header {
            background: #f5f5f5;
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        .communication-header h2 {
            font-size: 12pt;
            margin: 0;
            color: #000;
        }

        .content-section {
            padding: 8px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 10px;
        }

        .info-row {
            margin-bottom: 4px;
            font-size: 9pt;
        }

        .info-label {
            font-weight: bold;
            color: #444;
        }

        .records-section {
            border-top: 1px solid #ddd;
            margin-top: 10px;
            padding-top: 10px;
        }

        .records-section h3 {
            font-size: 11pt;
            margin: 0 0 8px 0;
            color: #444;
        }

        .record {
            background: #fafafa;
            padding: 8px;
            margin-bottom: 8px;
            border-left: 2px solid #ddd;
        }

        .record h4 {
            font-size: 10pt;
            margin: 0 0 5px 0;
            color: #333;
        }

        @media print {
            .communication {
                border: none;
                border-bottom: 1px solid #ccc;
            }

            .communication:last-child {
                border-bottom: none;
            }

            .record {
                background: none;
            }
        }
    </style>
</head>
<body>
<div class="header">
    <h1>{{ __('communication_sheets') }}</h1>
    <div class="print-info">{{ __('printed_on') }}: {{ now() }}</div>
</div>

@foreach($communications as $communication)
    <div class="communication">
        <div class="communication-header">
            <h2>{{ $communication->code }} : {{ $communication->name }}</h2>
        </div>

        <div class="content-section">
            <div class="info-grid">
                <div>
                    <div class="info-row">
                        <span class="info-label">{{ __('content') }}:</span>
                        {{ Str::limit($communication->content, 100) }}
                    </div>
                    <div class="info-row">
                        <span class="info-label">{{ __('requester') }}:</span>
                        {{ $communication->user->name ?? __('not_available') }}
                        ({{ $communication->userOrganisation->name ?? __('not_available') }})
                    </div>
                    <div class="info-row">
                        <span class="info-label">{{ __('operator') }}:</span>
                        {{ $communication->operator->name ?? __('not_available') }}
                        ({{ $communication->operatorOrganisation->name ?? __('not_available') }})
                    </div>
                </div>
                <div>
                    <div class="info-row">
                        <span class="info-label">{{ __('status') }}:</span>
                        {{ $communication->status->name ?? __('not_available') }}
                    </div>
                    <div class="info-row">
                        <span class="info-label">{{ __('return_date') }}:</span>
                        {{ $communication->return_date ?? __('not_available') }}
                    </div>
                    <div class="info-row">
                        <span class="info-label">{{ __('effective_return_date') }}:</span>
                        {{ $communication->return_effective ?? __('not_available') }}
                    </div>
                </div>
            </div>

            @if($communication->records->count() > 0)
                <div class="records-section">
                    <h3>{{ __('associated_records') }}</h3>
                    @foreach($communication->records as $communicationRecord)
                        <div class="record">
                            <h4>{{ $communicationRecord->record->code ?? __('not_available') }}: {{ $communicationRecord->record->name ?? __('not_available') }}</h4>
                            <div class="info-row">
                                <span class="info-label">{{ __('content') }}:</span>
                                {{ Str::limit($communicationRecord->content ?? __('not_available'), 50) }}
                            </div>
                            <div class="info-grid">
                                <div class="info-row">
                                    <span class="info-label">{{ __('is_original') }}:</span>
                                    {{ $communicationRecord->is_original ? __('yes') : __('no') }}
                                </div>
                                <div class="info-row">
                                    <span class="info-label">{{ __('level') }}:</span>
                                    {{ $communicationRecord->record->level->name ?? __('not_available') }}
                                </div>
                                <div class="info-row">
                                    <span class="info-label">{{ __('support') }}:</span>
                                    {{ $communicationRecord->record->support->name ?? __('not_available') }}
                                </div>
                                <div class="info-row">
                                    <span class="info-label">{{ __('status') }}:</span>
                                    {{ $communicationRecord->record->status->name ?? __('not_available') }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endforeach
</body>
</html>
