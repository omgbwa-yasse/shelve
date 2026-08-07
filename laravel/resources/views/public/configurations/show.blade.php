@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ ucfirst(str_replace('_', ' ', $configuration)) }}</h5>
                    <div>
                        <a href="{{ route('public.configurations.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> {{ __('Back') }}
                        </a>
                        <form action="{{ route('public.configurations.reset', $configuration) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-warning" onclick="return confirm('{{ __('Are you sure you want to reset this configuration?') }}')">
                                <i class="bi bi-arrow-counterclockwise"></i> {{ __('Reset to Defaults') }}
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('public.configurations.update', $configuration) }}" method="POST">
                        @csrf
                        @method('PUT')

                        @if(is_array($config))
                            @foreach($config as $key => $value)
                                <div class="mb-3">
                                    <label for="{{ $key }}" class="form-label">
                                        {{ ucfirst(str_replace('_', ' ', $key)) }}
                                    </label>

                                    @if(is_bool($value))
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="{{ $key }}" name="{{ $key }}" {{ $value ? 'checked' : '' }}>
                                        </div>
                                    @elseif(is_array($value))
                                        <textarea class="form-control" id="{{ $key }}" name="{{ $key }}" rows="5">{{ json_encode($value, JSON_PRETTY_PRINT) }}</textarea>
                                    @else
                                        <input type="text" class="form-control" id="{{ $key }}" name="{{ $key }}" value="{{ $value }}">
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <div class="alert alert-info">
                                {{ __('No configuration settings available') }}
                            </div>
                        @endif

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> {{ __('Save Changes') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
