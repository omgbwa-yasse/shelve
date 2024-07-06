@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Slip Record Details for Slip {{ $slip->name }}</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ $slipRecord->name }}</h5>
                <p class="card-text">{{ $slipRecord->content }}</p>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>Code:</strong> {{ $slipRecord->code }}</li>
                    <li class="list-group-item"><strong>Date Format:</strong> {{ $slipRecord->date_format }}</li>
                    <li class="list-group-item"><strong>Date Start:</strong> {{ $slipRecord->date_start }}</li>
                    <li class="list-group-item"><strong>Date End:</strong> {{ $slipRecord->date_end }}</li>
                    <li class="list-group-item"><strong>Date Exact:</strong> {{ $slipRecord->date_exact }}</li>
                    <li class="list-group-item"><strong>Level ID:</strong> {{ $slipRecord->level_id }}</li>
                    <li class="list-group-item"><strong>Width:</strong> {{ $slipRecord->width }}</li>
                    <li class="list-group-item"><strong>Width Description:</strong> {{ $slipRecord->width_description }}</li>
                    <li class="list-group-item"><strong>Support ID:</strong> {{ $slipRecord->support_id }}</li>
                    <li class="list-group-item"><strong>Activity ID:</strong> {{ $slipRecord->activity_id }}</li>
                    <li class="list-group-item"><strong>Container ID:</strong> {{ $slipRecord->container_id }}</li>
                </ul>
                <a href="{{ route('slips.records.index', $slip->id) }}" class="btn btn-secondary mt-3">Back</a>
                <a href="{{ route('slips.records.edit', [$slip, $slipRecord->id]) }}" class="btn btn-warning mt-3">Edit</a>
                <form action="{{ route('slips.records.destroy', [$slip->id, $slipRecord->id]) }}" method="POST" style="display: inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger mt-3" onclick="return confirm('Are you sure you want to delete this slip record?')">Delete</button>
                </form>
        </div>
        </div>
    </div>
@endsection
