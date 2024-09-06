@extends('layouts.app')

@section('content')
<h1>Changer de Date : {{ $dolly->name }}</h1>
<p>{{ $dolly->description }}</p>
<form action="" method="GET">
    @csrf
    <input type="text" name="categ" value="dates" hidden>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="date_exact" class="form-label">Date exacte</label>
            <input type="date" class="form-control" id="date_exact" name="date_exact">
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Changer</button>
</form>



@endsection
