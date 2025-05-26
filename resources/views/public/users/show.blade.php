@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('User Details') }}</h5>
                    <div>
                        <a href="{{ route('public.users.edit', $user) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> {{ __('Edit') }}
                        </a>
                        <a href="{{ route('public.users.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> {{ __('Back') }}
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <strong>{{ __('Name') }}:</strong>
                        </div>
                        <div class="col-md-8">
                            {{ $user->name }}
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <strong>{{ __('Email') }}:</strong>
                        </div>
                        <div class="col-md-8">
                            {{ $user->email }}
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <strong>{{ __('Status') }}:</strong>
                        </div>
                        <div class="col-md-8">
                            <span class="badge badge-{{ $user->is_active ? 'success' : 'danger' }}">
                                {{ $user->is_active ? __('Active') : __('Inactive') }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <strong>{{ __('Created At') }}:</strong>
                        </div>
                        <div class="col-md-8">
                            {{ $user->created_at->format('Y-m-d H:i:s') }}
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <strong>{{ __('Last Updated') }}:</strong>
                        </div>
                        <div class="col-md-8">
                            {{ $user->updated_at->format('Y-m-d H:i:s') }}
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <strong>{{ __('Last Login') }}:</strong>
                        </div>
                        <div class="col-md-8">
                            {{ $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : __('Never') }}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
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
