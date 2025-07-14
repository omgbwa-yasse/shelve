@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1>{{ __('Thesaurus Hierarchy') }}</h1>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('tool.thesaurus.index') }}">{{ __('Thesaurus') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('thesaurus.concepts') }}">{{ __('Concepts') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('Hierarchy') }}</li>
                </ol>
            </nav>

            <!-- Scheme Selection -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>{{ __('Select Scheme') }}</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('thesaurus.hierarchy') }}">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="scheme_id">{{ __('Thesaurus Scheme') }}</label>
                                    <select class="form-control" id="scheme_id" name="scheme_id" onchange="this.form.submit()">
                                        <option value="">{{ __('Select a scheme...') }}</option>
                                        @foreach($schemes as $schemeOption)
                                            <option value="{{ $schemeOption->id }}"
                                                    {{ ($scheme && $scheme->id == $schemeOption->id) ? 'selected' : '' }}>
                                                {{ $schemeOption->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="view_type">{{ __('View') }}</label>
                                    <div>
                                        <a href="{{ route('thesaurus.concepts') }}" class="btn btn-secondary">
                                            {{ __('List View') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if($scheme)
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Hierarchy for') }}: {{ $scheme->title }}</h5>
                        @if($scheme->description)
                            <small class="text-muted">{{ $scheme->description }}</small>
                        @endif
                    </div>

                    <div class="card-body">
                        @if(count($hierarchyData) > 0)
                            <div class="hierarchy-tree">
                                @foreach($hierarchyData as $node)
                                    @include('thesaurus.terms.partials.hierarchy-node', ['node' => $node, 'level' => 0])
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <p class="text-muted">{{ __('No concepts found in this scheme or no hierarchy structure defined.') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <p class="text-muted">{{ __('Please select a thesaurus scheme to view its hierarchy.') }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.hierarchy-tree {
    font-family: 'Courier New', monospace;
}

.hierarchy-node {
    margin-left: 20px;
    margin-bottom: 5px;
}

.hierarchy-node .concept-link {
    text-decoration: none;
    color: #007bff;
}

.hierarchy-node .concept-link:hover {
    text-decoration: underline;
}

.hierarchy-level-0 { margin-left: 0; }
.hierarchy-level-1 { margin-left: 20px; }
.hierarchy-level-2 { margin-left: 40px; }
.hierarchy-level-3 { margin-left: 60px; }
.hierarchy-level-4 { margin-left: 80px; }
.hierarchy-level-5 { margin-left: 100px; }
</style>
@endsection
