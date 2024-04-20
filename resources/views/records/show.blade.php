@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Record Details</div>

                <div class="card-body">
                    <h5 class="card-title">{{ $record->name }}</h5>
                    <h6 class="card-subtitle mb-2 text-muted">Reference: {{ $record->reference }}</h6>
                    <p class="card-text">Date Format: {{ $record->date_format }}</p>
                    <p class="card-text">Date Start: {{ $record->date_start }}</p>
                    <p class="card-text">Date End: {{ $record->date_end }}</p>
                    <p class="card-text">Date Exact: {{ $record->date_exact }}</p>
                    <p class="card-text">Description: {{ $record->description }}</p>
                    <p class="card-text">Level: {{ $record->level->name }}</p>
                    <p class="card-text">Status: {{ $record->status->name }}</p>
                    <p class="card-text">Support: {{ $record->support->name }}</p>
                    <p class="card-text">Classification: {{ $record->classification->name }}</p>
                    <p class="card-text">Parent: {{ $record->parent ? $record->parent->name : '-' }}</p>
                    <p class="card-text">Container: {{ $record->container->name }}</p>
                    <p class="card-text">Transfer: {{ $record->transfer ? $record->transfer->name : '-' }}</p>
                    <p class="card-text">User: {{ $record->user->name }}</p>

                    <a href="{{ route('records.edit', $record->id) }}" class="btn btn-primary">Edit</a>
                    <a href="{{ route('records.index') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
