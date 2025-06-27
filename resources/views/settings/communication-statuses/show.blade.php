@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Communication Status Details</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ $communicationStatus->name }}</h5>
                <p class="card-text">{{ $communicationStatus->description }}</p>
                <a href="{{ route('communication-status.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
@endsection
