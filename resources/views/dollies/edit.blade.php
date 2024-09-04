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
            <label for="type_id" class="form-label">Type</label>
            <select name="type_id" id="type_id" class="form-select" required>
                @foreach (\App\Models\DollyType::all() as $type)
                <option value="{{ $type->id }}" {{ $type->id == $dolly->type_id ? 'selected' : '' }}>
                    @if($type->name == 'record')
                        Description des archives
                    @elseif($type->name == 'mail')
                        Courrier
                    @elseif($type->name == 'communication')
                        Communication des archives
                    @elseif($type->name == 'room')
                        Salle d'archives
                    @elseif($type->name == 'building')
                        Bâtiments d'archives
                    @elseif($type->name == 'container')
                        Boites d'archives et chronos
                    @elseif($type->name == 'shelve')
                        Etagère
                    @elseif($type->name == 'slip')
                        Versement
                    @elseif($type->name == 'slip_record')
                        Description de versement
                    @endif
                </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
