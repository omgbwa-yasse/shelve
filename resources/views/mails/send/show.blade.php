@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Courrier sortant : fiche</h1>
    <table class="table">
        <tbody>
            <tr>
                <th>Code</th>
                <td>{{ $transaction->code }}</td>
            </tr>
            <tr>
                <th>Date Creation</th>
                <td>{{ $transaction->date_creation }}</td>
            </tr>

        </tbody>
    </table>
</div>
@endsection
