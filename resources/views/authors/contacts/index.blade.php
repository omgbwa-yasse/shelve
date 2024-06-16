@extends('layouts.app')

@section('content')
    <h1>Contacts for {{ $author->name }}</h1>
    <a href="{{ route('author-contacts.create', $author) }}" class="btn btn-primary">Add Contact</a>
    <table class="table">
        <thead>
            <tr>
                <th>Phone 1</th>
                <th>Phone 2</th>
                <th>Email</th>
                <th>Address</th>
                <th>Website</th>
                <th>Fax</th>
                <th>Other</th>
                <th>PO Box</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($authorContacts as $contact)
                <tr>
                    <td>{{ $contact->phone1 }}</td>
                    <td>{{ $contact->phone2 }}</td>
                    <td>{{ $contact->email }}</td>
                    <td>{{ $contact->address }}</td>
                    <td>{{ $contact->website }}</td>
                    <td>{{ $contact->fax }}</td>
                    <td>{{ $contact->other }}</td>
                    <td>{{ $contact->po_box }}</td>
                    <td>
                        <a href="{{ route('author-contacts.show', [$author, $contact]) }}" class="btn btn-info">View</a>
                        <a href="{{ route('author-contacts.edit', [$author, $contact]) }}" class="btn btn-warning">Edit</a>
                        <form action="{{ route('author-contacts.destroy', $contact) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
