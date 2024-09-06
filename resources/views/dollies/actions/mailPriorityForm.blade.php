@extends('layouts.app')

@section('content')
    <div class="container">
            <h1>Changer de priorité : {{ $dolly->name }}</h1>
                <p>{{ $dolly->description }}</p>
                <form action="" method="GET">
                    @csrf
                    <div class="mb-3">
                        <div class="select-with-search">
                            <select name="level_id" id="level_id" class="form-select" required>
                            @foreach ($priorities as $priority)
                                <option value="{{ $priority->id }}">{{ $priority->name }} </option>
                            @endforeach
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Changer</button>
            </form>


@endsection
