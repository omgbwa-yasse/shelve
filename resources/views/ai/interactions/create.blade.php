@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Create AI Interaction') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('ai.interactions.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="ai_model_id" class="form-label">{{ __('AI Model') }}</label>
                            <select class="form-select @error('ai_model_id') is-invalid @enderror" id="ai_model_id" name="ai_model_id" required>
                                <option value="">{{ __('Select an AI Model') }}</option>
                                @foreach(\App\Models\AiModel::where('is_active', true)->get() as $model)
                                    <option value="{{ $model->id }}">{{ $model->name }}</option>
                                @endforeach
                            </select>
                            @error('ai_model_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="input" class="form-label">{{ __('Input') }}</label>
                            <textarea class="form-control @error('input') is-invalid @enderror" id="input" name="input" rows="4" required>{{ old('input') }}</textarea>
                            @error('input')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="parameters" class="form-label">{{ __('Parameters (JSON)') }}</label>
                            <textarea class="form-control @error('parameters') is-invalid @enderror" id="parameters" name="parameters" rows="3">{{ old('parameters') }}</textarea>
                            @error('parameters')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="module_type" class="form-label">{{ __('Module Type') }}</label>
                            <input type="text" class="form-control @error('module_type') is-invalid @enderror" id="module_type" name="module_type" value="{{ old('module_type') }}">
                            @error('module_type')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="module_id" class="form-label">{{ __('Module ID') }}</label>
                            <input type="number" class="form-control @error('module_id') is-invalid @enderror" id="module_id" name="module_id" value="{{ old('module_id') }}">
                            @error('module_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="session_id" class="form-label">{{ __('Session ID') }}</label>
                            <input type="text" class="form-control @error('session_id') is-invalid @enderror" id="session_id" name="session_id" value="{{ old('session_id') }}">
                            @error('session_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('ai.interactions.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                            <button type="submit" class="btn btn-primary">{{ __('Create Interaction') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection