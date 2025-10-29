{{-- Indicateur d'enregistrement vocal --}}
<div id="voiceRecordingIndicator" class="voice-recording-indicator" style="display: none;">
    <div class="d-flex align-items-center justify-content-center">
        <div class="recording-animation me-2">
            <div class="recording-dot"></div>
        </div>
        <span class="text-primary">🎤 {{ __('Recording... Speak now') }}</span>
        <button type="button" class="btn btn-sm btn-outline-danger ms-3" id="stopRecordingButton">
            <i class="bi bi-stop-circle"></i> {{ __('Stop') }}
        </button>
    </div>
</div>
