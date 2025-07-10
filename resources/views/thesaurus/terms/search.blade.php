@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">{{ __('Recherche dans le thésaurus') }}</h1>

            <!-- Formulaire de recherche -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>{{ __('Recherche avancée') }}</h5>
                </div>
                <div class="card-body">
                    <form id="search-form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="search_term" class="form-label">{{ __('Terme') }}</label>
                                    <input type="text" class="form-control" id="search_term" name="search_term"
                                           placeholder="{{ __('Tapez pour rechercher...') }}" autocomplete="off">
                                    <div id="autocomplete-results" class="autocomplete-dropdown"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="language" class="form-label">{{ __('Langue') }}</label>
                                    <select class="form-select" id="language" name="language">
                                        <option value="">{{ __('Toutes les langues') }}</option>
                                        <option value="fr">{{ __('Français') }}</option>
                                        <option value="en">{{ __('Anglais') }}</option>
                                        <option value="es">{{ __('Espagnol') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="search_type" class="form-label">{{ __('Type de recherche') }}</label>
                                    <select class="form-select" id="search_type" name="search_type">
                                        <option value="all">{{ __('Tous les champs') }}</option>
                                        <option value="preferred">{{ __('Label préféré uniquement') }}</option>
                                        <option value="scope">{{ __('Note d\'application') }}</option>
                                        <option value="non_descriptors">{{ __('Non-descripteurs') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> {{ __('Rechercher') }}
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> {{ __('Réinitialiser') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Résultats de recherche -->
            <div id="search-results">
                <!-- Les résultats seront chargés ici via AJAX -->
            </div>

            <!-- Loader -->
            <div id="search-loader" class="text-center d-none">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">{{ __('Recherche en cours...') }}</span>
                </div>
                <p class="mt-2">{{ __('Recherche en cours...') }}</p>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.autocomplete-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-top: none;
    border-radius: 0 0 4px 4px;
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
}

.autocomplete-item {
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
}

.autocomplete-item:hover,
.autocomplete-item.active {
    background-color: #f8f9fa;
}

.autocomplete-item:last-child {
    border-bottom: none;
}

.term-card {
    transition: all 0.2s ease;
}

.term-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.search-term-container {
    position: relative;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let autocompleteTimeout;
    let currentRequest;

    // Autocomplete functionality
    $('#search_term').on('input', function() {
        const term = $(this).val().trim();
        const dropdown = $('#autocomplete-results');

        if (term.length < 2) {
            dropdown.hide();
            return;
        }

        // Clear previous timeout
        clearTimeout(autocompleteTimeout);

        // Cancel previous request
        if (currentRequest) {
            currentRequest.abort();
        }

        autocompleteTimeout = setTimeout(() => {
            currentRequest = $.ajax({
                url: '{{ route("terms.autocomplete") }}',
                type: 'GET',
                data: { term: term },
                success: function(response) {
                    if (response.success && response.results.length > 0) {
                        let html = '';
                        response.results.forEach(item => {
                            html += `
                                <div class="autocomplete-item" data-id="${item.id}" data-term="${item.preferred_label}">
                                    <strong>${item.preferred_label}</strong>
                                    ${item.scope_note ? `<br><small class="text-muted">${item.scope_note}</small>` : ''}
                                </div>
                            `;
                        });
                        dropdown.html(html).show();
                    } else {
                        dropdown.hide();
                    }
                },
                error: function(xhr) {
                    if (xhr.statusText !== 'abort') {
                        dropdown.hide();
                    }
                }
            });
        }, 300);
    });

    // Handle autocomplete selection
    $(document).on('click', '.autocomplete-item', function() {
        const term = $(this).data('term');
        $('#search_term').val(term);
        $('#autocomplete-results').hide();
        $('#search-form').submit();
    });

    // Hide autocomplete when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.search-term-container').length) {
            $('#autocomplete-results').hide();
        }
    });

    // Keyboard navigation for autocomplete
    $('#search_term').on('keydown', function(e) {
        const dropdown = $('#autocomplete-results');
        const items = dropdown.find('.autocomplete-item');
        const activeItem = items.filter('.active');

        if (dropdown.is(':visible') && items.length > 0) {
            switch(e.keyCode) {
                case 40: // Down arrow
                    e.preventDefault();
                    if (activeItem.length === 0) {
                        items.first().addClass('active');
                    } else {
                        activeItem.removeClass('active');
                        const next = activeItem.next('.autocomplete-item');
                        if (next.length > 0) {
                            next.addClass('active');
                        } else {
                            items.first().addClass('active');
                        }
                    }
                    break;

                case 38: // Up arrow
                    e.preventDefault();
                    if (activeItem.length === 0) {
                        items.last().addClass('active');
                    } else {
                        activeItem.removeClass('active');
                        const prev = activeItem.prev('.autocomplete-item');
                        if (prev.length > 0) {
                            prev.addClass('active');
                        } else {
                            items.last().addClass('active');
                        }
                    }
                    break;

                case 13: // Enter
                    e.preventDefault();
                    if (activeItem.length > 0) {
                        activeItem.click();
                    } else {
                        $('#search-form').submit();
                    }
                    break;

                case 27: // Escape
                    dropdown.hide();
                    break;
            }
        }
    });

    // Handle search form submission
    $('#search-form').on('submit', function(e) {
        e.preventDefault();

        const formData = {
            search_term: $('#search_term').val(),
            language: $('#language').val(),
            search_type: $('#search_type').val()
        };

        const loader = $('#search-loader');
        const resultsDiv = $('#search-results');

        loader.removeClass('d-none');
        resultsDiv.empty();
        $('#autocomplete-results').hide();

        $.ajax({
            url: '{{ route("terms.search") }}',
            type: 'GET',
            data: formData,
            success: function(response) {
                loader.addClass('d-none');

                if (response.success) {
                    if (response.results.length > 0) {
                        let html = `
                            <div class="card">
                                <div class="card-header">
                                    <h5>{{ __('Résultats de recherche') }} (${response.results.length})</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                        `;

                        response.results.forEach(term => {
                            html += `
                                <div class="col-md-6 mb-3">
                                    <div class="card term-card h-100">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <a href="{{ url('tools/thesaurus/terms') }}/${term.id}" class="text-decoration-none">
                                                    ${term.preferred_label}
                                                </a>
                                                <span class="badge bg-secondary ms-2">${term.language}</span>
                                            </h6>
                                            ${term.scope_note ? `<p class="card-text small text-muted">${term.scope_note}</p>` : ''}
                                            <div class="mt-2">
                                                <a href="{{ url('tools/thesaurus/terms') }}/${term.id}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> {{ __('Voir') }}
                                                </a>
                                                <a href="{{ url('tools/thesaurus/terms') }}/${term.id}/edit" class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-pencil"></i> {{ __('Modifier') }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });

                        html += `
                                    </div>
                                </div>
                            </div>
                        `;

                        resultsDiv.html(html);
                    } else {
                        resultsDiv.html(`
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                {{ __('Aucun résultat trouvé pour votre recherche.') }}
                            </div>
                        `);
                    }
                } else {
                    resultsDiv.html(`
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                            {{ __('Erreur lors de la recherche') }}: ${response.message}
                        </div>
                    `);
                }
            },
            error: function(xhr) {
                loader.addClass('d-none');
                resultsDiv.html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        {{ __('Erreur lors de la recherche') }}: ${xhr.responseJSON?.message || xhr.statusText}
                    </div>
                `);
            }
        });
    });

    // Handle form reset
    $('#search-form').on('reset', function() {
        $('#search-results').empty();
        $('#autocomplete-results').hide();
    });
});
</script>
@endpush
@endsection
