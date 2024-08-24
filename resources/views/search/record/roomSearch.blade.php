@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Rooms</h1>
        <div id="roomList">
            @foreach ($rooms as $room)
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-9">
                                <h5 class="card-title mb-2">
                                    <b>{{ $room->code ?? 'N/A' }}</b> - {{ $room->name ?? 'N/A' }} (ID: {{ $room->id ?? 'N/A' }})
                                </h5>
                                <p class="card-text mb-1">
                                    <i class="bi bi-file-earmark-text"></i> <strong>Description:</strong> {{ $room->description ?? 'N/A' }}<br>
                                    <i class="bi bi-building"></i> <strong>Floor:</strong> {{ $room->floor->name ?? 'N/A' }}<br>
                                    <i class="bi bi-map"></i> <strong>Building:</strong> {{ $room->floor->building->name ?? 'N/A' }}
                                </p>
                            </div>
                            <div class="col-md-3 text-md-end text-center">
                                <div class="d-flex justify-content-md-end justify-content-center align-items-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{route('record-select-shelve') }}?categ=shelve&id={{ $room->id }}" class="btn btn-sm btn-outline-secondary" title="View">
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
