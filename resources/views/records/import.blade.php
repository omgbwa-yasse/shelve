@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Import Records</h2>
        <form action="{{ route('records.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="file">File:</label>
                <input type="file" name="file" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="format">Format:</label>
                <select name="format" class="form-control" required>
                    <option value="excel">Excel</option>
                    <option value="ead">EAD</option>
                    <option value="seda">SEDA</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Import</button>
        </form>
    </div>
@endsection
