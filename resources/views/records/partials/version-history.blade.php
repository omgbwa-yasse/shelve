@php($versions = $record->getAllVersions())
@if($versions->count() > 1 || ($record->version_number ?? 1) > 1)
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-clock-history"></i> Historique des versions
        </div>
        <ul class="list-group list-group-flush">
            @foreach($versions as $version)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <a href="{{ route('records.show', $version) }}">v{{ $version->version_number }} — {{ $version->name }}</a>
                    <span>
                        @if($version->is_current_version)
                            <span class="badge bg-success">Courante</span>
                        @else
                            <span class="badge bg-light text-dark">Ancienne</span>
                        @endif
                    </span>
                </li>
            @endforeach
        </ul>
        <div class="card-body">
            <a href="{{ route('records.versions', $record) }}"><i class="bi bi-list"></i> Gérer les versions</a>
        </div>
    </div>
@endif
