@extends('layouts.app')

@section('content')
    <h1>{{ __('Retention Rules for Activity') }}: </h1>
    {{ __('Activity') }}: <strong>{{ $activity->name }}</strong>
    {{ __('Description') }}: {{ $activity->description ?? __('N/A') }}
    <hr>
    <p><a href="{{ route('activities.retentions.create', $activity->id) }}" class="btn btn-primary">{{ __('Add Rule') }}</a></p>
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('Retention') }}</th>
                <th>{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($activity->retentions as $retention)
                <tr>
                    <td>{{ $retention->code }} - {{ $retention->duration }} {{ __('years') }}, {{ $retention->description ?? __('no description') }} </td>
                    <td>
                        <a href="{{ route('activities.retentions.edit', [$activity->id, $retention->id]) }}" class="btn btn-primary btn-sm">{{ __('Edit') }}</a>
                        <form action="{{ route('activities.retentions.destroy', [$activity->id, $retention->id]) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('{{ __('Are you sure you want to delete this retention rule?') }}')">{{ __('Delete') }}</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
