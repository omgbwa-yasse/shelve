@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Attachments for Mail #{{ $mail->id }}</h1>
        <a href="{{ route('mail-attachment.create', $mail) }}" class="btn btn-primary mb-3">Add Attachment</a>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Size</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attachments as $attachment)
                    <tr>
                        <td>{{ $attachment->name }}</td>
                        <td>{{ $attachment->size }}</td>
                        <td>{{ $attachment->created_at }}</td>
                        <td>
                            <a href="{{ route('mail-attachment.show', [$mail, $attachment]) }}" class="btn btn-sm btn-info">View</a>
                            <form action="{{ route('mail-attachment.destroy', [$mail, $attachment]) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this attachment?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
