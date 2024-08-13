@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Courrier sortant : fiche</h1>
    <table class="table">
        <tbody>
            <tr>
                <th>Code</th>
                <td>{{ $transaction->code }}</td>
            </tr>
            <tr>
                <th>Date Creation</th>
                <td>{{ $transaction->date_creation }}</td>
            </tr>
            <tr>
                <th>Action</th>
                <td>{{ $transaction->action->name }}</td>
            </tr>
            <tr>
                <th>Description</th>
                <td>{{ $transaction->description }}</td>
            </tr>

        </tbody>
    </table>
    <a href="{{ route('mail-send.index') }}" class="btn btn-secondary mt-3">Retour</a>
    <a href="{{ route('mail-send.edit', $transaction) }}" class="btn btn-secondary mt-3">Edit</a>
    <form action="{{ route('mail-send.destroy', $transaction->id) }}" method="POST" style="display: inline-block;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger mt-3">Delete</button>
    </form>
</div>

@endsection
