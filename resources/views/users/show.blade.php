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
                    <li class="breadcrumb-item active" aria-current="page">{{ __('User Details') }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 mt-2 text-primary">
                <i class="bi bi-person-circle me-2"></i>{{ __('User Details') }}
            </h1>
            <p class="text-muted mb-0">{{ __('Personal Information') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning">
                <i class="bi bi-pencil me-2"></i>{{ __('Edit') }}
            </a>
            <a href="{{ route('settings.home') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Informations personnelles -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person me-2"></i>{{ __('Personal Information') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">{{ __('Name') }}</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-person text-primary"></i>
                                </span>
                                <input type="text" class="form-control" value="{{ $user->name }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">{{ __('Surname') }}</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-person-badge text-primary"></i>
                                </span>
                                <input type="text" class="form-control" value="{{ $user->surname ?? __('Not specified') }}" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">{{ __('Email') }}</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-envelope text-primary"></i>
                                </span>
                                <input type="email" class="form-control" value="{{ $user->email }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">{{ __('Birthday') }}</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-calendar-event text-primary"></i>
                                </span>
                                <input type="date" class="form-control" value="{{ $user->birthday }}" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">{{ __('Account Created') }}</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-calendar-plus text-primary"></i>
                                </span>
                                <input type="text" class="form-control" value="{{ $user->created_at->format('d/m/Y H:i') }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">{{ __('Last Updated') }}</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-calendar-check text-primary"></i>
                                </span>
                                <input type="text" class="form-control" value="{{ $user->updated_at->format('d/m/Y H:i') }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations du compte -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-shield-check me-2"></i>{{ __('Account Information') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small">{{ __('User ID') }}</label>
                        <div class="fw-bold">
                            <code class="bg-light px-2 py-1 rounded">{{ $user->id }}</code>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">{{ __('Account Status') }}</label>
                        <div>
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i>{{ __('Active') }}
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">{{ __('Member Since') }}</label>
                        <div class="small">
                            <i class="bi bi-clock me-1"></i>{{ $user->created_at->diffForHumans() }}
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil me-2"></i>{{ __('Edit Profile') }}
                        </a>
                        <button class="btn btn-outline-secondary btn-sm" onclick="showPasswordModal()">
                            <i class="bi bi-key me-2"></i>{{ __('Change Password') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Organisations affiliées -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-building me-2"></i>{{ __('Affiliated Organizations') }}
                    </h5>
                </div>
                <div class="card-body">
                    @if($user->organisations && $user->organisations->count() > 0)
                        <div class="row">
                            @foreach($user->organisations as $organisation)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card border-0 bg-light h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="organisation-icon me-3">
                                                    <i class="bi bi-building text-primary"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">{{ $organisation->name }}</h6>
                                                    @if($user->current_organisation_id == $organisation->id)
                                                        <span class="badge bg-primary">
                                                            <i class="bi bi-star me-1"></i>{{ __('Current Organization') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($organisation->description)
                                                <p class="text-muted small mb-0">{{ Str::limit($organisation->description, 100) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-building-x fs-1 text-muted mb-3"></i>
                            <h6 class="text-muted">{{ __('No Organizations') }}</h6>
                            <p class="text-muted mb-0">{{ __('This user is not affiliated with any organization.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour changer le mot de passe -->
<div class="modal fade" id="passwordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-key me-2"></i>{{ __('Change Password') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="passwordForm">
                    @csrf
                    <div class="mb-3">
                        <label for="currentPassword" class="form-label">{{ __('Current Password') }}</label>
                        <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">{{ __('New Password') }}</label>
                        <input type="password" class="form-control" id="newPassword" name="password" required>
                        <div class="form-text">{{ __('Minimum 8 characters') }}</div>
                    </div>
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">{{ __('Confirm New Password') }}</label>
                        <input type="password" class="form-control" id="confirmPassword" name="password_confirmation" required>
                    </div>
                    <div id="passwordError" class="alert alert-danger" style="display: none;"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>{{ __('Cancel') }}
                </button>
                <button type="button" class="btn btn-warning" onclick="changePassword()">
                    <i class="bi bi-check-circle me-2"></i>{{ __('Change Password') }}
                </button>
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

.organisation-icon {
    width: 40px;
    height: 40px;
    background: rgba(13, 110, 253, 0.1);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.breadcrumb-item a {
    color: #6c757d;
}

.breadcrumb-item a:hover {
    color: #0d6efd;
}

.input-group-text {
    background-color: rgba(13, 110, 253, 0.1);
    border-color: rgba(13, 110, 253, 0.2);
}

.form-control:read-only {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.badge {
    font-size: 0.75rem;
    padding: 0.5em 0.75em;
}

.modal-content {
    border-radius: 12px;
    border: none;
}

.modal-header {
    border-radius: 12px 12px 0 0;
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
function showPasswordModal() {
    new bootstrap.Modal(document.getElementById('passwordModal')).show();
}

function changePassword() {
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const errorDiv = document.getElementById('passwordError');

    // Validation côté client
    if (!currentPassword || !newPassword || !confirmPassword) {
        showPasswordError('{{ __("All fields are required.") }}');
        return;
    }

    if (newPassword.length < 8) {
        showPasswordError('{{ __("Password must be at least 8 characters long.") }}');
        return;
    }

    if (newPassword !== confirmPassword) {
        showPasswordError('{{ __("New passwords do not match.") }}');
        return;
    }

    // Ici vous pouvez ajouter la logique pour changer le mot de passe
    // Pour l'instant, on affiche juste un message de succès
    alert('{{ __("Password change functionality will be implemented here.") }}');
    
    // Fermer le modal
    bootstrap.Modal.getInstance(document.getElementById('passwordModal')).hide();
    
    // Réinitialiser le formulaire
    document.getElementById('passwordForm').reset();
    errorDiv.style.display = 'none';
}

function showPasswordError(message) {
    const errorDiv = document.getElementById('passwordError');
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
}
</script>
@endsection
