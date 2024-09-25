@extends('layouts.app')

@section('content')
    <h1>Create New Communication Record</h1>
    <form action="{{ route('transactions.records.store', $communication) }}" method="POST">
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
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
@endsection
