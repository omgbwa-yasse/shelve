@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Contenant pour archivage </h1>

        <table class="table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Intitulé</th>
                    <th>Créer par </th>
                    <th>Type</th>
                    <th colspan="2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($mailContainers as $mailContainer)
                    <tr>
                        <td>{{ $mailContainer->code }}</td>
                        <td>{{ $mailContainer->name }}</td>
                        <td>{{ $mailContainer->creator->name }}</td>
                        <td>{{ $mailContainer->containerType->name }}</td>
                        <td>
                            <a href="{{ route('mail-container.show', $mailContainer->id) }}" class="btn btn-info">Paramètre</a>
                            <a href="{{ route('mails.sort') }}?categ=container&id={{ $mailContainer->id }}" class="btn btn-success"> {{ $mailContainer->mailArchivings->count() }} courriers archivés</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

