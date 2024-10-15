<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('communication_sheets') }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .communication { margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        .communication h2 { color: #333; }
        .communication p { margin: 5px 0; }
        .record { margin-left: 20px; border-left: 2px solid #eee; padding-left: 10px; }
    </style>
</head>
<body>
<h1>{{ __('communication_sheets') }}</h1>
@foreach($communications as $communication)
    <div class="communication">
        <h2>{{ $communication->code }} : {{ $communication->name }}</h2>
        <p><strong>{{ __('content') }} :</strong> {{ $communication->content }}</p>
        <p><strong>{{ __('requester') }} :</strong> {{ $communication->user->name ?? __('not_available') }} ({{ $communication->userOrganisation->name ?? __('not_available') }})</p>
        <p><strong>{{ __('operator') }} :</strong> {{ $communication->operator->name ?? __('not_available') }} ({{ $communication->operatorOrganisation->name ?? __('not_available') }})</p>
        <p><strong>{{ __('return_date') }} :</strong> {{ $communication->return_date ?? __('not_available') }}</p>
        <p><strong>{{ __('effective_return_date') }} :</strong> {{ $communication->return_effective ?? __('not_available') }}</p>
        <p><strong>{{ __('status') }} :</strong> {{ $communication->status->name ?? __('not_available') }}</p>
        @if($communication->records->count() > 0)
            <h3>{{ __('associated_records') }} :</h3>
            @foreach($communication->records as $communicationRecord)
                <div class="record">
                    <h4>{{ $communicationRecord->record->code ?? __('not_available') }} : {{ $communicationRecord->record->name ?? __('not_available') }}</h4>
                    <p><strong>{{ __('content') }} :</strong> {{ $communicationRecord->content ?? __('not_available') }}</p>
                    <p><strong>{{ __('is_original') }} :</strong> {{ $communicationRecord->is_original ? __('yes') : __('no') }}</p>
                    <p><strong>{{ __('level') }} :</strong> {{ $communicationRecord->record->level->name ?? __('not_available') }}</p>
                    <p><strong>{{ __('support') }} :</strong> {{ $communicationRecord->record->support->name ?? __('not_available') }}</p>
                    <p><strong>{{ __('status') }} :</strong> {{ $communicationRecord->record->status->name ?? __('not_available') }}</p>
                </div>
            @endforeach
        @endif
    </div>
@endforeach
</body>
</html>
