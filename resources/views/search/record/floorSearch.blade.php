@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Floors</h1>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Building</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($floors as $floor)
                    <tr>
                        <td>{{ $floor->id }}</td>
                        <td>{{ $floor->name }}</td>
                        <td>{{ $floor->description }}</td>
                        <td>{{ $floor->building->name }}</td>
                        <td>
                            <a href="{{ route('record-select-room') }}?categ=room&id={{$floor->id}}" class="btn btn-info btn-sm">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
@endsection
