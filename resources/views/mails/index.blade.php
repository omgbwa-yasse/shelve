@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Mails</h1>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Reference</th>
                <th>Object</th>
                <th>Description</th>
                <th>Authors</th>
                <th>Created At</th>
                <th>Updated At</th>
                <th>Priority</th>
                <th>Typology</th>
                <th>Attachment</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($mails as $mail)
            <tr>
                <td>{{ $mail->reference }}</td>
                <td>{{ $mail->object }}</td>
                <td>{{ $mail->description }}</td>
                <td>{{ $mail->authors }}</td>
                <td>{{ $mail->create_at }}</td>
                <td>{{ $mail->update_at }}</td>
                <td>{{ $mail->mailPriority->name }}</td>
                <td>{{ $mail->mailTypology->name }}</td>
                <td>
                    @if($mail->mailAttachment)
                        <a href="{{ route('mail_attachments.download', $mail->mailAttachment->id) }}" target="_blank">Download</a>
                    @endif
                </td>
                <td>
                    <a href="{{ route('mails.edit', $mail->id) }}" class="btn btn-primary">Edit</a>
                    <form action="{{ route('mails.destroy', $mail->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('mails.create') }}" class="btn btn-primary">Create Mail</a>
</div>



@endsection
