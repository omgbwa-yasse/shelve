@extends('layouts.app')
@section('content')
<div class="container">
    <h1>Ajouter une nouvelle typologie de Courrier</h1>
    <a href="{{ route('mail-typology.create') }}" class="btn btn-primary mb-3">Create New Mail Typology</a>
    <table class="table">
        <thead>
            <tr>
                <th>Intitulé</th>
                <th>Description</th>
                <th>Domaine d'activité</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($mailTypologies as $mailTypology)
                <tr>
                    <td>{{ $mailTypology->name }}</td>
                    <td>{{ $mailTypology->description }}</td>
                    <td>{{ $mailTypology->class->name ?? 'NAN' }}</td>
                    <td>{{ $mailTypology->mails->count() ?? 'NAN' }}</td>
                    <td>
                        <a href="{{ route('mail-typology.show', $mailTypology->id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('mails.sort') }}?categ=typology&id={{$mailTypology->id}}" class="btn btn-info btn-sm">Voir les mails</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
