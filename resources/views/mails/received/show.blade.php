@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Courrier entrant : fiche</h1>
    <table class="table">
        <tbody>
            <tr>
                <th>Code</th>
                <td>{{ $MailTransaction->code }}</td>
            </tr>
            <tr>
                <th>Date Creation</th>
                <td>{{ $MailTransaction->date_creation }}</td>
            </tr>

        </tbody>
    </table>
</div>
@endsection
