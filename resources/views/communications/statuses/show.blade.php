@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Communication Status Details</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ $status->name }}</h5>
                <p class="card-text">{{ $status->description }}</p>
                <a href="{{ route('communication-status.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
@endsection
