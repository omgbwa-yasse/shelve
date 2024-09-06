@extends('layouts.app')

@section('content')
        <div class="container">
            <h1>Changer de boîtes/box : {{ $dolly->name }}</h1>
                <p>{{ $dolly->description }}</p>
                <form action="" method="GET">
                    @csrf
                    <div class="mb-3">
                        <label for="activity_id" class="form-label"> choisir la boîtes/Box </label>
                        <div class="select-with-search">
                            <select name="activity_id" id="activity_id" class="form-select" required>
                                @foreach ($containers as $container)
                                    <option value="{{ $container->id }}">{{ $container->code }} </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Changer</button>
                </form>
@endsection
