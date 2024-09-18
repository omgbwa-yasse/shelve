<!-- resources/views/exports/dolly_export.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Exporter les enregistrements ou bordereaux d'un Dolly</h2>
        <form action="{{ route('export.dolly') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="dolly_id">Sélectionner un Dolly :</label>
                <select name="dolly_id" id="dolly_id" class="form-control" required>
                    <option value="">-- Sélectionner un Dolly --</option>
                    @foreach($dollies as $dolly)
                        <option value="{{ $dolly->id }}">{{ $dolly->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Type d'export :</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="export_type" id="export_records" value="records" checked>
                    <label class="form-check-label" for="export_records">
                        Enregistrements
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="export_type" id="export_slips" value="slips">
                    <label class="form-check-label" for="export_slips">
                        Bordereaux
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label>Format d'export :</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="export_format" id="export_excel" value="excel" checked>
                    <label class="form-check-label" for="export_excel">
                        Excel
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="export_format" id="export_seda" value="seda">
                    <label class="form-check-label" for="export_seda">
                        SEDA
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="export_format" id="export_ead" value="ead">
                    <label class="form-check-label" for="export_ead">
                        EAD
                    </label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Exporter</button>
        </form>
    </div>
@endsection
