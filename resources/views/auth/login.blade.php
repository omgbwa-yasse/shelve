@extends('layouts.app')

@section('content')
    <div class="container-fluid bg-primary min-vh-100 d-flex align-items-center justify-content-center">
        <div class="card shadow-lg border-0 rounded-lg" style="width: 400px;">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <h2 class="fw-bold text-primary">SHELVE </h2>
                    <p class="text-muted">votre système d'archivage intégré </p>
                </div>
                <form id="loginForm" method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <input id="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="{{ __('Nom d\'utilisateur') }}">
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <input id="password" type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="{{ __('Mot de passe') }}">
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            {{ __('Connexion') }}
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
            background-color: #f0f0f0 !important;
        }
        .btn-primary {
            background-color: #ff9800;
            border-color: #ff9800;
        }
        .btn-primary:hover {
            background-color: #f57c00;
            border-color: #f57c00;
        }
        .text-primary {
            color: #ff9800 !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.querySelector('#loginForm');
            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();
                // Add loading animation if needed
                setTimeout(() => {
                    this.submit();
                }, 500);
            });
        });
    </script>
@endpush
