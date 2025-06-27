@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Ajouter un Chariot</h1>
    <form action="{{ route('dolly.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control" required></textarea>
        </div>
        <div class="mb-3">
            <label for="category" class="form-label">Category</label>
            <select name="category" id="category" class="form-select" required>
                @foreach ($categories as $category)
                <option value="{{ $category }}">
                    @if($category == 'record')
                        Description des archives
                    @elseif($category == 'mail')
                        Courrier
                    @elseif($category == 'communication')
                        Communication des archives
                    @elseif($category == 'room')
                        Salle d'archives
                    @elseif($category == 'building')
                        Bâtiments d'archives
                    @elseif($category == 'container')
                        Boites et chronos
                    @elseif($category == 'shelf')
                        Etagère
                    @elseif($category == 'slip')
                        Versement
                    @elseif($category == 'slip_record')
                        Description de versement
                    @endif
                </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</div>
@endsection
