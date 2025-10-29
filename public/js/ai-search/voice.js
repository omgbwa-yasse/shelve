/**
 * Gestionnaire pour la reconnaissance vocale dans AI Search
 */
class VoiceSpeechRecognition {
    constructor() {
        // Vérifier le support des API nécessaires
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            console.log("getUserMedia is not supported on your browser!");
            return;
        }

        // Vérifier le support de Speech Recognition
        if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
            console.log('Speech Recognition not supported in this browser');
            return;
        }

        // Propriétés
        this.recognition = null;
        this.stream = null;
        this.isRecording = false;
        this.pendingRecording = false;
        this.permissionGranted = false;

        // Références DOM
        this.voiceButton = document.getElementById('voiceButton');
        this.voiceIcon = document.getElementById('voiceIcon');
        this.recordingIndicator = document.getElementById('voiceRecordingIndicator');
        this.stopButton = document.getElementById('stopRecordingButton');
        this.messageInput = document.getElementById('messageInput');
        this.autoSendCheckbox = document.getElementById('autoSendVoice');

        // Contraintes pour getUserMedia
        this.constraints = {
            audio: true,
            video: false
        };

        // Initialiser
        this.init();
    }

    init() {
        console.log('Initializing VoiceSpeechRecognition...');

        // Configurer Speech Recognition
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition) {
            console.log('Speech Recognition not available');
            return;
        }

        this.recognition = new SpeechRecognition();
        this.recognition.continuous = false;
        this.recognition.interimResults = true;
        this.recognition.lang = 'fr-FR';

        // Event listeners pour Speech Recognition
        this.recognition.onstart = this.onRecognitionStart.bind(this);
        this.recognition.onresult = this.onRecognitionResult.bind(this);
        this.recognition.onerror = this.onRecognitionError.bind(this);
        this.recognition.onend = this.onRecognitionEnd.bind(this);

        // Event listeners pour les boutons
        if (this.voiceButton) {
            this.voiceButton.onclick = this.toggleRecording.bind(this);
        }
        if (this.stopButton) {
            this.stopButton.onclick = this.stopRecording.bind(this);
        }

        // Raccourci clavier
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.shiftKey && e.key === 'V') {
                e.preventDefault();
                this.toggleRecording();
            }
        });

        // Sauvegarder les préférences
        if (this.autoSendCheckbox) {
            this.autoSendCheckbox.addEventListener('change', () => {
                localStorage.setItem('ai-search-auto-send-voice', this.autoSendCheckbox.checked);
            });

            // Charger les préférences
            const savedAutoSend = localStorage.getItem('ai-search-auto-send-voice');
            if (savedAutoSend !== null) {
                this.autoSendCheckbox.checked = savedAutoSend === 'true';
            }
        }

        // Afficher les paramètres vocaux
        const voiceSettings = document.getElementById('voiceSettings');
        if (voiceSettings) {
            voiceSettings.style.display = 'block';
        }

        console.log('VoiceSpeechRecognition initialized successfully');
    }

    toggleRecording() {
        if (this.isRecording) {
            this.stopRecording();
            return;
        }

        this.pendingRecording = true;

        if (this.permissionGranted) {
            this.startSpeechRecognition();
        } else {
            this.requestPermission();
        }
    }

    requestPermission() {
        this.showMessage('Demande d\'autorisation microphone...', 'info');

        navigator.mediaDevices
            .getUserMedia(this.constraints)
            .then(this.handleStreamSuccess.bind(this))
            .catch(this.handleStreamError.bind(this));
    }

    handleStreamSuccess(stream) {
        console.log('Stream access granted');
        this.stream = stream;
        this.permissionGranted = true;

        // Arrêter le stream immédiatement
        this.stream.getAudioTracks().forEach(track => track.stop());
        this.stream = null;

        // Démarrer la reconnaissance vocale
        this.startSpeechRecognition();
    }

    handleStreamError(error) {
        console.log("getUserMedia error: ", error);
        this.permissionGranted = false;
        this.showMessage('Accès au microphone refusé', 'error');
        this.showPermissionInstructions();
        this.pendingRecording = false;
    }

    startSpeechRecognition() {
        if (this.isRecording || !this.recognition) return;

        try {
            console.log('Starting speech recognition...');
            if (this.messageInput) {
                this.messageInput.value = '';
            }
            this.recognition.start();
        } catch (error) {
            console.error('Error starting speech recognition:', error);
            this.showMessage('Erreur lors du démarrage de la reconnaissance', 'error');
            this.pendingRecording = false;
        }
    }

    stopRecording() {
        console.log('stopRecording called');
        this.pendingRecording = false;

        if (this.recognition && this.isRecording) {
            this.recognition.stop();
        }
    }

    onRecognitionStart() {
        console.log('Speech recognition started');
        this.isRecording = true;
        this.pendingRecording = false;
        this.updateUI(true);
        this.showMessage('Microphone activé. Parlez maintenant...', 'info');
    }

    onRecognitionResult(event) {
        let finalTranscript = '';
        let interimTranscript = '';

        for (let i = event.resultIndex; i < event.results.length; i++) {
            const transcript = event.results[i][0].transcript;
            if (event.results[i].isFinal) {
                finalTranscript += transcript;
            } else {
                interimTranscript += transcript;
            }
        }

        if (this.messageInput) {
            if (finalTranscript) {
                this.messageInput.value = finalTranscript.trim();
                this.messageInput.classList.add('voice-completed');
                setTimeout(() => {
                    this.messageInput.classList.remove('voice-completed');
                }, 2000);

                // Envoyer automatiquement si l'option est activée
                if (this.autoSendCheckbox && this.autoSendCheckbox.checked && finalTranscript.trim().length > 0) {
                    this.showMessage('Message envoyé automatiquement', 'success');
                    setTimeout(() => {
                        if (window.aiSearchChat) {
                            window.aiSearchChat.sendMessage();
                        }
                    }, 500);
                } else {
                    this.showMessage('Reconnaissance terminée. Cliquez sur Envoyer.', 'success');
                }
            } else if (interimTranscript) {
                this.messageInput.value = interimTranscript.trim();
            }
        }
    }

    onRecognitionError(event) {
        console.error('Speech recognition error:', event.error);
        this.isRecording = false;
        this.updateUI(false);

        let errorMessage = 'Erreur de reconnaissance vocale';
        switch(event.error) {
            case 'network':
                errorMessage = 'Erreur réseau. Vérifiez votre connexion internet.';
                break;
            case 'not-allowed':
                errorMessage = 'Accès au microphone refusé';
                this.permissionGranted = false;
                this.showPermissionInstructions();
                break;
            case 'no-speech':
                errorMessage = 'Aucune parole détectée. Essayez de parler plus fort.';
                break;
            case 'audio-capture':
                errorMessage = 'Microphone non détecté ou problème audio.';
                break;
        }

        this.showMessage(errorMessage, 'error');
        this.pendingRecording = false;
    }

    onRecognitionEnd() {
        console.log('Speech recognition ended');
        this.isRecording = false;
        this.updateUI(false);
    }

    updateUI(recording) {
        if (!this.voiceButton || !this.voiceIcon || !this.recordingIndicator) return;

        if (recording) {
            this.voiceButton.classList.add('recording');
            this.voiceIcon.className = 'bi bi-mic-fill';
            this.voiceButton.title = 'Cliquez pour arrêter l\'enregistrement';
            this.recordingIndicator.style.display = 'block';
        } else {
            this.voiceButton.classList.remove('recording');
            this.voiceIcon.className = 'bi bi-mic';
            this.voiceButton.title = 'Reconnaissance vocale';
            this.recordingIndicator.style.display = 'none';
        }
    }

    showMessage(message, type = 'info') {
        if (!this.recordingIndicator) return;

        const messageSpan = this.recordingIndicator.querySelector('span');
        if (!messageSpan) return;

        this.recordingIndicator.classList.remove('voice-error', 'voice-success');

        if (type === 'error') {
            this.recordingIndicator.classList.add('voice-error');
            messageSpan.innerHTML = '❌ ' + message;
        } else if (type === 'success') {
            this.recordingIndicator.classList.add('voice-success');
            messageSpan.innerHTML = '✅ ' + message;
        } else {
            messageSpan.innerHTML = '🎤 ' + message;
        }

        this.recordingIndicator.style.display = 'block';

        if (type === 'error' || type === 'success') {
            setTimeout(() => {
                if (!this.isRecording && this.recordingIndicator.style.display === 'block') {
                    this.recordingIndicator.style.display = 'none';
                }
            }, 3000);
        }
    }

    showPermissionInstructions() {
        if (!this.recordingIndicator) return;

        const messageSpan = this.recordingIndicator.querySelector('span');
        if (!messageSpan) return;

        this.recordingIndicator.classList.remove('voice-success');
        this.recordingIndicator.classList.add('voice-error');

        messageSpan.innerHTML = `
            🔒 <strong>Autorisation microphone requise</strong><br>
            <small>
                • Cliquez sur l'icône 🔒 ou 🎤 dans la barre d'adresse<br>
                • Sélectionnez "Autoriser" pour le microphone<br>
                • Puis cliquez à nouveau sur le bouton microphone
            </small>
        `;

        this.recordingIndicator.style.display = 'block';
    }

    resetPermissions() {
        console.log('Resetting permissions...');
        this.permissionGranted = false;
        this.pendingRecording = false;
        this.showMessage('Permissions réinitialisées. Vous pouvez réessayer.', 'info');
    }
}

// Fonctions globales pour la compatibilité
let voiceRecognition = null;

function startRecording() {
    if (voiceRecognition) {
        voiceRecognition.toggleRecording();
    }
}

function stopRecording() {
    if (voiceRecognition) {
        voiceRecognition.stopRecording();
    }
}

function resetPermissions() {
    if (voiceRecognition) {
        voiceRecognition.resetPermissions();
    }
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    // Attendre que les éléments soient disponibles
    setTimeout(() => {
        try {
            voiceRecognition = new VoiceSpeechRecognition();
            console.log('Voice recognition initialized successfully');
        } catch (error) {
            console.error('Error initializing voice recognition:', error);
            // Masquer le bouton si non supporté
            const voiceButton = document.getElementById('voiceButton');
            if (voiceButton) {
                voiceButton.style.display = 'none';
            }
        }
    }, 100);
});
