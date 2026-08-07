{{-- Options de reconnaissance vocale --}}
<div class="voice-settings mt-2" style="display: none;" id="voiceSettings">
    <small class="text-muted">
        <i class="bi bi-gear me-1"></i>
        <label class="form-check-label">
            <input type="checkbox" class="form-check-input me-1" id="autoSendVoice" checked>
            {{ __('Auto-send after voice recognition') }}
        </label>
        <span class="ms-3">
            <i class="bi bi-keyboard me-1"></i>
            {{ __('Shortcut:') }} <kbd>Ctrl+Shift+V</kbd>
        </span>
    </small>
</div>
