@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Communication Details</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ $communication->code }}</h5>
                <p class="card-text">Operator: {{ $communication->operator->name }}</p>
                <p class="card-text">Operator Organisation: {{ $communication->operatorOrganisation->name }}</p>
                <p class="card-text">User: {{ $communication->user->name }}</p>
                <p class="card-text">User Organisation: {{ $communication->userOrganisation->name }}</p>
                <p class="card-text">Return Date: {{ $communication->return_date }}</p>
                <p class="card-text">Return Effective: {{ $communication->return_effective }}</p>
                <p class="card-text">Status: {{ $communication->status->name }}</p>
                <a href="{{ route('communication-transactions.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
@endsection
