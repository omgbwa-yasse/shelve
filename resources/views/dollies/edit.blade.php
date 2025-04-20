@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Modifier un chariot</h1>
    <form action="{{ route('dolly.update', $dolly) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $dolly->name }}" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control" required>{{ $dolly->description }}</textarea>
        </div>
        <div class="mb-3">
            <label for="category" class="form-label">Type</label>


            <select name="category" id="category" class="form-select" required>
                @foreach ($categories as $category)
                    <option value="{{ $category }}" {{ $dolly->category == $category ? 'selected' : '' }}>
                        @switch($category)
                            @case('record')
                                Description des archives
                                @break
                            @case('mail')
                                Courrier
                                @break
                            @case('communication')
                                Communication des archives
                                @break
                            @case('room')
                                Salle d'archives
                                @break
                            @case('building')
                                Bâtiments d'archives
                                @break
                            @case('container')
                                Boites d'archives et chronos
                                @break
                            @case('shelve')
                                Etagère
                                @break
                            @case('slip')
                                Versement
                                @break
                            @case('slip_record')
                                Description de versement
                                @break
                        @endswitch
                    </option>


                @endforeach
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
