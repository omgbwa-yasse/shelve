@extends('layouts.app')
@section('content')
<div class="container">
    <h1 class="mt-5">Mail Batch Details</h1>
    <table class="table">
        <tbody>
            <tr>
                <th>ID:</th>
                <td>{{ $mailbatch->id }}</td>
            </tr>
            <tr>
                <th>Code:</th>
                <td>{{ $mailbatch->code }}</td>
            </tr>
            <tr>
                <th>Name:</th>
                <td>{{ $mailbatch->name }}</td>
            </tr>
        </tbody>
    </table>
    <a href="{{ route('batch.index') }}" class="btn btn-secondary">Back</a>
</div>
@endsection
