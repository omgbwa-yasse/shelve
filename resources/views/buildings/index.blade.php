@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Buildings</h1>
        <a href="{{ route('buildings.create') }}" class="btn btn-primary mb-3">
            <i class="bi bi-plus-circle"></i> Ajouter un bâtiment
        </a>

        <div id="buildingList">
            @foreach ($buildings as $building)
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-9">
                                <h5 class="card-title mb-2">
                                    <b>{{ $building->name ?? 'N/A' }}</b> (ID: {{ $building->id ?? 'N/A' }})
                                </h5>
                                <p class="card-text mb-1">
                                    <i class="bi bi-file-earmark-text"></i> <strong>Description:</strong> {{ $building->description ?? 'N/A' }}<br>
                                    @if($building->floors->count() > 1)
                                        <i class="bi bi-building"></i> <strong>Building with {{ $building->floors->count() }} levels</strong>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-3 text-md-end text-center">
                                <div class="d-flex justify-content-md-end justify-content-center align-items-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('floors.index', $building->id)}}" class="btn btn-sm btn-outline-secondary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
