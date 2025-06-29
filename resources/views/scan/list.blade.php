@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <h2 class="mb-3">Liste des sessions de numérisation</h2>
    <div id="scan-sessions-list">
        <div class="text-center text-muted py-5">
            <span class="spinner-border"></span> Chargement des sessions...
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
$(function() {
    function loadSessions() {
        $('#scan-sessions-list').html('<div class="text-center text-muted py-5"><span class="spinner-border"></span> Chargement...</div>');
        $.get("{{ route('scan.sessions') }}", function(data) {
            $('#scan-sessions-list').html(data);
        });
    }
    loadSessions();
    // Pour recharger dynamiquement si besoin
    window.reloadScanSessions = loadSessions;
});
</script>
@endsection
