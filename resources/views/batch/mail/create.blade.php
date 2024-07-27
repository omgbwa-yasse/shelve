@extends('layouts.app')

@section('content')
    <h1>Create Batch Mail for {{ $batch->name }}</h1>
    <form action="{{ route('batch.mail.store', $batch) }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="mail_id" class="form-label">Mail</label>
            <select name="mail_id" id="mail_id" class="form-select" required>
                @foreach ($mails as $mail)
                    <option value="{{ $mail->id }}">{{ $mail->subject }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
@endsection
