@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Workflows de l\'Organisation') }}</h3>
                </div>
                <div class="card-body">
                    @if($workflows && count($workflows) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ __('Nom') }}</th>
                                        <th>{{ __('Template') }}</th>
                                        <th>{{ __('Statut') }}</th>
                                        <th>{{ __('Étape actuelle') }}</th>
                                        <th>{{ __('Date de création') }}</th>
                                        <th>{{ __('Date de fin') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($workflows as $workflow)
                                        <tr>
                                            <td>{{ $workflow->name }}</td>
                                            <td>{{ $workflow->template->name ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge badge-{{ $workflow->status === 'completed' ? 'success' : ($workflow->status === 'running' ? 'primary' : ($workflow->status === 'paused' ? 'warning' : 'secondary')) }}">
                                                    {{ ucfirst($workflow->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($workflow->currentStep)
                                                    {{ $workflow->currentStep->name }}
                                                @else
                                                    {{ __('Aucune') }}
                                                @endif
                                            </td>
                                            <td>{{ $workflow->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                @if($workflow->completed_at)
                                                    {{ $workflow->completed_at->format('d/m/Y H:i') }}
                                                @else
                                                    {{ __('En cours') }}
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('workflows.instances.show', $workflow->id) }}" class="btn btn-sm btn-primary">
                                                    {{ __('Voir') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            {{ __('Aucun workflow trouvé pour votre organisation.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
