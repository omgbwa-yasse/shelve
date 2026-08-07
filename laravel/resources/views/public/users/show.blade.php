@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <h5 class="mb-0">{{ __('User Details') }}</h5>
                        <div class="btn-toolbar" role="toolbar">
                            <div class="btn-group me-2" role="group">
                                @if($user->is_approved)
                                    <form action="{{ route('public.users.deactivate', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('{{ __('Are you sure you want to deactivate this user?') }}')">
                                            <i class="bi bi-x-circle"></i> {{ __('Deactivate') }}
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('public.users.activate', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('{{ __('Are you sure you want to activate this user?') }}')">
                                            <i class="bi bi-check-circle"></i> {{ __('Activate') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                            <div class="btn-group" role="group">
                                <a href="{{ route('public.users.edit', $user) }}" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil"></i> {{ __('Edit') }}
                                </a>
                                <a href="{{ route('public.users.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-arrow-left"></i> {{ __('Back') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Informations personnelles -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-person"></i> {{ __('Personal Information') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong>{{ __('First Name') }}:</strong>
                                </div>
                                <div class="col-md-8">
                                    {{ $user->first_name }}
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong>{{ __('Last Name') }}:</strong>
                                </div>
                                <div class="col-md-8">
                                    {{ $user->name }}
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong>{{ __('Email') }}:</strong>
                                </div>
                                <div class="col-md-8">
                                    <span class="text-break">{{ $user->email }}</span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong>{{ __('Address') }}:</strong>
                                </div>
                                <div class="col-md-8">
                                    {{ $user->address ?? '-' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informations de contact -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-telephone"></i> {{ __('Contact Information') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong>{{ __('Phone 1') }}:</strong>
                                </div>
                                <div class="col-md-8">
                                    {{ $user->phone1 ?? '-' }}
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong>{{ __('Phone 2') }}:</strong>
                                </div>
                                <div class="col-md-8">
                                    {{ $user->phone2 ?? '-' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informations système -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-gear"></i> {{ __('System Information') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong>{{ __('Status') }}:</strong>
                                </div>
                                <div class="col-md-8">
                                    <span class="badge badge-{{ $user->is_approved ? 'success' : 'warning' }}">
                                        {{ $user->is_approved ? __('Approved') : __('Pending Approval') }}
                                    </span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong>{{ __('Created At') }}:</strong>
                                </div>
                                <div class="col-md-8">
                                    {{ $user->created_at->format('d/m/Y H:i') }}
                                    <small class="text-muted">({{ $user->created_at->diffForHumans() }})</small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong>{{ __('Updated At') }}:</strong>
                                </div>
                                <div class="col-md-8">
                                    {{ $user->updated_at->format('d/m/Y H:i') }}
                                    <small class="text-muted">({{ $user->updated_at->diffForHumans() }})</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Zone dangereuse -->
                    <div class="card border-danger">
                        <div class="card-header bg-danger text-white">
                            <h6 class="mb-0"><i class="bi bi-exclamation-triangle"></i> {{ __('Danger Zone') }}</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">{{ __('Once you delete this user, there is no going back. Please be certain.') }}</p>
                            <form action="{{ route('public.users.destroy', $user) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('{{ __('Are you sure you want to delete this user?') }}')">
                                    <i class="bi bi-trash"></i> {{ __('Delete User') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
