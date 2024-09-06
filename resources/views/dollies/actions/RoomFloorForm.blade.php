@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="room">
            <h1>Changer de niveau/bâtiment : {{ $dolly->name }}</h1>
            <p>{{ $dolly->description }}</p>
            <form action="" method="GET">
                @csrf
                <div class="mb-3">
                    <div class="select-with-search">
                        <select name="floor_id" id="floor_id" class="form-select" required>
                        @foreach ($floors as $floor)
                            <option value="{{ $floor->id }}">{{ $floor->name }} - {{ $floor->building->name }} </option>
                        @endforeach
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Changer</button>
        </form>

@endsection
