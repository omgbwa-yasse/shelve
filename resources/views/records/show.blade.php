@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        {{-- Breadcrumb + Messages --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('records.index') }}">{{ __('records') }}</a></li>
                    <li class="breadcrumb-item active">{{ $record->code }}</li>
                </ol>
            </nav>
            <div class="btn-group">
                <a href="{{ route('records.edit', $record) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil"></i> {{ __('edit_sheet') }}
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="bi bi-trash"></i> {{ __('delete_sheet') }}
                </button>
                <button type="button" id="btn-ai-enrich" class="btn btn-sm btn-outline-info">
                    <i class="bi bi-stars"></i> {{ __('ai_enrich') ?? 'AI Enrich' }}
                </button>
            </div>
        </div>

        @if (session('success') || session('error'))
            <div class="alert alert-{{ session('success') ? 'success' : 'danger' }} alert-dismissible fade show">
                {{ session('success') ?? session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Main Content Card --}}
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h4 class="mb-0">{{ $record->name }} [{{ $record->level->name }}]</h4>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    {{-- Parent Record --}}
                    @if ($record->parent)
                        <div class="col-12">
                            <strong>{{ __('in') }}:</strong>
                            <a href="{{ route('records.show', $record->parent) }}">
                                [{{ $record->parent->level->name ?? '' }}] {{ $record->parent->name ?? '' }} /
                                @foreach($record->parent->authors as $author)
                                    {{ $author->name ?? '' }}
                                @endforeach
                                -
                                @if($record->parent->date_start != NULL && $record->parent->date_end != NULL)
                                    {{ $record->parent->date_start }} {{ __('to') }} {{ $record->parent->date_end }}
                                @elseif($record->parent->date_exact != NULL && $record->parent->date_end == NULL)
                                    {{ $record->parent->date_start }}
                                @elseif($record->parent->date_exact != NULL && $record->parent->date_start == NULL)
                                    {{ $record->parent->date_exact }}
                                @endif
                            </a>
                        </div>
                    @endif

                    {{-- Essential Information --}}
                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-3">{{ __('code') }}</dt>
                            <dd class="col-sm-9">{{ $record->code }}</dd>

                            <dt class="col-sm-3">{{ __('title_analysis') }}</dt>
                            <dd class="col-sm-9">{{ $record->name }}</dd>

                            <dt class="col-sm-3">{{ __('dates') }}</dt>
                            <dd class="col-sm-9">
                                @if($record->date_exact == NULL)
                                    @if($record->date_end == NULL)
                                        {{ $record->date_start ?? 'N/A' }}
                                    @else
                                        {{ $record->date_start ?? 'N/A' }} {{ __('to') }} {{ $record->date_end ?? 'N/A' }}
                                    @endif
                                @else
                                    {{ $record->date_exact ?? 'N/A' }}
                                @endif
                            </dd>

                            <dt class="col-sm-3">{{ __('producers') }}</dt>
                            <dd class="col-sm-9">
                                <a href="{{ route('records.sort')}}?categ=authors&id={{ $record->authors->pluck('id')->join('') }}">
                                    {{ $record->authors->isEmpty() ? 'N/A' : $record->authors->map(fn($author) => "{$author->name}")->implode(' ') }}
                                </a>
                            </dd>
                        </dl>
                    </div>

                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-3">{{ __('support') }}</dt>
                            <dd class="col-sm-9">{{ $record->support->name ?? 'N/A' }}</dd>

                            <dt class="col-sm-3">{{ __('status') }}</dt>
                            <dd class="col-sm-9">{{ $record->status->name ?? 'N/A' }}</dd>

                            <dt class="col-sm-3">{{ __('container') }}</dt>
                            <dd class="col-sm-9">
                                <a href="{{ route('records.sort')}}?categ=container&id={{ $record->container->id ?? 'none' }}">
                                    {{ $record->container->name ?? __('not_containerized') }}
                                </a>
                            </dd>

                            <dt class="col-sm-3">{{ __('created_by') }}</dt>
                            <dd class="col-sm-9">{{ $record->user->name ?? 'N/A' }}</dd>
                        </dl>
                    </div>

                    {{-- Dimensions --}}
                    <div class="col-12">
                        <dl class="row mb-0">
                            <dt class="col-sm-2">{{ __('width') }}</dt>
                            <dd class="col-sm-4">{{ $record->width ? $record->width . ' cm' : 'N/A' }}</dd>

                            <dt class="col-sm-2">{{ __('width_description') }}</dt>
                            <dd class="col-sm-4">{{ $record->width_description ?? 'N/A' }}</dd>
                        </dl>
                    </div>

                    {{-- Terms and Activity --}}
                    <div class="col-12">
                        <dl class="row mb-0">
                            <dt class="col-sm-2">{{ __('terms') }}</dt>
                            <dd class="col-sm-10">
                                @foreach($record->terms as $index => $term)
                                    <a href="{{ route('records.sort')}}?categ=term&id={{ $term->id ?? 'N/A' }}">
                                        {{ $term->name ?? 'N/A' }}
                                    </a>
                                    @if(!$loop->last)
                                        {{ " ; " }}
                                    @endif
                                @endforeach
                            </dd>

                            <dt class="col-sm-2">{{ __('activity') }}</dt>
                            <dd class="col-sm-10">
                                <a href="{{ route('records.sort')}}?categ=activity&id={{ $record->activity->id ?? 'N/A' }}">
                                    {{ $record->activity->name ?? 'N/A' }}
                                </a>
                            </dd>
                        </dl>
                    </div>

                    {{-- Content & Notes --}}
                    <div class="col-12">
                        <dl class="row mb-0">
                            <dt class="col-sm-2">{{ __('content') }}</dt>
                            <dd class="col-sm-10">{{ $record->content ?? 'N/A' }}</dd>

                            <dt class="col-sm-2">{{ __('note') }}</dt>
                            <dd class="col-sm-10">{{ $record->note ?? 'N/A' }}</dd>

                            <dt class="col-sm-2">{{ __('archivist_note') }}</dt>
                            <dd class="col-sm-10">{{ $record->archivist_note ?? 'N/A' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        {{-- MCP AI Features Card --}}
        <div class="card mb-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-stars me-2"></i>{{ __('ai_features') ?? 'AI Features' }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card h-100 bg-light">
                            <div class="card-body">
                                <h5 class="card-title">{{ __('record_enrichment') ?? 'Record Enrichment' }}</h5>
                                <p class="card-text small">{{ __('ai_enrichment_desc') ?? 'Extract keywords and suggest terms from record content.' }}</p>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-primary" id="btn-extract-keywords">
                                        <i class="bi bi-key"></i> {{ __('extract_keywords') ?? 'Extract Keywords' }}
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary" id="btn-suggest-terms">
                                        <i class="bi bi-tags"></i> {{ __('suggest_terms') ?? 'Suggest Terms' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card h-100 bg-light">
                            <div class="card-body">
                                <h5 class="card-title">{{ __('transfer_features') ?? 'Transfer Features' }}</h5>
                                <p class="card-text small">{{ __('ai_transfer_desc') ?? 'Validate, classify, and generate reports for archive transfers.' }}</p>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-primary" id="btn-validate-records">
                                        <i class="bi bi-check-circle"></i> {{ __('validate') ?? 'Validate' }}
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary" id="btn-suggest-classification">
                                        <i class="bi bi-diagram-3"></i> {{ __('classify') ?? 'Classify' }}
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary" id="btn-generate-report">
                                        <i class="bi bi-file-earmark-text"></i> {{ __('report') ?? 'Report' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div id="ai-results-container" class="border rounded p-3" style="display: none;">
                            <h6 class="border-bottom pb-2 mb-3">
                                <i class="bi bi-stars me-1"></i><span id="ai-results-title">{{ __('ai_results') ?? 'AI Results' }}</span>
                            </h6>
                            <div id="ai-results-content" class="small">
                                <!-- Results will be displayed here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Archive Boxes Section --}}
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="bi bi-archive me-2"></i>{{ __('archive_boxes') }}
                </h5>
            </div>
            <div class="card-body">
                <div class="list-group mb-3">
                    @foreach ($record->recordContainers as $recordContainer)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $recordContainer->container->code }} - {{ $recordContainer->description }}</span>
                            <form action="{{ route('record-container-remove')}}?r_id={{ $recordContainer->record_id }}&c_id={{ $recordContainer->container_id }}"
                                  method="post" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-outline-danger">{{ __('remove_from_box') }}</button>
                            </form>
                        </div>
                    @endforeach
                </div>

                <form action="{{ route('record-container-insert')}}?r_id={{ $record->id }}" method="post" class="row g-2">
                    @csrf
                    <div class="col-md-3">
                        <input type="text" name="code" class="form-control form-control-sm"
                               placeholder="{{ __('code') }}: B12453">
                    </div>
                    <div class="col">
                        <input type="text" name="description" class="form-control form-control-sm"
                               placeholder="{{ __('title_analysis') }}">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary">{{ __('insert') }}</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Child Records --}}
        @if($record->children->isNotEmpty() || $record->level->has_child)
            <div class="card mb-3">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('child_records') }}</h5>
                    @if($record->level->has_child)
                        <a href="{{ route('record-child.create', $record->id) }}" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-plus-circle me-1"></i>{{ __('add_child_sheet') }}
                        </a>
                    @endif
                </div>
                @if($record->children->isNotEmpty())
                    <div class="list-group list-group-flush">
                        @foreach($record->children as $child)
                            <a href="{{ route('records.show', $child) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $child->code }}</strong>
                                    <span class="text-muted ms-2">{{ $child->name }}</span>
                                </div>
                                <span class="badge bg-primary rounded-pill">{{ $child->level->name ?? 'N/A' }}</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="card-body text-muted">
                        {{ __('no_child_records') }}
                    </div>
                @endif
            </div>
        @endif

        {{-- Attachments Section --}}
        <div class="card mb-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-paperclip me-2"></i>{{ __('attachments') }}</h5>
                <a href="{{ route('records.attachments.create', $record) }}" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-plus-circle me-1"></i>{{ __('add_file') }}
                </a>
            </div>
            <div class="card-body">
                <div class="row row-cols-1 row-cols-md-4 g-3" id="attachmentsList">
                    @forelse($record->attachments as $attachment)
                        <div class="col">
                            <div class="card h-100 shadow-sm">
                                <div class="card-img-top bg-light" style="height: 140px;">
                                    @if($attachment->thumbnail_path)
                                        <img src="{{ asset('storage/' . $attachment->thumbnail_path) }}"
                                             class="img-fluid h-100 w-100" style="object-fit: cover;">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center h-100">
                                            <i class="bi bi-file-earmark-pdf fs-1 text-secondary"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-body p-2">
                                    <p class="card-text small text-truncate mb-2" title="{{ $attachment->name }}">
                                        {{ $attachment->name }}
                                    </p>
                                    <a href="{{ route('records.attachments.show', [$record, $attachment]) }}"
                                       class="btn btn-sm btn-outline-primary w-100">
                                        <i class="bi bi-download me-1"></i>{{ __('view_file') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-muted">
                            {{ __('no_attachments') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Back Button --}}
        <a href="{{ route('records.index') }}" class="btn btn-outline-secondary w-100">
            <i class="bi bi-arrow-left me-2"></i>{{ __('back_to_home') }}
        </a>
    </div>

    {{-- Delete Modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('confirm_deletion') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">{{ __('delete_confirmation') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                        {{ __('cancel') }}
                    </button>
                    <form action="{{ route('records.destroy', $record) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">
                            {{ __('delete') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

            // MCP API Functions
            const recordId = {{ $record->id }};
            const apiBaseUrl = '/api/mcp'; // Adjust this based on your API configuration
            
            const showLoading = (containerId, message) => {
                const container = document.getElementById(containerId);
                container.innerHTML = `
                    <div class="d-flex align-items-center">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span>${message || 'Loading...'}</span>
                    </div>
                `;
            };
            
            const showResults = (title, content) => {
                const resultsContainer = document.getElementById('ai-results-container');
                const resultsTitle = document.getElementById('ai-results-title');
                const resultsContent = document.getElementById('ai-results-content');
                
                resultsTitle.textContent = title;
                resultsContent.innerHTML = content;
                resultsContainer.style.display = 'block';
                
                // Scroll to results
                resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            };
            
            const handleApiError = (error) => {
                console.error('API Error:', error);
                showResults('Error', `<div class="alert alert-danger">An error occurred: ${error.message || 'Unknown error'}</div>`);
            };
            
            // Enrich Record
            document.getElementById('btn-ai-enrich').addEventListener('click', async () => {
                try {
                    showResults('Record Enrichment', '<div class="text-center">Processing record content...</div>');
                    showLoading('ai-results-content', 'Analyzing record content...');
                    
                    const response = await fetch(`${apiBaseUrl}/enrich/${recordId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    
                    if (!response.ok) throw new Error(`API returned status: ${response.status}`);
                    
                    const data = await response.json();
                    
                    let resultsHTML = `
                        <div class="alert alert-success">
                            <strong>Record successfully enriched!</strong>
                        </div>
                        <h6>Extracted Keywords:</h6>
                        <div class="mb-3">
                            ${data.keywords.map(keyword => 
                                `<span class="badge bg-info text-dark me-1 mb-1">${keyword}</span>`
                            ).join('')}
                        </div>
                        <h6>Suggested Terms:</h6>
                        <div>
                            ${data.terms.map(term => 
                                `<span class="badge bg-primary me-1 mb-1">${term}</span>`
                            ).join('')}
                        </div>
                    `;
                    
                    showResults('Record Enrichment Results', resultsHTML);
                } catch (error) {
                    handleApiError(error);
                }
            });
            
            // Extract Keywords
            document.getElementById('btn-extract-keywords').addEventListener('click', async () => {
                try {
                    showResults('Keyword Extraction', '<div class="text-center">Extracting keywords...</div>');
                    showLoading('ai-results-content', 'Analyzing record content for keywords...');
                    
                    const response = await fetch(`${apiBaseUrl}/extract-keywords/${recordId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    
                    if (!response.ok) throw new Error(`API returned status: ${response.status}`);
                    
                    const data = await response.json();
                    
                    let resultsHTML = `
                        <h6>Extracted Keywords by Category:</h6>
                        <div class="row g-3">
                    `;
                    
                    for (const [category, keywords] of Object.entries(data.categorizedKeywords)) {
                        resultsHTML += `
                            <div class="col-md-4">
                                <div class="card h-100 border-info">
                                    <div class="card-header bg-info bg-opacity-10 small fw-bold">${category}</div>
                                    <div class="card-body p-2">
                                        ${keywords.map(keyword => 
                                            `<span class="badge bg-light text-dark border me-1 mb-1">${keyword}</span>`
                                        ).join('')}
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                    
                    resultsHTML += `</div>`;
                    showResults('Keyword Extraction Results', resultsHTML);
                } catch (error) {
                    handleApiError(error);
                }
            });
            
            // Suggest Terms
            document.getElementById('btn-suggest-terms').addEventListener('click', async () => {
                try {
                    showResults('Term Suggestions', '<div class="text-center">Generating term suggestions...</div>');
                    showLoading('ai-results-content', 'Analyzing record content for term suggestions...');
                    
                    const response = await fetch(`${apiBaseUrl}/assign-terms/${recordId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    
                    if (!response.ok) throw new Error(`API returned status: ${response.status}`);
                    
                    const data = await response.json();
                    
                    let resultsHTML = `
                        <div class="mb-3">
                            <div class="alert alert-info">
                                The following terms are suggested based on the record content:
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            ${data.suggestedTerms.map(term => `
                                <div class="border rounded p-2 bg-light">
                                    <div class="fw-bold small">${term.name}</div>
                                    <div class="text-muted xsmall">${term.confidence}% confidence</div>
                                    <button class="btn btn-xs btn-outline-primary mt-1 btn-apply-term" 
                                            data-term-id="${term.id}" data-term-name="${term.name}">
                                        <i class="bi bi-plus-circle"></i> Apply
                                    </button>
                                </div>
                            `).join('')}
                        </div>
                    `;
                    
                    showResults('Term Suggestion Results', resultsHTML);
                    
                    // Add event listeners for apply term buttons
                    document.querySelectorAll('.btn-apply-term').forEach(button => {
                        button.addEventListener('click', async (e) => {
                            const termId = e.target.closest('button').getAttribute('data-term-id');
                            const termName = e.target.closest('button').getAttribute('data-term-name');
                            
                            try {
                                const response = await fetch(`/api/records/${recordId}/add-term/${termId}`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    }
                                });
                                
                                if (!response.ok) throw new Error(`API returned status: ${response.status}`);
                                
                                // Update UI to reflect the applied term
                                e.target.closest('button').innerHTML = '<i class="bi bi-check"></i> Applied';
                                e.target.closest('button').classList.remove('btn-outline-primary');
                                e.target.closest('button').classList.add('btn-success');
                                e.target.closest('button').disabled = true;
                                
                                // Update the terms list in the UI without page reload
                                const termsContainer = document.querySelector('dt:contains("{{ __('terms') }}")').nextElementSibling;
                                termsContainer.innerHTML += `
                                    <a href="{{ route('records.sort')}}?categ=term&id=${termId}">
                                        ${termName}
                                    </a> ; 
                                `;
                            } catch (error) {
                                console.error('Error applying term:', error);
                                alert('Failed to apply term. Please try again.');
                            }
                        });
                    });
                } catch (error) {
                    handleApiError(error);
                }
            });
            
            // Validate Records
            document.getElementById('btn-validate-records').addEventListener('click', async () => {
                try {
                    showResults('Record Validation', '<div class="text-center">Validating record...</div>');
                    showLoading('ai-results-content', 'Checking record compliance...');
                    
                    const response = await fetch(`${apiBaseUrl}/validate/${recordId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    
                    if (!response.ok) throw new Error(`API returned status: ${response.status}`);
                    
                    const data = await response.json();
                    
                    const statusBadge = data.isValid 
                        ? '<span class="badge bg-success">Valid</span>' 
                        : '<span class="badge bg-danger">Invalid</span>';
                        
                    let resultsHTML = `
                        <div class="alert ${data.isValid ? 'alert-success' : 'alert-danger'}">
                            <h6>Validation Status: ${statusBadge}</h6>
                            <p>${data.message}</p>
                        </div>
                        
                        <h6>Validation Details:</h6>
                        <ul class="list-group mb-3">
                            ${data.validationResults.map(result => `
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    ${result.field}
                                    ${result.valid 
                                        ? '<span class="badge bg-success"><i class="bi bi-check"></i></span>' 
                                        : '<span class="badge bg-danger"><i class="bi bi-x"></i></span>'
                                    }
                                </li>
                                ${!result.valid ? `<li class="list-group-item list-group-item-danger small">${result.message}</li>` : ''}
                            `).join('')}
                        </ul>
                        
                        ${!data.isValid ? `
                            <div>
                                <h6>Suggestions:</h6>
                                <ul>
                                    ${data.suggestions.map(suggestion => `<li>${suggestion}</li>`).join('')}
                                </ul>
                            </div>
                        ` : ''}
                    `;
                    
                    showResults('Record Validation Results', resultsHTML);
                } catch (error) {
                    handleApiError(error);
                }
            });
            
            // Suggest Classification
            document.getElementById('btn-suggest-classification').addEventListener('click', async () => {
                try {
                    showResults('Classification Suggestion', '<div class="text-center">Generating classification suggestions...</div>');
                    showLoading('ai-results-content', 'Analyzing record for classification...');
                    
                    const response = await fetch(`${apiBaseUrl}/classify/${recordId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    
                    if (!response.ok) throw new Error(`API returned status: ${response.status}`);
                    
                    const data = await response.json();
                    
                    let resultsHTML = `
                        <div class="alert alert-info mb-3">
                            Based on the content analysis, the following classifications are suggested:
                        </div>
                        
                        <div class="list-group mb-3">
                            ${data.classifications.map((classification, index) => `
                                <div class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">${classification.name}</h6>
                                        <span class="badge bg-primary rounded-pill">${classification.confidence}%</span>
                                    </div>
                                    <p class="text-muted small mb-0">${classification.description}</p>
                                </div>
                            `).join('')}
                        </div>
                        
                        <div>
                            <h6>Classification Rationale:</h6>
                            <p>${data.rationale}</p>
                        </div>
                    `;
                    
                    showResults('Classification Suggestions', resultsHTML);
                } catch (error) {
                    handleApiError(error);
                }
            });
            
            // Generate Report
            document.getElementById('btn-generate-report').addEventListener('click', async () => {
                try {
                    showResults('Report Generation', '<div class="text-center">Generating archive report...</div>');
                    showLoading('ai-results-content', 'Creating comprehensive report...');
                    
                    const response = await fetch(`${apiBaseUrl}/report/${recordId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    
                    if (!response.ok) throw new Error(`API returned status: ${response.status}`);
                    
                    const data = await response.json();
                    
                    let resultsHTML = `
                        <div class="alert alert-success mb-3">
                            <i class="bi bi-file-earmark-check me-2"></i>
                            Report generated successfully!
                        </div>
                        
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Report Summary</h6>
                            </div>
                            <div class="card-body">
                                <p>${data.summary}</p>
                                
                                <h6 class="mt-3">Key Metrics:</h6>
                                <ul class="list-unstyled">
                                    ${Object.entries(data.metrics || {}).map(([key, value]) => 
                                        `<li><strong>${key}:</strong> ${value}</li>`
                                    ).join('')}
                                </ul>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <a href="${data.reportUrl}" class="btn btn-outline-primary" target="_blank">
                                <i class="bi bi-file-earmark-pdf me-2"></i>Download Full Report
                            </a>
                        </div>
                    `;
                    
                    showResults('Generated Report', resultsHTML);
                } catch (error) {
                    handleApiError(error);
                }
            });

            // Animation pour les attachements
            const attachmentsList = document.getElementById('attachmentsList');
            if (attachmentsList) {
                attachmentsList.addEventListener('click', function(e) {
                    if (e.target.tagName === 'A') {
                        e.target.classList.add('btn-primary');
                        e.target.classList.remove('btn-outline-primary');
                        setTimeout(() => {
                            e.target.classList.remove('btn-primary');
                            e.target.classList.add('btn-outline-primary');
                        }, 300);
                    }
                });
            }

            // Défilement fluide pour l'accordéon
            document.querySelectorAll('.accordion-button').forEach(button => {
                button.addEventListener('click', function() {
                    setTimeout(() => {
                        this.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 350);
                });
            });

            // Gestion des alertes auto-disparition
            const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.classList.remove('show');
                    setTimeout(() => alert.remove(), 150);
                }, 5000);
            });
        });
    </script>
@endpush
