@extends('layouts.app')
@section('content')
<div class="container">
    <h1>Recherche par date</h1>

    <form action="{{ route('reservations.sort') }}" method="GET">
        @csrf
        <input type="text" name="categ" value="dates" hidden>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="date_exact" class="form-label">Date exacte</label>
                <input type="date" class="form-control" id="date_exact" name="date_exact">
            </div>
            <div class="col-md-4 mb-3">
                <label for="start_date" class="form-label">Date début</label>
                <input type="date" class="form-control" id="date_start" name="date_start">
            </div>
            <div class="col-md-4 mb-3">
                <label for="date_end" class="form-label">Date fin</label>
                <input type="date" class="form-control" id="date_end" name="date_end">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Rechercher</button>
    </form>

</div>
@endsection
