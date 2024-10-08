@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Export Slips</h2>
        <form action="{{ route('slips.export') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="dolly_id">Select Dolly (optional):</label>
                <select name="dolly_id" id="dolly_id" class="form-control">
                    <option value="">All Slips</option>
                    @foreach($dollies as $dolly)
                        <option value="{{ $dolly->id }}">{{ $dolly->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="format">Export Format:</label>
                <select name="format" id="format" class="form-control">
                    <option value="excel">Excel</option>
                    <option value="ead">EAD</option>
                    <option value="seda">SEDA</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Export</button>
        </form>
    </div>
@endsection
