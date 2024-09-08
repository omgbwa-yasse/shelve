@extends('layouts.app')

@section('content')
    <div class="status">
        <h1>Changer le status : {{ $dolly->name }}</h1>
        <p>{{ $dolly->description }}</p>
        <form action="" method="GET">
            @csrf
        <div class="mb-3">
            <div class="select-with-search">
            <select name="level_id" id="level_id" class="form-select" required>
                @foreach ($statuses as $status)
                    <option value="{{ $status->id }}">{{ $status->name }} </option>
                @endforeach
            </select>
            </div>
        </div>
    <button type="submit" class="btn btn-primary">Changer</button>
    </form>
@endsection
