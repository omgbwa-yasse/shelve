@extends('layouts.app')

@section('content')
    <h1>Backup Planning Details</h1>
    <p><strong>Frequency:</strong> {{ $planning->frequence }}</p>
    <p><strong>Week Day:</strong> {{ $planning->week_day }}</p>
    <p><strong>Month Day:</strong> {{ $planning->month_day }}</p>
    <p><strong>Hour:</strong> {{ $planning->hour }}</p>
    <form action="{{ route('backups.plannings.destroy', [$backup->id, $planning->id]) }}" method="POST" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Delete Backup Planning</button>
    </form>
@endsection
