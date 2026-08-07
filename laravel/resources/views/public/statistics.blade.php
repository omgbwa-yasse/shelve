@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-bar-chart"></i> {{ __('Public Module Statistics') }}
                    </h5>
                    <a href="{{ route('public.dashboard') }}" class="btn btn-secondary">
                        <i class="bi bi-speedometer2"></i> {{ __('Dashboard') }}
                    </a>
                </div>

                <div class="card-body">
                    <!-- Users Statistics -->
                    @if(isset($stats['users']))
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">
                                <i class="bi bi-people-fill"></i> {{ __('Users Statistics') }}
                            </h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <h3 class="text-primary">{{ $stats['users']['total'] }}</h3>
                                            <p class="text-muted mb-0">{{ __('Total Users') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <h3 class="text-success">{{ $stats['users']['active'] }}</h3>
                                            <p class="text-muted mb-0">{{ __('Active Users') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-warning">
                                        <div class="card-body text-center">
                                            <h3 class="text-warning">{{ $stats['users']['pending'] }}</h3>
                                            <p class="text-muted mb-0">{{ __('Pending Users') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-info">
                                        <div class="card-body text-center">
                                            <h3 class="text-info">{{ $stats['users']['new_this_month'] }}</h3>
                                            <p class="text-muted mb-0">{{ __('New This Month') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- News Statistics -->
                    @if(isset($stats['news']))
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">
                                <i class="bi bi-newspaper"></i> {{ __('News Statistics') }}
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <h3 class="text-primary">{{ $stats['news']['total'] }}</h3>
                                            <p class="text-muted mb-0">{{ __('Total News') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <h3 class="text-success">{{ $stats['news']['published'] }}</h3>
                                            <p class="text-muted mb-0">{{ __('Published News') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Events Statistics -->
                    @if(isset($stats['events']))
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">
                                <i class="bi bi-calendar-event"></i> {{ __('Events Statistics') }}
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <h3 class="text-primary">{{ $stats['events']['total'] }}</h3>
                                            <p class="text-muted mb-0">{{ __('Total Events') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-info">
                                        <div class="card-body text-center">
                                            <h3 class="text-info">{{ $stats['events']['upcoming'] }}</h3>
                                            <p class="text-muted mb-0">{{ __('Upcoming Events') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Document Requests Statistics -->
                    @if(isset($stats['document_requests']))
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">
                                <i class="bi bi-file-earmark-text"></i> {{ __('Document Requests Statistics') }}
                            </h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <h3 class="text-primary">{{ $stats['document_requests']['total'] }}</h3>
                                            <p class="text-muted mb-0">{{ __('Total Requests') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-warning">
                                        <div class="card-body text-center">
                                            <h3 class="text-warning">{{ $stats['document_requests']['pending'] }}</h3>
                                            <p class="text-muted mb-0">{{ __('Pending Requests') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <h3 class="text-success">{{ $stats['document_requests']['completed'] }}</h3>
                                            <p class="text-muted mb-0">{{ __('Completed Requests') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
