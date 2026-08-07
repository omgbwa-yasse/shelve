@extends('layouts.app')

@section('content')
    <h1>Create New Backup Planning for Backup {{ $backup->id }}</h1>
    <form action="{{ route('backups.plannings.store', $backup->id) }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="frequence" class="form-label">Frequency</label>
            <select name="frequence" id="frequence" class="form-select" required>
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="week_day" class="form-label">Week Day</label>
            <input type="number" name="week_day" id="week_day" class="form-control" min="1" max="7">
        </div>
        <div class="mb-3">
            <label for="month_day" class="form-label">Month Day</label>
            <input type="number" name="month_day" id="month_day" class="form-control" min="1" max="31">
        </div>
        <div class="mb-3">
            <label for="hour" class="form-label">Hour</label>
            <input type="time" name="hour" id="hour" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Create Backup Planning</button>
    </form>
@endsection
