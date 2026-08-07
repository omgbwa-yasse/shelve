@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Event Registrations') }}: {{ $event->title }}</h5>
                    <div>
                        <a href="{{ route('public.events.show', $event->id) }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> {{ __('Back to Event') }}
                        </a>
                        <a href="{{ route('public.event-registrations.export', $event->id) }}" class="btn btn-success">
                            <i class="bi bi-file-excel"></i> {{ __('Export to Excel') }}
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ __('Total Registrations') }}</h6>
                                        <p class="card-text display-4">{{ $registrations->total() }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ __('Available Spots') }}</h6>
                                        <p class="card-text display-4">
                                            {{ $event->max_participants ? ($event->max_participants - $registrations->total()) : '∞' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ __('Registration Status') }}</h6>
                                        <p class="card-text">
                                            @if($event->max_participants)
                                                @if($registrations->total() >= $event->max_participants)
                                                    <span class="badge badge-danger">{{ __('Full') }}</span>
                                                @else
                                                    <span class="badge badge-success">{{ __('Open') }}</span>
                                                @endif
                                            @else
                                                <span class="badge badge-success">{{ __('Open') }}</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Phone') }}</th>
                                    <th>{{ __('Registration Date') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($registrations as $registration)
                                    <tr>
                                        <td>{{ $registration->name }}</td>
                                        <td>{{ $registration->email }}</td>
                                        <td>{{ $registration->phone ?: '-' }}</td>
                                        <td>{{ $registration->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <span class="badge badge-{{ $registration->status === 'confirmed' ? 'success' : ($registration->status === 'pending' ? 'warning' : 'danger') }}">
                                                {{ __(ucfirst($registration->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#notesModal{{ $registration->id }}">
                                                    <i class="bi bi-chat"></i>
                                                </button>
                                                <form action="{{ route('public.event-registrations.destroy', $registration->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ __('Are you sure you want to cancel this registration?') }}')">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                </form>
                                            </div>

                                            <!-- Modal pour les notes -->
                                            <div class="modal fade" id="notesModal{{ $registration->id }}" tabindex="-1" role="dialog" aria-labelledby="notesModalLabel{{ $registration->id }}" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="notesModalLabel{{ $registration->id }}">{{ __('Registration Notes') }}</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>{{ $registration->notes ?: __('No notes provided.') }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $registrations->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
