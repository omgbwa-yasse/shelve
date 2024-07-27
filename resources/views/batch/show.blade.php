@extends('layouts.app')
@section('content')
<div class="container">
    <h1 class="mt-5">Parapheur : fiche</h1>
    <table class="table">
        <tbody>
            <tr>
                <td>Code : {{  $mailBatch->code  }}</td>
            </tr>
            <tr>
                <td>Nom : {{  $mailBatch->name  }}</td>
            </tr>
        </tbody>
    </table>
    <a href="{{ route('batch.index') }}" class="btn btn-secondary mt-3">Back</a>
    <a href="{{ route('batch.edit', $mailBatch->id) }}" class="btn btn-warning mt-3">Edit</a>
    <form action="{{ route('batch.destroy', $mailBatch->id) }}" method="POST" style="display: inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger mt-3" onclick="return confirm('Are you sure you want to delete this mail batch?')">Delete</button>
    </form>
    <a href="{{ route('batch.mail.create', $mailBatch) }}" class="btn btn-warning mt-3">Ajouter des courrier</a>
</div>
@endsection
