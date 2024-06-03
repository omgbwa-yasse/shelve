
@extends('layouts.app')

@section('content')
    <h1>Mail Subjects</h1>
    <a href="{{ route('mail_subjects.create') }}">Create New Mail Subject</a>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($mailSubjects as $mailSubject)
                <tr>
                    <td>{{ $mailSubject->id }}</td>
                    <td>{{ $mailSubject->name }}</td>
                    <td>
                        <a href="{{ route('mail_subjects.show', $mailSubject->id) }}">View</a>
                        <a href="{{ route('mail_subjects.edit', $mailSubject->id) }}">Edit</a>
                        <form action="{{ route('mail_subjects.destroy', $mailSubject->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Are you sure you want to delete this mail subject?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
