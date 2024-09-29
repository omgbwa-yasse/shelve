@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Activity Details</h1>
    <dl>
        <dt>Organisation</dt>
        <dd>{{ $organisationActivity->organisation->name }}</dd>
        <dt>Activity</dt>
        <dd>{{ $organisationActivity->activity->name }}</dd>
        <dt>Creator</dt>
        <dd>{{ $organisationActivity->creator->name }}</dd>
    </dl>
    <a href="{{ route('organisations.activities.index', $organisationActivity->organisation) }}" class="btn btn-secondary">Back</a>
</div>
@endsection
