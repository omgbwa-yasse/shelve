@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="mb-3">
        <ul class="nav nav-tabs" id="scan-menu-tabs">
            <li class="nav-item">
                <a class="nav-link active" id="tab-scan-list" href="#" onclick="showScanListTab();return false;">Liste des numérisations</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-scan-session" href="#" onclick="showScanSessionTab();return false;">Page de numérisation</a>
            </li>
        </ul>
    </div>
    <div id="scan-main-content">
        <div class="text-center py-5"><span class="spinner-border"></span> Chargement...</div>
    </div>
</div>
@endsection
@section('scripts')
<script>
function showScanListTab() {
    $('#tab-scan-list').addClass('active');
    $('#tab-scan-session').removeClass('active');
    $('#scan-main-content').load("{{ route('scan.index') }}/list", function() {
        // Optionnel: callback après chargement
    });
}
function showScanSessionTab(sessionId) {
    $('#tab-scan-list').removeClass('active');
    $('#tab-scan-session').addClass('active');
    if(sessionId) {
        $('#scan-main-content').load("/scan/session/"+sessionId);
    } else {
        $('#scan-main-content').html('<div class="alert alert-info">Sélectionnez une session dans la liste pour commencer la numérisation.</div>');
    }
}
$(function() {
    showScanListTab();
    window.showScanListTab = showScanListTab;
    window.showScanSessionTab = showScanSessionTab;
});
</script>
@endsection
