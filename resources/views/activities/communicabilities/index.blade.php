@extends('layouts.app')

@section('content')
    <h1>{{ __('Transfer Period Details') }}</h1>
    {{ __('Activity') }}:
        <a href="{{ route('activities.show', $activity->id)}}">
            {{ $activity->code }}: {{ $activity->name }}
        </a>
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
                <tr>
                    <td>{{ $activity->communicability->code }}: {{ $activity->communicability->duration }} {{ __('years') }}, {{ $activity->communicability->name }}</td>
                    <td>
                        <a href="{{ route('activities.communicabilities.edit', [$activity->id, $activity->communicability->id]) }}" class="btn btn-sm btn-primary">{{ __('Edit') }}</a>
                        <form action="{{ route('activities.communicabilities.destroy', [$activity->id, $activity->communicability->id]) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ __('Are you sure you want to delete this communicability?') }}')">{{ __('Delete') }}</button>
                        </form>
                    </td>
                </tr>
        </tbody>
    </table>
@endsection
