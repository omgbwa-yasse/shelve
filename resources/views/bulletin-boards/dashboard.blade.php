<!-- resources/views/bulletin-boards/dashboard.blade.php -->

@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Tableau de Bord</h1>
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total des Babillards</h5>
                        <p class="card-text">{{ $stats['total_posts'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Événements Actifs</h5>
                        <p class="card-text">{{ $stats['active_events'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Mes Publications</h5>
                        <p class="card-text">{{ $stats['my_posts'] }}</p>
                    </div>
                </div>
            </div>
        </div>
        <h2>Activités Récentes</h2>
        <ul>
            @foreach($stats['recent_activities'] as $activity)
                <li>{{ $activity->name }} par {{ $activity->user->name }}</li>
            @endforeach
        </ul>
    </div>
@endsection
