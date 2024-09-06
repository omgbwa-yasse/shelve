@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Changer d'étagère/salle : {{ $dolly->name }}</h1>
            <p>{{ $dolly->description }}</p>
            <form action="" method="GET">
                @csrf
                <div class="mb-3">
                    <div class="select-with-search">
                        <select name="shelf_id" id="shelf_id" class="form-select" required>
                        @foreach ($shelves as $shelf)
                            <option value="{{ $shelf->id }}">{{ $shelf->name }} - {{ $shelf->room->name }} </option>
                        @endforeach
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Changer</button>
        </form>



@endsection
