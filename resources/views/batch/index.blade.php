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
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($mailBatches as $batch)
                    <tr>
                        <td>{{ $batch->code }}</td>
                        <td>{{ $batch->name }}</td>

                        <td>
                            <a href="{{ route('mails.sort') }}?categ=batch&id={{$batch->id}}" class="btn btn-info btn-sm">Voir le contenu</a>
                            <a href="{{ route('batch.show', $batch) }}" class="btn btn-warning btn-sm">Paramètre</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
