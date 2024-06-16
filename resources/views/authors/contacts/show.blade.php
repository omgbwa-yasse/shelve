@extends('layouts.app')

@section('content')
    <h1>Contact Details</h1>
    <table class="table">
        <tbody>
            <tr>
                <th>Phone 1</th>
                <td>{{ $authorContact->phone1 }}</td>
            </tr>
            <tr>
                <th>Phone 2</th>
                <td>{{ $authorContact->phone2 }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $authorContact->email }}</td>
            </tr>
            <tr>
                <th>Address</th>
                <td>{{ $authorContact->address }}</td>
            </tr>
            <tr>
                <th>Website</th>
                <td>{{ $authorContact->website }}</td>
            </tr>
            <tr>
                <th>Fax</th>
                <td>{{ $authorContact->fax }}</td>
            </tr>
            <tr>
                <th>Other</th>
                <td>{{ $authorContact->other }}</td>
            </tr>
            <tr>
                <th>PO Box</th>
                <td>{{ $authorContact->po_box }}</td>
            </tr>
        </tbody>
    </table>
    <a href="{{ route('author-contacts.index', $author) }}" class="btn btn-secondary">Back</a>
@endsection
