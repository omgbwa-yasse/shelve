<style>
  .quick-nav-container { position: fixed; right: 16px; bottom: 16px; z-index: 1040; }
  .quick-nav-menu .dropdown-menu { min-width: 220px; }
</style>

<div class="quick-nav-container">
  <div class="btn-group dropup quick-nav-menu">
    <button type="button" class="btn btn-primary rounded-circle shadow" id="quickNavBtn" aria-haspopup="true" aria-expanded="false" title="Quick actions (b,p,n,e,c,/)">
      <i class="bi bi-lightning-charge"></i>
    </button>
    <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      <span class="visually-hidden">Toggle Dropdown</span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
      <li>
        <a class="dropdown-item" href="{{ session('records.back_url', route('records.index')) }}">
          <i class="bi bi-arrow-left me-2"></i>{{ __('back_to_list') ?? 'Retour à la liste' }}
        </a>
      </li>
      <li><hr class="dropdown-divider"></li>
      <li>
        <a class="dropdown-item {{ isset($prevId) ? '' : 'disabled' }}" href="{{ isset($prevId) ? route('records.show', $prevId) : '#' }}">
          <i class="bi bi-chevron-left me-2"></i>{{ __('previous') }}
        </a>
      </li>
      <li>
        <a class="dropdown-item {{ isset($nextId) ? '' : 'disabled' }}" href="{{ isset($nextId) ? route('records.show', $nextId) : '#' }}">
          <i class="bi bi-chevron-right me-2"></i>{{ __('next') }}
        </a>
      </li>
      <li><hr class="dropdown-divider"></li>
      <li>
        <a class="dropdown-item" href="{{ route('records.create') }}">
          <i class="bi bi-plus-circle me-2"></i>{{ __('new') }}
        </a>
      </li>
      @if(isset($record) && $record)
      <li>
        <a class="dropdown-item" href="{{ route('records.create', ['parent_id' => $record->id]) }}">
          <i class="bi bi-diagram-3 me-2"></i>{{ __('create_child') ?? 'Créer un enfant' }}
        </a>
      </li>
      @endif
      <li>
        <a class="dropdown-item" href="{{ route('records.advanced.form') }}">
          <i class="bi bi-search me-2"></i>{{ __('advanced') }}
        </a>
      </li>
      @can('records_import')
      <li>
        <a class="dropdown-item" href="{{ route('records.import.form') }}">
          <i class="bi bi-download me-2"></i>{{ __('record_import') }}
        </a>
      </li>
      @endcan
      @can('records_export')
      <li>
        <a class="dropdown-item" href="{{ route('records.export.form') }}">
          <i class="bi bi-upload me-2"></i>{{ __('record_export') }}
        </a>
      </li>
      @endcan
    </ul>
  </div>
</div>

<script>
  (function() {
    // Raccourcis clavier
    document.addEventListener('keydown', function(e) {
      if (e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable)) {
        return; // Ne pas interférer avec la saisie
      }

      // Back
      if (e.key === 'b') {
        window.location.href = "{{ session('records.back_url', route('records.index')) }}";
      }
      // Previous
      if (e.key === 'p' && {{ isset($prevId) ? 'true' : 'false' }}) {
        window.location.href = "{{ isset($prevId) ? route('records.show', $prevId) : '#' }}";
      }
      // Next
      if (e.key === 'n' && {{ isset($nextId) ? 'true' : 'false' }}) {
        window.location.href = "{{ isset($nextId) ? route('records.show', $nextId) : '#' }}";
      }
      // Edit
      if (e.key === 'e' && {{ isset($record) ? 'true' : 'false' }}) {
        window.location.href = "{{ isset($record) ? route('records.edit', $record) : '#' }}";
      }
      // Create new
      if (e.key === 'c' && !e.shiftKey) {
        window.location.href = "{{ route('records.create') }}";
      }
      // Focus filtre liste
      if (e.key === '/') {
        const filter = document.getElementById('listFilter');
        if (filter) {
          e.preventDefault();
          filter.focus();
        }
      }
    });
  })();
</script>

