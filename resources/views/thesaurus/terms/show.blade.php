@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1>{{ __('Concept Details') }}</h1>

            @if($concept->scheme)
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('tool.thesaurus.index') }}">{{ __('Thesaurus') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('thesaurus.concepts') }}">{{ __('Concepts') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $concept->pref_label ?? $concept->id }}</li>
                    </ol>
                </nav>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3>{{ $concept->pref_label ?? __('Untitled Concept') }}</h3>
                    @if($concept->scheme)
                        <small class="text-muted">{{ __('Scheme') }}: {{ $concept->scheme->title }}</small>
                    @endif
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>{{ __('Basic Information') }}</h5>
                            <dl class="row">
                                <dt class="col-sm-4">{{ __('ID') }}</dt>
                                <dd class="col-sm-8">{{ $concept->id }}</dd>

                                @if($concept->uri)
                                    <dt class="col-sm-4">{{ __('URI') }}</dt>
                                    <dd class="col-sm-8"><code>{{ $concept->uri }}</code></dd>
                                @endif

                                @if($concept->notation)
                                    <dt class="col-sm-4">{{ __('Notation') }}</dt>
                                    <dd class="col-sm-8">{{ $concept->notation }}</dd>
                                @endif

                                @if($concept->definition)
                                    <dt class="col-sm-4">{{ __('Definition') }}</dt>
                                    <dd class="col-sm-8">{{ $concept->definition }}</dd>
                                @endif
                            </dl>

                            @if($concept->labels && $concept->labels->count() > 0)
                                <h5>{{ __('Labels') }}</h5>
                                <ul class="list-group list-group-flush">
                                    @foreach($concept->labels as $label)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            {{ $label->value ?? $label->literal_form }}
                                            <span class="badge badge-secondary">{{ $label->type ?? $label->label_type }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        <div class="col-md-6">
                            @if($concept->sourceRelations && $concept->sourceRelations->count() > 0)
                                <h5>{{ __('Related Concepts') }}</h5>
                                <ul class="list-group list-group-flush">
                                    @foreach($concept->sourceRelations as $relation)
                                        @if($relation->targetConcept)
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <a href="{{ route('thesaurus.concepts.show', $relation->targetConcept) }}">
                                                    {{ $relation->targetConcept->pref_label ?? 'Untitled' }}
                                                </a>
                                                <span class="badge badge-info">{{ $relation->relation_type }}</span>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            @endif

                            @if($concept->targetRelations && $concept->targetRelations->count() > 0)
                                <ul class="list-group list-group-flush mt-2">
                                    @foreach($concept->targetRelations as $relation)
                                        @if($relation->sourceConcept)
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <a href="{{ route('thesaurus.concepts.show', $relation->sourceConcept) }}">
                                                    {{ $relation->sourceConcept->pref_label ?? 'Untitled' }}
                                                </a>
                                                <span class="badge badge-warning">{{ $relation->relation_type }}</span>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            @endif

                            @if($concept->notes && $concept->notes->count() > 0)
                                <h5 class="mt-3">{{ __('Notes') }}</h5>
                                @foreach($concept->notes as $note)
                                    <div class="alert alert-info">
                                        <strong>{{ $note->note_type ?? __('Note') }}:</strong>
                                        {{ $note->note_text ?? $note->literal_form }}
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    @if($concept->records && $concept->records->count() > 0)
                        <hr>
                        <h5>{{ __('Associated Records') }} ({{ $concept->records->count() }})</h5>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>{{ __('Record') }}</th>
                                        <th>{{ __('Title') }}</th>
                                        <th>{{ __('Weight') }}</th>
                                        <th>{{ __('Context') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($concept->records as $record)
                                        <tr>
                                            <td>{{ $record->id }}</td>
                                            <td>
                                                <a href="{{ route('records.show', $record) }}">
                                                    {{ $record->name ?? $record->title }}
                                                </a>
                                            </td>
                                            <td>
                                                @if($record->pivot && isset($record->pivot->weight))
                                                    <span class="badge badge-primary">{{ number_format($record->pivot->weight, 2) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($record->pivot && isset($record->pivot->context))
                                                    <small class="text-muted">{{ $record->pivot->context }}</small>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <div class="card-footer">
                    <a href="{{ route('thesaurus.concepts') }}" class="btn btn-secondary">
                        {{ __('Back to Concepts') }}
                    </a>
                    @if($concept->scheme)
                        <a href="{{ route('thesaurus.hierarchy', ['scheme_id' => $concept->scheme->id]) }}" class="btn btn-info">
                            {{ __('View Hierarchy') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
