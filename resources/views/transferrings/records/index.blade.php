@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Slip Records for Slip {{ $slip->name }}</h1>
        <a href="{{ route('slips.records.create', $slip->id) }}" class="btn btn-primary mb-3">Create New Slip Record</a>
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
                @foreach ($slipRecords as $slipRecord)
                    <tr>
                        <td>{{ $slipRecord->id }}</td>
                        <td>{{ $slipRecord->code }}</td>
                        <td>{{ $slipRecord->name }}</td>
                        <td>{{ $slipRecord->date_format }}</td>
                        <td>
                            <a href="{{ route('slips.records.show', [$slip->id, $slipRecord->id]) }}" class="btn btn-info btn-sm">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
