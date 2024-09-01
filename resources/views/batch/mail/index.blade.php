@extends('layouts.app')

@section('content')
    <h1>Batch Mails for {{ $batch->name }}</h1>
    <a href="{{ route('batch.mail.create', $batch) }}" class="btn btn-primary mb-3">Create Batch Mail</a>
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
                    <td></td>
                    <td></td>
                    <td>{{ $batchMail->insert_date }}</td>
                    <td>{{ $batchMail->remove_date }}</td>
                    <td>
                        <a href="{{ route('batch.mail.show', [$batch, $batchMail]) }}" class="btn btn-sm btn-info">Show</a>
                        <a href="{{ route('batch.mail.edit', [$batch, $batchMail]) }}" class="btn btn-sm btn-primary">Edit</a>
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
