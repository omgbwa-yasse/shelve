@extends('layouts.app')

@section('content')
    <h1>Courrier du parapheur : {{ $batch->name }}</h1>
    <a href="{{ route('batch.mail.create', $batch) }}" class="btn btn-primary mb-3">Ajouter un courrier</a>
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <table class="table">
        <thead>
            <tr>
                <th>Code</th>
                <th>Courrier</th>
                <th>Insert Date</th>
                <th>Remove Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($batchMails as $batchMail)
                <tr>
                    <td>{{ $batchMail->mail->code }}</td>
                    <td>{{ $batchMail->mail->name }}</td>
                    <td>{{ $batchMail->insert_date }}</td>
                    <td>{{ $batchMail->id }}</td>
                    <td>
                        <form action="{{ route('batch.mail.destroy', [$batch, $batchMail]) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this batch mail?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
