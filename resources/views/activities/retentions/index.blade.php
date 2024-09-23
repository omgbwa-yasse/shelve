@extends('layouts.app')

@section('content')
    <h1>Règles de conservation associer à l'activité : </h1>
    Activité : <strong>{{ $activity->name }}</strong>
    Description : {{ $activity->description ?? 'SD' }}
    <hr>
    <p><a href="{{ route('activities.retentions.create', $activity->id) }}" class="btn btn-primary">Ajouter une règle</a></p>
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
                        <form action="{{ route('activities.retentions.destroy', [$activity->id, $retention->id]) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
