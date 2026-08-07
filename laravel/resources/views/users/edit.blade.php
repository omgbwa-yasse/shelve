@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <!-- Header avec navigation -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('settings.home') }}" class="text-decoration-none">
                            <i class="bi bi-gear me-1"></i>{{ __('settings') }}
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('users.show', $user->id) }}" class="text-decoration-none">
                            <i class="bi bi-person me-1"></i>{{ __('User Details') }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('Edit User') }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 mt-2 text-primary">
                <i class="bi bi-pencil-square me-2"></i>{{ __('Edit User') }}
            </h1>
            <p class="text-muted mb-0">{{ __('Update user information') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('users.show', $user->id) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-person-gear me-2"></i>{{ __('Edit User Information') }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.update', $user->id) }}" method="POST" id="editUserForm">
                        @csrf
                        @method('PUT')

                        <!-- Informations personnelles -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="bi bi-person me-2"></i>{{ __('Personal Information') }}
                                </h6>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">
                                    <i class="bi bi-person text-primary me-1"></i>{{ __('Name') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $user->name) }}" 
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">
                                        <i class="bi bi-exclamation-triangle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="surname" class="form-label">
                                    <i class="bi bi-person-badge text-primary me-1"></i>{{ __('Surname') }}
                                </label>
                                <input type="text" 
                                       class="form-control @error('surname') is-invalid @enderror" 
                                       id="surname" 
                                       name="surname" 
                                       value="{{ old('surname', $user->surname) }}">
                                @error('surname')
                                    <div class="invalid-feedback">
                                        <i class="bi bi-exclamation-triangle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope text-primary me-1"></i>{{ __('Email') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', $user->email) }}" 
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">
                                        <i class="bi bi-exclamation-triangle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="birthday" class="form-label">
                                    <i class="bi bi-calendar-event text-primary me-1"></i>{{ __('Birthday') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       class="form-control @error('birthday') is-invalid @enderror" 
                                       id="birthday" 
                                       name="birthday" 
                                       value="{{ old('birthday', $user->birthday) }}" 
                                       required>
                                @error('birthday')
                                    <div class="invalid-feedback">
                                        <i class="bi bi-exclamation-triangle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Informations du compte -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-info mb-3">
                                    <i class="bi bi-shield-lock me-2"></i>{{ __('Account Information') }}
                                </h6>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>{{ __('Password Change') }}:</strong> 
                                    {{ __('Leave blank to keep current password') }}
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">
                                    <i class="bi bi-key text-primary me-1"></i>{{ __('Password') }}
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                        <i class="bi bi-eye" id="passwordIcon"></i>
                                    </button>
                                </div>
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i>{{ __('Minimum 8 characters') }}
                                </div>
                                @error('password')
                                    <div class="invalid-feedback">
                                        <i class="bi bi-exclamation-triangle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">
                                    <i class="bi bi-key-fill text-primary me-1"></i>{{ __('Confirm Password') }}
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control @error('password_confirmation') is-invalid @enderror" 
                                           id="password_confirmation" 
                                           name="password_confirmation">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                        <i class="bi bi-eye" id="passwordConfirmationIcon"></i>
                                    </button>
                                </div>
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i>{{ __('Required only if changing password') }}
                                </div>
                                @error('password_confirmation')
                                    <div class="invalid-feedback">
                                        <i class="bi bi-exclamation-triangle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Validation du mot de passe -->
                        <div class="row mb-4" id="passwordValidation" style="display: none;">
                            <div class="col-12">
                                <h6 class="text-success mb-3">
                                    <i class="bi bi-check-circle me-2"></i>{{ __('Password Requirements') }}
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul class="list-unstyled">
                                            <li class="mb-2">
                                                <i class="bi bi-circle text-muted me-2" id="lengthCheck"></i>
                                                {{ __('At least 8 characters') }}
                                            </li>
                                            <li class="mb-2">
                                                <i class="bi bi-circle text-muted me-2" id="matchCheck"></i>
                                                {{ __('Passwords match') }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('users.show', $user->id) }}" class="btn btn-secondary">
                                        <i class="bi bi-x-circle me-2"></i>{{ __('Cancel') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-2"></i>{{ __('Update') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
    border-bottom: none;
}

.breadcrumb-item a {
    color: #6c757d;
}

.breadcrumb-item a:hover {
    color: #0d6efd;
}

.form-label {
    font-weight: 500;
    color: #495057;
}

.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.input-group-text {
    background-color: rgba(13, 110, 253, 0.1);
    border-color: rgba(13, 110, 253, 0.2);
}

.alert {
    border-radius: 8px;
    border: none;
}

.alert-info {
    background-color: rgba(13, 202, 240, 0.1);
    color: #055160;
}

.btn {
    border-radius: 8px;
    font-weight: 500;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

@media (max-width: 768px) {
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }
    
    .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
}
</style>

<script>
// Validation en temps réel du mot de passe
document.getElementById('password').addEventListener('input', validatePassword);
document.getElementById('password_confirmation').addEventListener('input', validatePassword);

function validatePassword() {
    const password = document.getElementById('password').value;
    const confirmation = document.getElementById('password_confirmation').value;
    const validationDiv = document.getElementById('passwordValidation');
    
    // Afficher la validation seulement si un mot de passe est saisi
    if (password || confirmation) {
        validationDiv.style.display = 'block';
        
        // Vérifier la longueur
        const lengthCheck = document.getElementById('lengthCheck');
        if (password.length >= 8) {
            lengthCheck.className = 'bi bi-check-circle text-success me-2';
        } else {
            lengthCheck.className = 'bi bi-circle text-muted me-2';
        }
        
        // Vérifier la correspondance
        const matchCheck = document.getElementById('matchCheck');
        if (password && confirmation && password === confirmation) {
            matchCheck.className = 'bi bi-check-circle text-success me-2';
        } else if (password && confirmation) {
            matchCheck.className = 'bi bi-x-circle text-danger me-2';
        } else {
            matchCheck.className = 'bi bi-circle text-muted me-2';
        }
    } else {
        validationDiv.style.display = 'none';
    }
}

// Basculer la visibilité du mot de passe
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId === 'password' ? 'passwordIcon' : 'passwordConfirmationIcon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        field.type = 'password';
        icon.className = 'bi bi-eye';
    }
}

// Validation du formulaire avant soumission
document.getElementById('editUserForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmation = document.getElementById('password_confirmation').value;
    
    // Si un mot de passe est saisi, vérifier qu'il est confirmé
    if (password && !confirmation) {
        e.preventDefault();
        alert('{{ __("Please confirm your password.") }}');
        document.getElementById('password_confirmation').focus();
        return false;
    }
    
    // Si les mots de passe ne correspondent pas
    if (password && confirmation && password !== confirmation) {
        e.preventDefault();
        alert('{{ __("Passwords do not match.") }}');
        document.getElementById('password_confirmation').focus();
        return false;
    }
    
    // Si le mot de passe est trop court
    if (password && password.length < 8) {
        e.preventDefault();
        alert('{{ __("Password must be at least 8 characters long.") }}');
        document.getElementById('password').focus();
        return false;
    }
});

// Animation d'entrée pour les champs
document.addEventListener('DOMContentLoaded', function() {
    const formGroups = document.querySelectorAll('.mb-3');
    formGroups.forEach((group, index) => {
        group.style.opacity = '0';
        group.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            group.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            group.style.opacity = '1';
            group.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
</script>
@endsection
