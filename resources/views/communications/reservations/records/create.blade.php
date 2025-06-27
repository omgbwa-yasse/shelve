@extends('layouts.app')

@section('content')
    <h1>Create New reservation Record</h1>
    <form action="{{ route('reservations.records.store', $reservation->id) }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="record_id">Record</label>
            <select name="record_id" id="record_id" class="form-control" required>
                @foreach ($records as $record)
                    <option value="{{ $record->id }}">{{ $record->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="is_original">Is Original</label>
            <select name="is_original" id="is_original" class="form-control" required>
                <option value="1">Yes</option>
                <option value="0">No</option>
            </select>
        </div>
        <div class="form-group">
            <label for="reservation_date">Reservation Date</label>
            <input type="date" name="reservation_date" id="reservation_date" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
@endsection
