@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Mes Workflows') }}</h3>
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
                                        <th>{{ __('Mon étape') }}</th>
                                        <th>{{ __('Statut étape') }}</th>
                                        <th>{{ __('Date de création') }}</th>
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
                                                @php
                                                    $myStep = $workflow->steps->where('assignments.assignee_id', auth()->id())->first();
                                                @endphp
                                                @if($myStep)
                                                    {{ $myStep->name }}
                                                @else
                                                    {{ __('Aucune') }}
                                                @endif
                                            </td>
                                            <td>
                                                @if($myStep)
                                                    <span class="badge badge-{{ $myStep->status === 'completed' ? 'success' : ($myStep->status === 'in_progress' ? 'warning' : 'secondary') }}">
                                                        {{ ucfirst($myStep->status) }}
                                                    </span>
                                                @else
                                                    {{ __('N/A') }}
                                                @endif
                                            </td>
                                            <td>{{ $workflow->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <a href="{{ route('workflows.instances.show', $workflow->id) }}" class="btn btn-sm btn-primary">
                                                    {{ __('Voir') }}
                                                </a>
                                                @if($myStep && $myStep->status !== 'completed')
                                                    <a href="{{ route('workflows.step-instances.show', $myStep->id) }}" class="btn btn-sm btn-warning">
                                                        {{ __('Traiter') }}
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            {{ __('Aucun workflow assigné.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
