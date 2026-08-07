@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <h1 class="mb-4">{{ __('settings') }}</h1>

    <div class="row g-4">
        <!-- Gestion des Paramètres -->
        <div class="col-12">
            <div class="card rounded-4 shadow-sm border-primary">
                <div class="card-header bg-primary text-white rounded-top-4">
                    <h5 class="mb-0"><i class="bi bi-gear-wide me-2"></i>{{ __('Gestion des Paramètres') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-grid">
                                <a href="{{ route('settings.categories.index') }}" class="btn btn-outline-primary rounded-3 h-100">
                                    <div class="text-center py-2">
                                        <i class="bi bi-folder-plus fs-2 d-block mb-2"></i>
                                        <strong>{{ __('Catégories') }}</strong>
                                        <div class="small text-muted">{{ __('Gérer les catégories de paramètres') }}</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-grid">
                                <a href="{{ route('settings.definitions.index') }}" class="btn btn-outline-primary rounded-3 h-100">
                                    <div class="text-center py-2">
                                        <i class="bi bi-gear-wide-connected fs-2 d-block mb-2"></i>
                                        <strong>{{ __('Paramètres') }}</strong>
                                        <div class="small text-muted">{{ __('Gérer les paramètres et leurs valeurs') }}</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mon Compte -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 rounded-4 shadow-sm">
                <div class="card-header bg-primary text-white rounded-top-4">
                    <h5 class="mb-0"><i class="bi bi-person-circle me-2"></i>{{ __('my_account') }}</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('users.show', auth()->user()->id) }}" class="btn btn-outline-primary rounded-3">
                            <i class="bi bi-gear me-2"></i>{{ __('my_account') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Autorisations et postes -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 rounded-4 shadow-sm">
                <div class="card-header bg-primary text-white rounded-top-4">
                    <h5 class="mb-0"><i class="bi bi-people me-2"></i>{{ __('authorizations_and_positions') }}</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('users.index') }}" class="btn btn-outline-primary rounded-3">
                            <i class="bi bi-person me-2"></i>{{ __('users') }}
                        </a>
                        <a href="{{ route('user-organisation-role.index') }}" class="btn btn-outline-primary rounded-3">
                            <i class="bi bi-diagram-3 me-2"></i>{{ __('assigned_positions') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Droits et permissions -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 rounded-4 shadow-sm">
                <div class="card-header bg-info text-white rounded-top-4">
                    <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>{{ __('rights_and_permissions') }}</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('roles.index') }}" class="btn btn-outline-info rounded-3">
                            <i class="bi bi-person-badge me-2"></i>{{ __('roles') }}
                        </a>
                        <a href="{{ route('role_permissions.index') }}" class="btn btn-outline-info rounded-3">
                            <i class="bi bi-key me-2"></i>{{ __('assign_permissions') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tâches -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 rounded-4 shadow-sm">
                <div class="card-header bg-success text-white rounded-top-4">
                    <h5 class="mb-0"><i class="bi bi-list-task me-2"></i>{{ __('tasks') }}</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('taskstatus.index') }}" class="btn btn-outline-success rounded-3">
                            <i class="bi bi-tag me-2"></i>{{ __('task_types') }}
                        </a>
                        <a href="{{ route('tasktype.index') }}" class="btn btn-outline-success rounded-3">
                            <i class="bi bi-flag me-2"></i>{{ __('task_statuses') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Courrier -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 rounded-4 shadow-sm">
                <div class="card-header bg-warning text-dark rounded-top-4">
                    <h5 class="mb-0"><i class="bi bi-envelope me-2"></i>{{ __('mail') }}</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('mail-typology.index') }}" class="btn btn-outline-warning rounded-3">
                            <i class="bi bi-tags me-2"></i>{{ __('typologies') }}
                        </a>
                        <a href="{{ route('mail-action.index') }}" class="btn btn-outline-warning rounded-3">
                            <i class="bi bi-play-circle me-2"></i>{{ __('actions') }}
                        </a>
                        <a href="{{ route('mail-priority.index') }}" class="btn btn-outline-warning rounded-3">
                            <i class="bi bi-flag me-2"></i>{{ __('priorities') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Répertoire -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 rounded-4 shadow-sm">
                <div class="card-header bg-danger text-white rounded-top-4">
                    <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>{{ __('directory') }}</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('record-supports.index') }}" class="btn btn-outline-danger rounded-3">
                            <i class="bi bi-hdd me-2"></i>{{ __('supports') }}
                        </a>
                        <a href="{{ route('record-statuses.index') }}" class="btn btn-outline-danger rounded-3">
                            <i class="bi bi-flag me-2"></i>{{ __('statuses') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transfert -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 rounded-4 shadow-sm">
                <div class="card-header bg-secondary text-white rounded-top-4">
                    <h5 class="mb-0"><i class="bi bi-arrow-right-circle me-2"></i>{{ __('transfer') }}</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('transferring-status.index') }}" class="btn btn-outline-secondary rounded-3">
                            <i class="bi bi-flag me-2"></i>{{ __('statuses') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Communication -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 rounded-4 shadow-sm">
                <div class="card-header bg-info text-white rounded-top-4">
                    <h5 class="mb-0"><i class="bi bi-chat-dots me-2"></i>{{ __('communication') }}</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                    </div>
                </div>
            </div>
        </div>

        <!-- Dépôt -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 rounded-4 shadow-sm">
                <div class="card-header bg-dark text-white rounded-top-4">
                    <h5 class="mb-0"><i class="bi bi-building me-2"></i>{{ __('deposit') }}</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('container-status.index') }}" class="btn btn-outline-dark rounded-3">
                            <i class="bi bi-flag me-2"></i>{{ __('container_statuses') }}
                        </a>
                        <a href="{{ route('container-property.index') }}" class="btn btn-outline-dark rounded-3">
                            <i class="bi bi-list-check me-2"></i>{{ __('container_property') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Outils de gestion -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 rounded-4 shadow-sm">
                <div class="card-header bg-success text-white rounded-top-4">
                    <h5 class="mb-0"><i class="bi bi-gear me-2"></i>{{ __('management_tools') }}</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('sorts.index') }}" class="btn btn-outline-success rounded-3">
                            <i class="bi bi-sort-alpha-down me-2"></i>{{ __('retention_final_sorts') }}
                        </a>
                        <a href="{{ route('languages.index') }}" class="btn btn-outline-success rounded-3">
                            <i class="bi bi-translate me-2"></i>{{ __('thesaurus_languages') }}
                        </a>
                        <a href="{{ route('term-categories.index') }}" class="btn btn-outline-success rounded-3">
                            <i class="bi bi-bookmark me-2"></i>{{ __('thesaurus_categories') }}
                        </a>
                        <a href="{{ route('term-equivalent-types.index') }}" class="btn btn-outline-success rounded-3">
                            <i class="bi bi-arrows-angle-expand me-2"></i>{{ __('thesaurus_equivalents') }}
                        </a>
                        <a href="{{ route('term-types.index') }}" class="btn btn-outline-success rounded-3">
                            <i class="bi bi-type me-2"></i>{{ __('thesaurus_types') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Système -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 rounded-4 shadow-sm">
                <div class="card-header bg-primary text-white rounded-top-4">
                    <h5 class="mb-0"><i class="bi bi-cpu me-2"></i>{{ __('system') }}</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('backups.index')}}" class="btn btn-outline-primary rounded-3">
                            <i class="bi bi-save me-2"></i>{{ __('my_backups') }}
                        </a>
                        <a href="{{ route('backups.create')}}" class="btn btn-outline-primary rounded-3">
                            <i class="bi bi-plus-square me-2"></i>{{ __('new_backup') }}
                        </a>
                        <a href="" class="btn btn-outline-primary rounded-3">
                            <i class="bi bi-people me-2"></i>{{ __('ldap') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
