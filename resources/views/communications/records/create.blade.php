@extends('layouts.app')

@section('content')
    <h1>Archives à communiquer</h1>

    <form action="{{ route('transactions.records.store', $communication) }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="record_id">Archives voulues</label>
            <select name="record_id" id="record_id" class="form-control" required>
                @foreach ($records as $record)
                    <option value="{{ $record->id }}">{{ $record->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="record_id">Documents sollicités *** (facultatif)</label>
            <textarea class="form-control" id="content" name="content"></textarea>
        </div>
        <div class="form-group">
            <label for="is_original">Copie original</label>
            <select name="is_original" id="is_original" class="form-control" required>
                <option value="1">Oui</option>
                <option value="0">Non</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
@endsection
