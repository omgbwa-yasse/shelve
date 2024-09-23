@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Ajouter une règle </h1>
    Activité : <strong>{{ $activity->code }} - {{ $activity->name }} </strong>
        <table class="table">
            <thead>
                <tr>
                    <th>Retention</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($activity->retentions as $retention)
                    <tr>
                        <td>{{ $retention->code }} - {{ $retention->duration }} ans, {{ $retention->description?? 'sans description' }} </td>
                        <td>
                            <a href="{{ route('activities.retentions.edit', [$activity->id, $retention->id]) }}" class="btn btn-primary btn-sm">Edit</a>
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
        <label for="retention_id">Choisir la règle</label>
        <select class="form-control" id="retention_id" name="retention_id">
            @foreach($retentions as $retention)
            <option value="{{ $retention->id }}">Règle n° {{ $retention->code }} durée {{ $retention->duration }} ans </option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Lier</button>
</form>
@endsection
