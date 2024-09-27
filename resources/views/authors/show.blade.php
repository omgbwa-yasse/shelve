@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Author Details</h2>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $author->name }}</h5>
            <p class="card-text">Type : {{ $author->authorType->name }}</p>
            @if ($author->parallel_name)
                <p class="card-text">Nom parallèle : {{ $author->parallel_name }}</p>
            @endif
            @if ($author->other_name)
                <p class="card-text">Autre nom : {{ $author->other_name }}</p>
            @endif
            @if ($author->lifespan)
                <p class="card-text">Période de vie : {{ $author->lifespan }}</p>
            @endif
            @if ($author->locations)
                <p class="card-text">Lieux : {{ $author->locations }}</p>
            @endif
            @if ($author->parent)
                <p class="card-text">Auteur parent : {{ $author->parent->name }}</p>
            @endif
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


    <a href="{{ route('mail-author.index') }}" class="btn btn-secondary mt-3">Back to Authors</a>
    <a href="{{ route('author-contact.create', $author) }}" class="btn btn-success mt-3">Ajouter un contact</a>
</div>
@endsection
