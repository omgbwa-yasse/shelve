@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Attachment Details</h1>
        <table class="table">
            <tbody>
                <tr>
                    <th>Name</th>
                    <td>{{ $attachment->name }}</td>
                </tr>
                <tr>
                    <th>Path</th>
                    <td>{{ $attachment->path }}</td>
                </tr>
                <tr>
                    <th>Crypt</th>
                    <td>{{ $attachment->crypt }}</td>
                </tr>
                <tr>
                    <th>Size</th>
                    <td>{{ $attachment->size }}</td>
                </tr>
                <tr>
                    <th>Created At</th>
                    <td>{{ $attachment->created_at }}</td>
                </tr>
                <tr>
                    <th>Updated At</th>
                    <td>{{ $attachment->updated_at }}</td>
                </tr>
            </tbody>
        </table>
        <a href="{{ route('mail-attachment.index', $mail) }}" class="btn btn-secondary">Back</a>
    </div>
@endsection
