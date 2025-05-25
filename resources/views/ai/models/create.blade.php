@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Create New AI Model</h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('ai.models.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="provider" class="form-label">Provider</label>
                            <input type="text" class="form-control @error('provider') is-invalid @enderror"
                                id="provider" name="provider" value="{{ old('provider') }}" required>
                            @error('provider')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="version" class="form-label">Version</label>
                            <input type="text" class="form-control @error('version') is-invalid @enderror"
                                id="version" name="version" value="{{ old('version') }}" required>
                            @error('version')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="api_type" class="form-label">API Type</label>
                            <input type="text" class="form-control @error('api_type') is-invalid @enderror"
                                id="api_type" name="api_type" value="{{ old('api_type') }}" required>
                            @error('api_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="capabilities" class="form-label">Capabilities (JSON)</label>
                            <textarea class="form-control @error('capabilities') is-invalid @enderror"
                                id="capabilities" name="capabilities" rows="3">{{ old('capabilities') }}</textarea>
                            @error('capabilities')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input @error('is_active') is-invalid @enderror"
                                id="is_active" name="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                            @error('is_active')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('ai.models.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create AI Model</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
