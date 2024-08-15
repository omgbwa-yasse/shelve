@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Organisation Active') }}</div>
                <div class="card-body">
                    <p><strong>Organisation:</strong> {{ $organisationActive->organisation->name }}</p>
                    <p><strong>User:</strong> {{ $organisationActive->user->name }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
