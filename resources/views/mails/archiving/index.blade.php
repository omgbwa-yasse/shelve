@extends('layouts.app')
@section('content')

<table class="table">
    <thead>
        <tr>
            <th>Container</th>
            <th>Mail</th>
            <th>Document Type</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($mailArchivings as $mailArchiving)
            <tr>
                <td>{{ $mailArchiving->container->name }}</td>
                <td>{{ $mailArchiving->mail->name }}</td>
                <td>{{ $mailArchiving->documentType->name }}</td>
                <td>
                    <a href="{{ route('mail-archiving.show', $mailArchiving->id) }}" class="btn btn-primary">Show</a>
                    <a href="{{ route('mail-archiving.edit', $mailArchiving->id) }}" class="btn btn-secondary">Edit</a>
                    <form action="{{ route('mail-archiving.destroy', $mailArchiving->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this mail archiving?')">Delete</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>


@endsection
