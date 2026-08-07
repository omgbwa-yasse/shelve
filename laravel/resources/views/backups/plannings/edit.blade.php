@extends('layouts.app')

@section('content')
    <h1>Edit Backup Planning for Backup {{ $backup->id }}</h1>
    <form action="{{ route('backups.plannings.update', [$backup->id, $planning->id]) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="frequence" class="form-label">Frequency</label>
            <select name="frequence" id="frequence" class="form-select" required>
                <option value="daily" {{ $planning->frequence == 'daily' ? 'selected' : '' }}>Daily</option>
                <option value="weekly" {{ $planning->frequence == 'weekly' ? 'selected' : '' }}>Weekly</option>
                <option value="monthly" {{ $planning->frequence == 'monthly' ? 'selected' : '' }}>Monthly</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="week_day" class="form-label">Week Day</label>
            <input type="number" name="week_day" id="week_day" class="form-control" min="1" max="7" value="{{ $planning->week_day }}">
        </div>
        <div class="mb-3">
            <label for="month_day" class="form-label">Month Day</label>
            <input type="number" name="month_day" id="month_day" class="form-control" min="1" max="31" value="{{ $planning->month_day }}">
        </div>
        <div class="mb-3">
            <label for="hour" class="form-label">Hour</label>
            <input type="time" name="hour" id="hour" class="form-control" required value="{{ $planning->hour }}">
        </div>
        <button type="submit" class="btn btn-primary">Update Backup Planning</button>
    </form>
@endsection
