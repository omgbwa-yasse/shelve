@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Mail Action Details</h1>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $mailAction->name }}</h5>
            <p class="card-text">
                <strong>Duration:</strong> {{ $mailAction->duration }}<br>
                <strong>To Return:</strong> {{ $mailAction->to_return ? 'Yes' : 'No' }}<br>
                <strong>Description:</strong> {{ $mailAction->description }}
            </p>
            <a href="{{ route('mail-action.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
