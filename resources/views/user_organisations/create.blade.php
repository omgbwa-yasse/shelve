@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Affecter un utilisateur à un poste</h1>
        <form action="{{ route('user-organisations.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="organisation_id" class="form-label">Choisir une organisation </label>
                <select name="organisation_id" class="form-select" id="organisation_id">
                    @foreach($organisations as $organisation)
                        <option value="{{ $organisation->id }}"> {{ $organisation->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="user_id" class="form-label">Choisir un utilisateur:</label>
                <select name="user_id" class="form-select" id="user_id">
                    @foreach($users as $user)
                        <option value="{{ $user->id }}"> {{ $user->name }}, {{ $user->surname }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Create</button>
        </form>
    </div>
@endsection

