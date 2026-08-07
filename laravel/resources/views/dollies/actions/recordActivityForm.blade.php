@extends('layouts.app')
@section('content')

    <div class="container">
        <h1>Changer d'activité : {{ $dolly->name }}</h1>
            <p>{{ $dolly->description }}</p>
            <form action="" method="GET">
                @csrf
                <div class="mb-3">
                    <label for="activity_id" class="form-label"> Activités </label>
                    <div class="select-with-search">
                        <select name="activity_id" id="activity_id" class="form-select" required>
                            <option value="" disabled selected>Enter the activity</option>
                            @foreach ($activities as $activity)
                                <option value="{{ $activity->id }}">{{ $activity->code }} - {{ $activity->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Changer</button>
            </form>

@endsection
