@extends('opac.layouts.app')

@section('title', __('Register') . ' - OPAC')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="opac-card">
                <div class="opac-card-header text-center">
                    <i class="fas fa-user-plus me-2"></i>{{ __('Create OPAC Account') }}
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('opac.register') }}">
                        @csrf

                        <!-- Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Full Name') }}</label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   required
                                   autocomplete="name"
                                   autofocus>
                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('Email Address') }}</label>
                            <input type="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   id="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required
                                   autocomplete="email">
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
                                   autocomplete="new-password">
                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                            <input type="password"
                                   class="form-control"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   required
                                   autocomplete="new-password">
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox"
                                       class="form-check-input @error('terms') is-invalid @enderror"
                                       id="terms"
                                       name="terms"
                                       value="1"
                                       {{ old('terms') ? 'checked' : '' }}
                                       required>
                                <label class="form-check-label" for="terms">
                                    {{ __('I agree to the') }}
                                    <a href="#" class="text-decoration-none">{{ __('Terms and Conditions') }}</a>
                                    {{ __('and') }}
                                    <a href="#" class="text-decoration-none">{{ __('Privacy Policy') }}</a>
                                </label>
                                @error('terms')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn opac-search-btn">
                                <i class="fas fa-user-plus me-2"></i>{{ __('Create Account') }}
                            </button>
                        </div>

                        <!-- Links -->
                        <div class="text-center">
                            <p class="small mb-0">
                                {{ __('Already have an account?') }}
                                <a href="{{ route('opac.login') }}" class="text-decoration-none">
                                    {{ __('Login here') }}
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Registration Info -->
            <div class="opac-card mt-4">
                <div class="card-body">
                    <h6 class="mb-3">
                        <i class="fas fa-info-circle me-2 text-info"></i>{{ __('Account Benefits') }}
                    </h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            {{ __('Save your search history') }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            {{ __('Create bookmarks and favorites') }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            {{ __('Request documents and services') }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            {{ __('Receive notifications and updates') }}
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-check text-success me-2"></i>
                            {{ __('Access personalized dashboard') }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
