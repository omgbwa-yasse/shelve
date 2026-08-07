@extends('layouts.app')

@section('content')
    <div class="container">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Name</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transferrings as $transferring)
                <tr>
                    <td>{{ $transferring->id }}</td>
                    <td>{{ $transferring->code }}</td>
                    <td>{{ $transferring->name }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
