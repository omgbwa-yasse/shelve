@extends('layouts.app')

@section('content')
    <div class="container-fluid bg-primary min-vh-100 d-flex align-items-center justify-content-center">
        <div class="card shadow-lg border-0 rounded-lg" style="width: 400px;">
            <div class="card-body p-4">
                <!-- Logo et titre -->
                <div class="text-center mb-4">
                    <img src="{{ asset('big.svg') }}" class="img-fluid mb-3" style="max-width: 180px;">
                </div>

                <!-- Formulaire -->
                <form id="loginForm" method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email -->
                    <div class="mb-3">
                        <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="bi bi-envelope"></i>
                        </span>
                            <input
                                id="email"
                                type="email"
                                class="form-control form-control-lg @error('email') is-invalid @enderror"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autocomplete="email"
                                autofocus
                                placeholder="{{ __('Nom d\'utilisateur') }}"
                            >
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Mot de passe -->
                    <div class="mb-4">
                        <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="bi bi-lock"></i>
                        </span>
                            <input
                                id="password"
                                type="password"
                                class="form-control form-control-lg @error('password') is-invalid @enderror"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="{{ __('Mot de passe') }}"
                            >
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Se souvenir de moi -->
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label text-muted" for="remember">
                            {{ __('Se souvenir de moi') }}
                        </label>
                    </div>

                    <!-- Bouton de connexion -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg position-relative">
                            <span class="spinner-border spinner-border-sm d-none position-absolute top-50 start-50 translate-middle" role="status"></span>
                            <span class="btn-text">{{ __('Connexion') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .bg-primary {
            background-color: #0D5C7C !important;
        }

        .btn-primary {
            background-color: #0D5C7C;
            border-color: #0D5C7C;
        }

        .btn-primary:hover {
            background-color: #094963;
            border-color: #094963;
        }

        .form-control:focus {
            border-color: #0D5C7C;
            box-shadow: 0 0 0 0.25rem rgba(13, 92, 124, 0.25);
        }

        .input-group-text {
            border: 1px solid #ced4da;
        }

        .form-check-input:checked {
            background-color: #0D5C7C;
            border-color: #0D5C7C;
        }

        .card {
            background-color: rgba(255, 255, 255, 0.98);
        }

        .btn-lg {
            padding: 0.8rem 1rem;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.querySelector('#loginForm');
            const submitButton = loginForm.querySelector('button[type="submit"]');
            const spinner = submitButton.querySelector('.spinner-border');
            const btnText = submitButton.querySelector('.btn-text');

            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();
                submitButton.disabled = true;
                spinner.classList.remove('d-none');
                btnText.classList.add('opacity-0');

                setTimeout(() => {
                    this.submit();
                }, 500);
            });
        });
    </script>
@endpush
