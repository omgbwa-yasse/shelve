@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4><i class="bi bi-clock-history"></i> {{ __('Version History') }}</h4>
                    <a href="{{ route('system.updates.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> {{ __('Back to Updates') }}
                    </a>
                </div>
                <div class="card-body">
                    @if(count($history) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ __('Version') }}</th>
                                        <th>{{ __('Previous Version') }}</th>
                                        <th>{{ __('Installation Date') }}</th>
                                        <th>{{ __('Installation Method') }}</th>
                                        <th>{{ __('Installed By') }}</th>
                                        <th>{{ __('Rollback') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($history as $version)
                                        <tr class="{{ $version['is_rollback'] ? 'table-warning' : '' }}">
                                            <td>
                                                <span class="badge bg-primary">{{ $version['version'] }}</span>
                                                @if($version['is_rollback'])
                                                    <span class="badge bg-warning ms-1">{{ __('Rollback') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($version['previous_version'])
                                                    <span class="badge bg-secondary">{{ $version['previous_version'] }}</span>
                                                @else
                                                    <span class="text-muted">{{ __('Initial Installation') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($version['installed_at'])->format('d/m/Y H:i:s') }}
                                                <br>
                                                <small class="text-muted">
                                                    {{ \Carbon\Carbon::parse($version['installed_at'])->diffForHumans() }}
                                                </small>
                                            </td>
                                            <td>
                                                @switch($version['installation_method'])
                                                    @case('github')
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-github"></i> GitHub
                                                        </span>
                                                        @break
                                                    @case('manual')
                                                        <span class="badge bg-info">
                                                            <i class="bi bi-hand-index"></i> {{ __('Manual') }}
                                                        </span>
                                                        @break
                                                    @case('auto')
                                                        <span class="badge bg-primary">
                                                            <i class="bi bi-robot"></i> {{ __('Automatic') }}
                                                        </span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">{{ $version['installation_method'] }}</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                @if(isset($version['installed_by_user']))
                                                    <div>
                                                        <strong>{{ $version['installed_by_user']['name'] ?? 'N/A' }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $version['installed_by_user']['email'] ?? 'N/A' }}</small>
                                                    </div>
                                                @else
                                                    <span class="text-muted">{{ __('System') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($version['is_rollback'])
                                                    <i class="bi bi-check-circle text-success"></i> {{ __('Yes') }}
                                                @else
                                                    <i class="bi bi-x-circle text-muted"></i> {{ __('No') }}
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    @if($version['changelog'])
                                                        <button class="btn btn-sm btn-outline-info"
                                                                onclick="viewVersionChangelog('{{ $version['version'] }}')">
                                                            <i class="bi bi-file-text"></i> {{ __('Changelog') }}
                                                        </button>
                                                    @endif

                                                    @if(!$loop->first && !$version['is_rollback'])
                                                        <button class="btn btn-sm btn-outline-warning"
                                                                onclick="rollbackToVersion('{{ $version['version'] }}')">
                                                            <i class="bi bi-arrow-counterclockwise"></i> {{ __('Rollback') }}
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="bi bi-info-circle"></i> {{ __('No version history available') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Changelog Modal -->
<div class="modal fade" id="changelogModal" tabindex="-1" aria-labelledby="changelogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changelogModalLabel">{{ __('Version Changelog') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="changelogContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">{{ __('Loading...') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function viewVersionChangelog(version) {
    const modal = new bootstrap.Modal(document.getElementById('changelogModal'));
    const content = document.getElementById('changelogContent');

    // Find changelog from history data
    const history = @json($history);
    const versionData = history.find(v => v.version === version);

    content.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">{{ __("Loading...") }}</span></div></div>';
    modal.show();

    if (versionData && versionData.changelog) {
        setTimeout(() => {
            content.innerHTML = `<pre class="bg-light p-3 rounded">${versionData.changelog}</pre>`;
        }, 500);
    } else {
        setTimeout(() => {
            content.innerHTML = '<div class="alert alert-info">{{ __("No changelog available for this version") }}</div>';
        }, 500);
    }
}

function rollbackToVersion(version) {
    if (!confirm(`{{ __("Are you sure you want to rollback to version") }} ${version}? {{ __("This operation cannot be undone.") }}`)) {
        return;
    }

    // This would typically require a backup path
    // For now, show a message that this feature needs to be implemented
    alert('{{ __("Rollback functionality requires backup path. Please use the main updates page.") }}');
}
</script>
@endpush
