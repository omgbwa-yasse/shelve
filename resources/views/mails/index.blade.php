@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Mails</h1>
        <a href="{{ route('mails.create') }}" class="btn btn-primary mb-3">Create Mail</a>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Object</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($mails as $mail)
                    <tr>
                        <td>{{ $mail->id }}</td>
                        <td>{{ $mail->code }}</td>
                        <td>{{ $mail->object }}</td>
                        <td>{{ $mail->date }}</td>
                        <td>
                            <a href="{{ route('mails.show', $mail->id) }}" class="btn btn-info btn-sm">Show</a>
                            <a href="{{ route('mails.edit', $mail->id) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('mails.destroy', $mail->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
