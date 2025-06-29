@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center mb-3">
        <button class="btn btn-secondary me-2" onclick="window.showScanList()"><i class="bi bi-arrow-left"></i> Retour à la liste</button>
        <h2 class="mb-0">Session de numérisation</h2>
    </div>
    <div id="scan-session-main">
        <div class="row">
            <div class="col-md-9">
                <div id="scan-current-page" class="border rounded p-3 text-center" style="min-height:400px">
                    <span class="spinner-border"></span> Chargement de la page en cours...
                </div>
            </div>
            <div class="col-md-3">
                <h5>Pages précédentes</h5>
                <div id="scan-thumbnails" class="d-flex flex-row-reverse flex-wrap gap-2 overflow-auto" style="max-height:500px">
                    <span class="spinner-border"></span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
function loadScanSession(sessionId) {
    $('#scan-session-main').html('<div class="text-center py-5"><span class="spinner-border"></span> Chargement...</div>');
    $.get("/scan/pages/"+sessionId, function(data) {
        $('#scan-session-main').html(data);
    });
}
window.loadScanSession = loadScanSession;
function showScanList() {
    window.location.hash = '';
    $('#scan-main-content').load("{{ route('scan.index') }} #scan-main-content > *");
}
window.showScanList = showScanList;
</script>
@endsection
