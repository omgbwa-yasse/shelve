@extends('layouts.app')

@section('content')
    <h1>Edit reservation Record</h1>
    <form action="{{ route('communications.reservations.records.update', [$reservation->id, $reservationRecord->id]) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="record_id">Record</label>
            <select name="record_id" id="record_id" class="form-control" required>
                @foreach ($records as $record)
                    <option value="{{ $record->id }}" {{ $reservationRecord->record_id == $record->id ? 'selected' : '' }}>{{ $record->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="is_original">Is Original</label>
            <select name="is_original" id="is_original" class="form-control" required>
                <option value="1" {{ $reservationRecord->is_original ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ !$reservationRecord->is_original ? 'selected' : '' }}>No</option>
            </select>
        </div>
        <div class="form-group">
            <label for="reservation_date">Return Date</label>
            <input type="date" name="reservation_date" id="reservation_date" class="form-control" value="{{ $reservationRecord->reservation_date }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
@endsection
