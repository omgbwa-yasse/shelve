@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4><i class="bi bi-arrow-clockwise"></i> {{ __('System Updates') }}</h4>
                    <button class="btn btn-outline-primary" onclick="checkForUpdates()">
                        <i class="bi bi-arrow-clockwise"></i> {{ __('Check for Updates') }}
                    </button>
                </div>
                <div class="card-body">
                    <!-- Current Version Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bi bi-info-circle text-primary"></i> {{ __('Current Version') }}
                                    </h5>
                                    <h3 class="text-primary">{{ $currentVersion ?? 'Unknown' }}</h3>
                                    @if(isset($versionInfo['installed_at']))
                                        <small class="text-muted">
                                            {{ __('Installed on') }}: {{ \Carbon\Carbon::parse($versionInfo['installed_at'])->format('d/m/Y H:i') }}
                                        </small>
                                    @endif
                                    @if(isset($versionInfo['updated_from']))
                                        <br><small class="text-muted">
                                            {{ __('Updated from') }}: {{ $versionInfo['updated_from'] }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bi bi-clock text-info"></i> {{ __('Last Check') }}
                                    </h5>
                                    @if(isset($versionInfo['last_check']))
                                        <p>{{ \Carbon\Carbon::parse($versionInfo['last_check'])->diffForHumans() }}</p>
                                    @else
                                        <p class="text-muted">{{ __('Never') }}</p>
                                    @endif
                                    <div id="updateStatus" class="mt-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Available Versions -->
                    <div class="row">
                        <div class="col-md-12">
                            <h5><i class="bi bi-download"></i> {{ __('Available Versions') }}</h5>
                            <div id="versionsContainer">
                                @if(count($availableVersions) > 0)
                                    <div class="row">
                                        @foreach($availableVersions as $version)
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="card {{ $version['is_current'] ? 'border-primary' : ($version['is_newer'] ? 'border-success' : '') }}">
                                                    <div class="card-body">
                                                        <h6 class="card-title d-flex justify-content-between">
                                                            {{ $version['tag_name'] }}
                                                            @if($version['is_current'])
                                                                <span class="badge bg-primary">{{ __('Current') }}</span>
                                                            @elseif($version['is_newer'])
                                                                <span class="badge bg-success">{{ __('Available') }}</span>
                                                            @else
                                                                <span class="badge bg-secondary">{{ __('Older') }}</span>
                                                            @endif
                                                        </h6>
                                                        <p class="card-text">
                                                            <strong>{{ $version['name'] }}</strong>
                                                        </p>
                                                        <small class="text-muted">
                                                            {{ __('Published') }}: {{ \Carbon\Carbon::parse($version['published_at'])->format('d/m/Y') }}
                                                        </small>
                                                        <div class="mt-2">
                                                            @if($version['is_newer'])
                                                                <button class="btn btn-success btn-sm"
                                                                        onclick="updateToVersion('{{ $version['tag_name'] }}')">
                                                                    <i class="bi bi-download"></i> {{ __('Update') }}
                                                                </button>
                                                            @endif
                                                            <button class="btn btn-outline-info btn-sm"
                                                                    onclick="viewChangelog('{{ $version['tag_name'] }}')">
                                                                <i class="bi bi-file-text"></i> {{ __('Changelog') }}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle"></i> {{ __('No versions available. Check your internet connection.') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6>{{ __('Quick Actions') }}</h6>
                                    <button class="btn btn-primary me-2" onclick="updateToLatest()">
                                        <i class="bi bi-arrow-up-circle"></i> {{ __('Update to Latest') }}
                                    </button>
                                    <a href="{{ route('system.updates.history') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-clock-history"></i> {{ __('Version History') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Progress Modal -->
<div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateModalLabel">{{ __('System Update') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="updateProgress">
                    <div class="progress mb-3">
                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                    <p id="updateStatus">{{ __('Preparing update...') }}</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Changelog Modal -->
<div class="modal fade" id="changelogModal" tabindex="-1" aria-labelledby="changelogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changelogModalLabel">{{ __('Changelog') }}</h5>
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
function checkForUpdates() {
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="bi bi-arrow-clockwise"></i> {{ __("Checking...") }}';
    button.disabled = true;

    fetch('{{ route("system.updates.check") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const statusDiv = document.getElementById('updateStatus');
            if (data.data.has_updates) {
                statusDiv.innerHTML = `<div class="alert alert-success">${data.data.message}</div>`;
            } else {
                statusDiv.innerHTML = `<div class="alert alert-info">${data.data.message}</div>`;
            }
        } else {
            alert('{{ __("Error checking for updates") }}: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('{{ __("Error checking for updates") }}');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function updateToVersion(version) {
    if (!confirm(`{{ __("Are you sure you want to update to version") }} ${version}? {{ __("This operation may take several minutes.") }}`)) {
        return;
    }

    const modal = new bootstrap.Modal(document.getElementById('updateModal'));
    modal.show();

    fetch(`{{ url('system/updates/update') }}/${version}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            confirm: true
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('updateStatus').textContent = data.message;
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            document.getElementById('updateStatus').textContent = '{{ __("Error") }}: ' + data.message;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('updateStatus').textContent = '{{ __("Update failed") }}';
    });
}

function updateToLatest() {
    // Find the latest version
    const versions = @json($availableVersions);
    const newerVersions = versions.filter(v => v.is_newer);

    if (newerVersions.length > 0) {
        const latestVersion = newerVersions[0].tag_name;
        updateToVersion(latestVersion);
    } else {
        alert('{{ __("No updates available") }}');
    }
}

function viewChangelog(version) {
    const modal = new bootstrap.Modal(document.getElementById('changelogModal'));
    const content = document.getElementById('changelogContent');

    content.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">{{ __("Loading...") }}</span></div></div>';
    modal.show();

    fetch(`{{ url('api/system/updates/changelog') }}/${version}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            content.innerHTML = `<pre>${data.data.changelog || '{{ __("No changelog available") }}'}</pre>`;
        } else {
            content.innerHTML = '<div class="alert alert-danger">{{ __("Error loading changelog") }}</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        content.innerHTML = '<div class="alert alert-danger">{{ __("Error loading changelog") }}</div>';
    });
}
</script>
@endpush
