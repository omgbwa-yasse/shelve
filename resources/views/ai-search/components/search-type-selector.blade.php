{{-- Composant sélecteur de type de recherche --}}
<div class="search-type-selector">
    <label class="form-label fw-bold">{{ __('Search in:') }}</label>
    <div class="btn-group w-100" role="group">
        <input type="radio" class="btn-check" name="searchType" id="searchRecords" value="records" {{ $defaultType === 'records' ? 'checked' : '' }}>
        <label class="btn btn-outline-primary" for="searchRecords">
            <i class="bi bi-folder me-1"></i>{{ __('Records') }}
        </label>

        <input type="radio" class="btn-check" name="searchType" id="searchMails" value="mails" {{ $defaultType === 'mails' ? 'checked' : '' }}>
        <label class="btn btn-outline-primary" for="searchMails">
            <i class="bi bi-envelope me-1"></i>{{ __('Mails') }}
        </label>

        <input type="radio" class="btn-check" name="searchType" id="searchCommunications" value="communications" {{ $defaultType === 'communications' ? 'checked' : '' }}>
        <label class="btn btn-outline-primary" for="searchCommunications">
            <i class="bi bi-chat-dots me-1"></i>{{ __('Communications') }}
        </label>

        <input type="radio" class="btn-check" name="searchType" id="searchSlips" value="slips" {{ $defaultType === 'slips' ? 'checked' : '' }}>
        <label class="btn btn-outline-primary" for="searchSlips">
            <i class="bi bi-arrow-left-right me-1"></i>{{ __('Transfers') }}
        </label>
    </div>
</div>
