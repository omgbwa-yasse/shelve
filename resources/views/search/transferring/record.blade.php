@extends('layouts.app')

@section('content')
    <div class="container">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Date Format</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transferrings as $record)
                    <tr>
                        <td>{{ $record->id }}</td>
                        <td>{{ $record->code }}</td>
                        <td>{{ $record->name }}</td>
                        <td>{{ $record->date_format }}</td>
                        <td>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
