@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Author Details</h2>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $author->name }}</h5>
            <p class="card-text">Type: {{ $author->authorType->name }}</p>
            @if ($author->parallel_name)
                <p class="card-text">Parallel Name: {{ $author->parallel_name }}</p>
            @endif
            @if ($author->other_name)
                <p class="card-text">Other Name: {{ $author->other_name }}</p>
            @endif
            @if ($author->lifespan)
                <p class="card-text">Lifespan: {{ $author->lifespan }}</p>
            @endif
            @if ($author->locations)
                <p class="card-text">Locations: {{ $author->locations }}</p>
            @endif
            @if ($author->parent)
                <p class="card-text">Parent Author: {{ $author->parent->name }}</p>
            @endif

        </div>
    </div>
<hr>

    <div  class="table-responsive" >
        @foreach($author->contacts as $contact)
        <table class="table table-primary">
            <tbody>
                <tr class="">
                    @if ($contact->phone1)
                        <td>{{ $contact->phone1 }}</td>
                    @endif
                    @if ($contact->phone2)
                        <td>{{ $contact->phone2 }}</td>
                    @endif
                    @if ($contact->email)
                        <td>{{ $contact->email }}</td>
                    @endif
                    @if ($contact->address)
                        <td>{{ $contact->address }}</td>
                    @endif
                    @if ($contact->website)
                        <td>{{ $contact->website }}</td>
                    @endif
                    @if ($contact->fax)
                        <td>{{ $contact->fax }}</td>
                    @endif
                    @if ($contact->other)
                        <td>{{ $contact->other }}</td>
                    @endif
                    @if ($contact->po_box)
                        <td>{{ $contact->po_box }}</td>
                    @endif
                </tr>
            </tbody>
        </table>
        @endforeach
    </div>
    <a class="btn btn-primary" href="{{ route('record-author.edit', $author) }}">Edit</a>
    <form action="{{ route('record-author.destroy', $author) }}" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Delete</button>
    </form>
    <a href="{{ route('mail-author.index') }}" class="btn btn-secondary mt-3">Back to Authors</a>
    <a href="{{ route('author-contact.create', $author) }}" class="btn btn-success mt-3">Ajouter un contact</a>
</div>
@endsection
