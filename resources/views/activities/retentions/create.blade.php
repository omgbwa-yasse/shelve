@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('Add Retention Rule') }}</h1>
    {{ __('Activity') }}: <strong>{{ $activity->code }} - {{ $activity->name }} </strong>
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
                            </td>
                    </tr>
                @endforeach
    </tbody>
    </table>
</div>
<hr>

<form action="{{ route('activities.retentions.store', $activity->id) }}" method="POST">
    @csrf
    <div class="form-group">
        <label for="retention_id">{{ __('Choose Retention Rule') }}</label>
        <select class="form-control" id="retention_id" name="retention_id">
            @foreach($retentions as $retention)
            <option value="{{ $retention->id }}">{{ $retention->name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
</form>
@endsection
