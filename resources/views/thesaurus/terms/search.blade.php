@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1>{{ __('Thesaurus Concepts') }}</h1>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('tool.thesaurus.index') }}">{{ __('Thesaurus') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('Concepts') }}</li>
                </ol>
            </nav>

            <!-- Search and Filter Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>{{ __('Search and Filter') }}</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('thesaurus.concepts') }}">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="search">{{ __('Search') }}</label>
                                    <input type="text" class="form-control" id="search" name="search"
                                           value="{{ request('search') }}" placeholder="{{ __('Search in labels...') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="scheme_id">{{ __('Scheme') }}</label>
                                    <select class="form-control" id="scheme_id" name="scheme_id">
                                        <option value="">{{ __('All Schemes') }}</option>
                                        @foreach($schemes as $scheme)
                                            <option value="{{ $scheme->id }}"
                                                    {{ request('scheme_id') == $scheme->id ? 'selected' : '' }}>
                                                {{ $scheme->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                                        <a href="{{ route('thesaurus.concepts') }}" class="btn btn-secondary">{{ __('Clear') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>{{ __('Concepts') }} ({{ $concepts->total() }})</h5>
                    <div>
                        <a href="{{ route('thesaurus.hierarchy') }}" class="btn btn-info btn-sm">
                            {{ __('Hierarchy View') }}
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if($concepts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ __('Preferred Label') }}</th>
                                        <th>{{ __('Scheme') }}</th>
                                        <th>{{ __('Alternative Labels') }}</th>
                                        <th>{{ __('Definition') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($concepts as $concept)
                                        <tr>
                                            <td>
                                                <strong>{{ $concept->pref_label ?? 'Untitled' }}</strong>
                                                @if($concept->notation)
                                                    <br><small class="text-muted">{{ $concept->notation }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($concept->scheme)
                                                    <span class="badge badge-secondary">{{ $concept->scheme->title }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($concept->alt_labels)
                                                    <small class="text-muted">{{ Str::limit($concept->alt_labels, 50) }}</small>
                                                @elseif($concept->labels && $concept->labels->where('type', '!=', 'preferred')->count() > 0)
                                                    <small class="text-muted">
                                                        {{ $concept->labels->where('type', '!=', 'preferred')->pluck('value')->implode(', ') }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($concept->definition)
                                                    <small>{{ Str::limit($concept->definition, 100) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('thesaurus.concepts.show', $concept) }}"
                                                   class="btn btn-sm btn-outline-primary">
                                                    {{ __('View') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $concepts->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-muted">{{ __('No concepts found.') }}</p>
                            @if(request('search') || request('scheme_id'))
                                <a href="{{ route('thesaurus.concepts') }}" class="btn btn-secondary">
                                    {{ __('View All Concepts') }}
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
