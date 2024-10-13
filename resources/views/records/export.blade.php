@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>{{ __('export_records') }}</h2>
        <form action="{{ route('records.export') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="dolly_id">{{ __('select_dolly') }}:</label>
                <select name="dolly_id" id="dolly_id" class="form-control">
                    <option value="">{{ __('all_records') }}</option>
                    @foreach($dollies as $dolly)
                        <option value="{{ $dolly->id }}">{{ $dolly->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="format">{{ __('export_format') }}:</label>
                <select name="format" id="format" class="form-control">
                    <option value="excel">Excel</option>
                    <option value="ead">EAD</option>
                    <option value="seda">SEDA</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('export') }}</button>
        </form>
    </div>
@endsection
