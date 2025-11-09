@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-speedometer2"></i> {{ __('Public Module Dashboard') }}
                    </h5>
                    <a href="{{ route('public.statistics') }}" class="btn btn-primary">
                        <i class="bi bi-bar-chart"></i> {{ __('View Statistics') }}
                    </a>
                </div>

                <div class="card-body">
                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <h1 class="text-primary mb-2">{{ $totalUsers }}</h1>
                                    <p class="text-muted mb-0">
                                        <i class="bi bi-people-fill"></i> {{ __('Total Users') }}
                                    </p>
                                    <a href="{{ route('public.users.index') }}" class="btn btn-sm btn-outline-primary mt-2">
                                        {{ __('View All') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <h1 class="text-success mb-2">{{ $activeUsers }}</h1>
                                    <p class="text-muted mb-0">
                                        <i class="bi bi-check-circle-fill"></i> {{ __('Active Users') }}
                                    </p>
                                    <small class="text-muted">{{ __('Approved accounts') }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-warning">
                                <div class="card-body text-center">
                                    <h1 class="text-warning mb-2">{{ $pendingUsers }}</h1>
                                    <p class="text-muted mb-0">
                                        <i class="bi bi-clock-fill"></i> {{ __('Pending Users') }}
                                    </p>
                                    <small class="text-muted">{{ __('Awaiting approval') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Users -->
                    <div class="row">
                        <div class="col-md-12">
                            <h6 class="text-muted mb-3">
                                <i class="bi bi-clock-history"></i> {{ __('Recent Users') }}
                            </h6>
                            @if($recentUsers->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Name') }}</th>
                                                <th>{{ __('Email') }}</th>
                                                <th>{{ __('Phone') }}</th>
                                                <th>{{ __('Status') }}</th>
                                                <th>{{ __('Registered') }}</th>
                                                <th>{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentUsers as $user)
                                                <tr>
                                                    <td>{{ $user->first_name }} {{ $user->name }}</td>
                                                    <td>{{ $user->email }}</td>
                                                    <td>{{ $user->phone1 ?? '-' }}</td>
                                                    <td>
                                                        @if($user->is_approved)
                                                            <span class="badge bg-success">{{ __('Active') }}</span>
                                                        @else
                                                            <span class="badge bg-warning">{{ __('Pending') }}</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $user->created_at->diffForHumans() }}</td>
                                                    <td>
                                                        <a href="{{ route('public.users.show', $user) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-eye"></i> {{ __('View') }}
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info text-center">
                                    <i class="bi bi-info-circle"></i> {{ __('No users registered yet') }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="text-muted mb-3">
                                <i class="bi bi-link-45deg"></i> {{ __('Quick Links') }}
                            </h6>
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <a href="{{ route('public.users.index') }}" class="btn btn-outline-primary w-100">
                                        <i class="bi bi-people"></i> {{ __('Users') }}
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="{{ route('public.news.index') }}" class="btn btn-outline-info w-100">
                                        <i class="bi bi-newspaper"></i> {{ __('News') }}
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="{{ route('public.events.index') }}" class="btn btn-outline-success w-100">
                                        <i class="bi bi-calendar-event"></i> {{ __('Events') }}
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="{{ route('public.pages.index') }}" class="btn btn-outline-warning w-100">
                                        <i class="bi bi-file-earmark-text"></i> {{ __('Pages') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
