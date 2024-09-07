@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Changer de Date : {{ $dolly->name }}</h1>
        <p>{{ $dolly->description }}</p>
        <form action="" method="GET">
            @csrf
            <input type="text" name="categ" value="dates" hidden>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="return_date" class="form-label">Date exacte</label>
                    <input type="date" class="form-control" id="return_date" name="return_date">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Rechercher</button>
        </form>
@endsection
