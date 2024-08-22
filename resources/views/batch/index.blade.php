@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Mes parapheurs</h1>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <table class="table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($mailBatches as $mailBatch)
                    <tr>
                        <td>{{ $mailBatch->code }}</td>
                        <td>{{ $mailBatch->name }}</td>
                        <td>
                            <a href="{{ route('mails.sort') }}?categ=batch&id={{$mailBatch->id}}" class="btn btn-info btn-sm">Voir le contenu</a>

                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
