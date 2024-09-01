@extends('layouts.app')

@section('content')
    <h1>Recevoir un parapheur </h1>
    <form action="{{ route('batch-received.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="batch_id" class="form-label">Parapheur </label>
            <select name="batch_id" id="batch_id" class="form-select" required>
                @foreach ($batches as $batch)
                    <option value="{{ $batch->id }}">{{ $batch->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="organisation_send_id" class="form-label">Organisation d'origine</label>
            <select name="organisation_send_id" id="organisation_send_id" class="form-select" required>
                @foreach ($organisations as $organisation)
                    <option value="{{ $organisation->id }}">{{ $organisation->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
@endsection
