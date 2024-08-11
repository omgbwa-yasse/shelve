@extends('layouts.app')

@section('content')

<h1>Documents archivés</h1>
<table class="table">
    <thead>
    <tr>
        <th>ID</th>
        <th>Code</th>
        <th>Name</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($records as $record)
        <tr>
            <td>{{ $record->id }}</td>
            <td>{{ $record->code }}</td>
            <td>{{ $record->name }}</td>
            <td>
                <a href="{{ route('records.show', $record) }}" class="btn btn-sm btn-info">Voir la fiche</a>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>


<h1>Courrier</h1>
<table class="table">
    <thead>
    <tr>
        <th>Code</th>
        <th>Object, Auteur</th>
        <th>Date</th>
        <th>Producteur</th>
        <th>Localisation</th>
        <th>Type</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($mails as $mail)
        <tr>
            <td>{{ $mail->code }}</td>
            <td>
                {{ $mail->name }},
                @foreach($mail->authors as $author)
                    {{ $author->name }}
                @endforeach
            </td>
            <td>{{ $mail->date }}</td>
            <td>{{ $mail->creator->name ?? '' }}</td>
            <td>
                @foreach($mail->container as $container)
                    {{ $container->code ?? '' }}
                    ({{ $container->name ?? 'Non conditionné' }})
                @endforeach
            </td>
            <td>{{ $mail->type->name ?? '' }}</td>
            <td>
                <a href="{{ route('mails.show', $mail->id) }}" class="btn btn-info btn-sm">Détails</a>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<h1>Archives versées</h1>

<div class="container">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Code</th>
                <th>Name</th>
                <th>Versement</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transferringRecords as $record)
                <tr>
                    <td>{{ $record->id }}</td>
                    <td>{{ $record->code }}</td>
                    <td>{{ $record->name }}</td>
                    <td>{{ $record->slip->code }} - {{ $record->slip->name }}</td>
                    <td>
                        <a href="{{ route('slips.show', $record->slip->id) }}" class="btn btn-info btn-sm">Détails</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>




<h1>Versements</h1>

<div class="container">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Code</th>
                <th>Name</th>
                <th>Date Format</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transferrings as $record)
                <tr>
                    <td>{{ $record->id }}</td>
                    <td>{{ $record->code }}</td>
                    <td>{{ $record->name }}</td>
                    <td>{{ $record->date_format }}</td>
                    <td>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>



@endsection
