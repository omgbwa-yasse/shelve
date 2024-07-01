@extends('layouts.app')

@section('content')
    <h1>Edit Communication Record</h1>
    <form action="{{ route('communication-records.update', $communicationRecord->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="record_id">Record</label>
            <select name="record_id" id="record_id" class="form-control" required>
                @foreach ($records as $record)
                    <option value="{{ $record->id }}" {{ $communicationRecord->record_id == $record->id ? 'selected' : '' }}>{{ $record->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="is_original">Is Original</label>
            <select name="is_original" id="is_original" class="form-control" required>
                <option value="1" {{ $communicationRecord->is_original ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ !$communicationRecord->is_original ? 'selected' : '' }}>No</option>
            </select>
        </div>
        <div class="form-group">
            <label for="return_date">Return Date</label>
            <input type="date" name="return_date" id="return_date" class="form-control" value="{{ $communicationRecord->return_date }}" required>
        </div>
        <div class="form-group">
            <label for="return_effective">Return Effective</label>
            <input type="date" name="return_effective" id="return_effective" class="form-control" value="{{ $communicationRecord->return_effective }}">
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
@endsection
