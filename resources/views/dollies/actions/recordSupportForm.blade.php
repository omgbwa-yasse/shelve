@extends('layouts.app')

@section('content')
        <h1>Changer de support de conservation : {{ $dolly->name }}</h1>
            <p>{{ $dolly->description }}</p>
            <form action="" method="GET">
                @csrf
                <div class="mb-3">
                    <div class="select-with-search">
                        <select name="support_id" id="support_id" class="form-select" required>
                        @foreach ($supports as $support)
                            <option value="{{ $support->id }}">{{ $support->name }} </option>
                        @endforeach
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Changer</button>
        </form>
@endsection
