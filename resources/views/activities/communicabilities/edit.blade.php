@extends('layouts.app')

@section('content')
    <h1>Modifier le délai de communicabilité</h1>
    Activité : <strong>{{ $activity->name }}</strong>
    <form action="{{ route('activities.communicabilities.update', [$activity->id, $activity->communicability->id]) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">Choisir le délai</label>
            <select name="communicability_id" id="communicability_id" class="form-select" required>
                <option value="">Choisir un délai</option>
                @foreach ($communicabilities as $communicability)
                    <option value="{{ $communicability->id }}" {{ $communicability->id == $activity->communicability->id ? 'selected' : '' }}>{{ $communicability->code }} - {{ $communicability->name }} : {{ $communicability->description }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
@endsection
