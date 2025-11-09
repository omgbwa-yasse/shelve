{{-- Actions Version Document --}}
<div class="btn-group btn-group-sm" role="group" aria-label="Actions version">
    {{-- Download --}}
    @if($version->attachment)
        <a href="{{ route('documents.versions.download', [$currentDocument, $version->version_number]) }}"
           class="btn btn-outline-primary" title="Télécharger cette version">
            <i class="fas fa-download"></i>
        </a>
    @endif

    {{-- Restore (si pas version courante) --}}
    @if(!$version->is_current_version)
        <form action="{{ route('documents.versions.restore', [$currentDocument, $version->version_number]) }}"
              method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-success" title="Restaurer cette version"
                    onclick="return confirm('Restaurer la version {{ $version->version_number }} ? Cela créera une nouvelle version.')">
                <i class="fas fa-undo"></i> Restaurer
            </button>
        </form>
    @else
        <span class="btn btn-success disabled" title="Version courante active">
            <i class="fas fa-check"></i> Actuelle
        </span>
    @endif
</div>
