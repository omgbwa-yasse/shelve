@extends('opac.layouts.app')

@section('title', __('Login') . ' - OPAC')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="opac-card">
                <div class="opac-card-header text-center">
                    <i class="fas fa-sign-in-alt me-2"></i>{{ __('Login to OPAC') }}
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('opac.login') }}">
                        @csrf

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('Email Address') }}</label>
                            <input type="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   id="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required
                                   autocomplete="email"
                                   autofocus>
                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">{{ __('Password') }}</label>
                            <input type="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   id="password"
                                   name="password"
                                   required
                                   autocomplete="current-password">
                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Remember Me -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox"
                                       class="form-check-input"
                                       id="remember"
                                       name="remember"
                                       {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    {{ __('Remember Me') }}
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn opac-search-btn">
                                <i class="fas fa-sign-in-alt me-2"></i>{{ __('Login') }}
                            </button>
                        </div>

                        <!-- Links -->
                        <div class="text-center">
                            <p class="small mb-2">
                                {{ __("Don't have an account?") }}
                                <a href="{{ route('opac.register') }}" class="text-decoration-none">
                                    {{ __('Register here') }}
                                </a>
                            </p>

                            <!-- Forgot Password Link (if implemented) -->
                            <!--
                            <a href="#" class="text-muted small">
                                {{ __('Forgot your password?') }}
                            </a>
                            -->
                        </div>
                    </form>
                </div>
            </div>

            <!-- Guest Access Info -->
            <div class="opac-card mt-4">
                <div class="card-body text-center">
                    <h6 class="mb-3">{{ __('Guest Access') }}</h6>
                    <p class="small text-muted mb-3">
                        {{ __('You can browse and search our collections without logging in, but some features require an account.') }}
                    </p>
                    <a href="{{ route('opac.search') }}" class="btn btn-outline-primary">
                        <i class="fas fa-search me-2"></i>{{ __('Browse as Guest') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
