@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Liste des courriers</h1>
        <a href="{{ route('mails.create') }}" class="btn btn-primary mb-3">Create Mail</a>
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
                        <td>{{ $mail->name }}, {{ $mail->author }}</td>
                        <td>{{ $mail->date }}</td>
                        <td> </td>
                        <td></td>
                        <td>
                            <a href="{{ route('mails.show', $mail->id) }}" class="btn btn-info btn-sm">Show</a>
                        </td>
                    </tr>
                    <tr>
                    <td>
                        @php
                            $transaction = json_decode($mail->lastTransaction);
                        @endphp

                        @if ($transaction)
                            <table class="table table-bordered">
                                <tbody>
                                    @foreach ($transaction as $key => $value)
                                        <tr>
                                            <th>{{ ucfirst(str_replace('_', ' ', $key)) }}</th>
                                            <td>{{ $value }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            Aucune transaction trouvée
                        @endif
                    </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
