@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('OPAC Configurations') }}</h5>
                    <div>
                        <button type="button" class="btn btn-success" onclick="exportConfigurations()">
                            <i class="bi bi-download"></i> {{ __('Export') }}
                        </button>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="bi bi-upload"></i> {{ __('Import') }}
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="list-group">
                        @if($configurations)
                            @foreach($configurations as $key => $config)
                                <a href="{{ route('public.configurations.show', $key) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ ucfirst(str_replace('_', ' ', $key)) }}</h6>
                                        <small class="text-muted">
                                            <i class="bi bi-chevron-right"></i>
                                        </small>
                                    </div>
                                    @if(is_array($config))
                                        <small class="text-muted">{{ count($config) }} {{ __('settings') }}</small>
                                    @endif
                                </a>
                            @endforeach
                        @else
                            <p class="text-muted">{{ __('No configurations available') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('public.configurations.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">{{ __('Import Configurations') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="configFile" class="form-label">{{ __('Select Configuration File') }}</label>
                        <input type="file" class="form-control" id="configFile" name="config_file" accept=".json" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Import') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function exportConfigurations() {
    window.location.href = "{{ route('public.configurations.export') }}";
}
</script>
@endsection
