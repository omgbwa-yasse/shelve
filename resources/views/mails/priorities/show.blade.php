@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Mail Priority Details</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ $mailPriority->name }}</h5>
                <p class="card-text">Duration: {{ $mailPriority->duration }} days</p>
                <p class="card-text">Created at: {{ $mailPriority->created_at }}</p>
                <p class="card-text">Updated at: {{ $mailPriority->updated_at }}</p>
                <a href="{{ route('mail-priority.edit', $mailPriority) }}" class="btn btn-warning">Edit</a>
                <a href="{{ route('mail-priority.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
@endsection
