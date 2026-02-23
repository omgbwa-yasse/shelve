@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2 fw-bold text-dark mb-0">
                <i class="bi bi-diagram-3 me-3 text-primary"></i>
                {{ __('child_records') }} — {{ $parent->code }} : {{ $parent->name }}
            </h1>
            <div class="d-flex gap-2">
                <a href="{{ route('records.show', $parent) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> {{ __('back') }}
                </a>
                @can('create', App\Models\RecordPhysical::class)
                <a href="{{ route('record-child.create', $parent) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> {{ __('create') }}
                </a>
                @endcan
            </div>
        </div>

        <!-- Children List -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                @if($parent->children && $parent->children->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('code') }}</th>
                                    <th>{{ __('name') }}</th>
                                    <th>{{ __('level') }}</th>
                                    <th>{{ __('status') }}</th>
                                    <th>{{ __('date') }}</th>
                                    <th class="text-end">{{ __('actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($parent->children as $child)
                                    <tr>
                                        <td>
                                            <a href="{{ route('records.show', $child) }}" class="text-decoration-none fw-semibold">
                                                {{ $child->code }}
                                            </a>
                                        </td>
                                        <td>{{ $child->name }}</td>
                                        <td>{{ $child->level->name ?? '—' }}</td>
                                        <td>
                                            @if($child->status)
                                                <span class="badge bg-secondary">{{ $child->status->name }}</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>{{ $child->date_exact ?? $child->date_start ?? '—' }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('records.show', $child) }}" class="btn btn-outline-primary btn-sm" title="{{ __('show') }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox display-4"></i>
                        <p class="mt-3">{{ __('no_child_records') }}</p>
                        @can('create', App\Models\RecordPhysical::class)
                        <a href="{{ route('record-child.create', $parent) }}" class="btn btn-primary btn-sm mt-2">
                            <i class="bi bi-plus-lg me-1"></i> {{ __('create') }}
                        </a>
                        @endcan
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
