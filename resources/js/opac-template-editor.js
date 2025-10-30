/**
 * OPAC Template Editor - Asset Bundle
 * Scripts et styles pour l'éditeur de templates avancé
 */

// Import des dépendances CSS
import '../css/opac-template-editor.css';

// Import de CodeMirror et ses modes
import { EditorView, basicSetup } from 'codemirror';
import { html } from '@codemirror/lang-html';
import { css } from '@codemirror/lang-css';
import { javascript } from '@codemirror/lang-javascript';
import { oneDark } from '@codemirror/theme-one-dark';

// Variables globales
window.OpacTemplateEditor = {
    editors: {},
    autoSaveInterval: null,
    previewFrame: null,
    currentTemplate: null,

    // Configuration
    config: {
        autoSaveDelay: 30000, // 30 secondes
        previewUpdateDelay: 1000, // 1 seconde
        maxFileSize: 2 * 1024 * 1024, // 2MB
        allowedTags: ['div', 'span', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'a', 'img', 'ul', 'ol', 'li', 'table', 'tr', 'td', 'th', 'thead', 'tbody'],
        dangerousPatterns: [
            /eval\s*\(/gi,
            /<script[^>]*>/gi,
            /javascript\s*:/gi,
            /on\w+\s*=/gi,
            /expression\s*\(/gi
        ]
    }
};

// Fonctions utilitaires
const EditorUtils = {

    /**
     * Initialise un éditeur CodeMirror
     */
    initEditor(elementId, language = 'html', theme = 'light') {
        const element = document.getElementById(elementId);
        if (!element) return null;

        const extensions = [basicSetup];

        // Ajout du mode selon le langage
        switch (language) {
            case 'html':
                extensions.push(html());
                break;
            case 'css':
                extensions.push(css());
                break;
            case 'javascript':
                extensions.push(javascript());
                break;
        }

        // Thème sombre si demandé
        if (theme === 'dark') {
            extensions.push(oneDark);
        }

        const editor = new EditorView({
            doc: element.value || '',
            extensions,
            parent: element.parentNode
        });

        // Masquer l'élément textarea original
        element.style.display = 'none';

        // Sync avec l'élément original
        editor.updateListener = EditorView.updateListener.of((update) => {
            if (update.docChanged) {
                element.value = editor.state.doc.toString();
                element.dispatchEvent(new Event('input'));
            }
        });

        return editor;
    },

    /**
     * Valide le contenu pour la sécurité
     */
    validateContent(content, type = 'html') {
        const config = window.OpacTemplateEditor.config;

        // Vérification de la taille
        if (content.length > config.maxFileSize) {
            throw new Error(`Contenu trop volumineux (max ${config.maxFileSize / 1024 / 1024}MB)`);
        }

        // Vérification des patterns dangereux
        for (const pattern of config.dangerousPatterns) {
            if (pattern.test(content)) {
                throw new Error(`Contenu non sécurisé détecté: ${pattern.source}`);
            }
        }

        return true;
    },

    /**
     * Formatte le contenu selon le type
     */
    formatContent(content, type = 'html') {
        try {
            switch (type) {
                case 'html':
                    return this.formatHtml(content);
                case 'css':
                    return this.formatCss(content);
                case 'javascript':
                    return this.formatJs(content);
                default:
                    return content;
            }
        } catch (error) {
            console.warn('Erreur de formatage:', error);
            return content;
        }
    },

    /**
     * Formatte le HTML
     */
    formatHtml(html) {
        // Formatage simple du HTML
        return html
            .replace(/></g, '>\n<')
            .replace(/^\s+|\s+$/gm, '')
            .split('\n')
            .map((line, index) => {
                const indent = '  '.repeat(this.getIndentLevel(line));
                return indent + line.trim();
            })
            .join('\n');
    },

    /**
     * Formatte le CSS
     */
    formatCss(css) {
        return css
            .replace(/\s*{\s*/g, ' {\n  ')
            .replace(/;\s*/g, ';\n  ')
            .replace(/\s*}\s*/g, '\n}\n')
            .replace(/,\s*/g, ',\n');
    },

    /**
     * Formatte le JavaScript
     */
    formatJs(js) {
        // Formatage basique du JS
        return js
            .replace(/;\s*/g, ';\n')
            .replace(/{\s*/g, ' {\n  ')
            .replace(/}\s*/g, '\n}\n');
    },

    /**
     * Calcule le niveau d'indentation
     */
    getIndentLevel(line) {
        const trimmed = line.trim();
        if (trimmed.startsWith('</')) return 0;
        if (trimmed.includes('</')) return 1;
        return 1;
    }
};

// API pour la communication avec le serveur
const TemplateAPI = {

    /**
     * Sauvegarde automatique
     */
    async autoSave(templateId, data) {
        try {
            const response = await fetch(`/api/opac-templates/auto-save`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({
                    template_id: templateId,
                    ...data
                })
            });

            if (!response.ok) {
                throw new Error(`Erreur ${response.status}: ${response.statusText}`);
            }

            return await response.json();
        } catch (error) {
            console.error('Erreur auto-save:', error);
            this.showNotification('Erreur de sauvegarde: ' + error.message, 'error');
            throw error;
        }
    },

    /**
     * Génère une prévisualisation
     */
    async generatePreview(templateId, data, deviceType = 'desktop') {
        try {
            const response = await fetch(`/api/opac-templates/preview`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({
                    template_id: templateId,
                    device_type: deviceType,
                    ...data
                })
            });

            if (!response.ok) {
                throw new Error(`Erreur ${response.status}: ${response.statusText}`);
            }

            return await response.json();
        } catch (error) {
            console.error('Erreur preview:', error);
            this.showNotification('Erreur de prévisualisation: ' + error.message, 'error');
            throw error;
        }
    },

    /**
     * Valide le template
     */
    async validateTemplate(data) {
        try {
            const response = await fetch(`/api/opac-templates/validate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                throw new Error(`Erreur ${response.status}: ${response.statusText}`);
            }

            return await response.json();
        } catch (error) {
            console.error('Erreur validation:', error);
            throw error;
        }
    },

    /**
     * Affiche une notification
     */
    showNotification(message, type = 'info') {
        // Créer et afficher une notification toast
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <span class="toast-message">${message}</span>
                <button type="button" class="toast-close" onclick="this.parentElement.parentElement.remove()">&times;</button>
            </div>
        `;

        document.body.appendChild(toast);

        // Auto-suppression après 5 secondes
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 5000);
    }
};

// Gestionnaire d'événements
const EventHandlers = {

    /**
     * Initialise tous les gestionnaires d'événements
     */
    init() {
        this.initAutoSave();
        this.initPreview();
        this.initValidation();
        this.initDeviceToggle();
        this.initComponentLibrary();
    },

    /**
     * Auto-save périodique
     */
    initAutoSave() {
        const editor = window.OpacTemplateEditor;

        if (editor.autoSaveInterval) {
            clearInterval(editor.autoSaveInterval);
        }

        editor.autoSaveInterval = setInterval(async () => {
            if (!editor.currentTemplate) return;

            const data = this.collectEditorData();
            if (data) {
                try {
                    await TemplateAPI.autoSave(editor.currentTemplate.id, data);
                    this.updateSaveStatus('Sauvegardé automatiquement');
                } catch (error) {
                    this.updateSaveStatus('Erreur de sauvegarde', 'error');
                }
            }
        }, editor.config.autoSaveDelay);
    },

    /**
     * Prévisualisation en temps réel
     */
    initPreview() {
        let previewTimeout;

        document.addEventListener('input', (e) => {
            if (e.target.matches('.template-editor-input')) {
                clearTimeout(previewTimeout);
                previewTimeout = setTimeout(() => {
                    this.updatePreview();
                }, window.OpacTemplateEditor.config.previewUpdateDelay);
            }
        });
    },

    /**
     * Validation en temps réel
     */
    initValidation() {
        document.addEventListener('blur', (e) => {
            if (e.target.matches('.template-editor-input')) {
                this.validateField(e.target);
            }
        });
    },

    /**
     * Toggle entre devices
     */
    initDeviceToggle() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-device-toggle]')) {
                const device = e.target.dataset.deviceToggle;
                this.switchPreviewDevice(device);
            }
        });
    },

    /**
     * Bibliothèque de composants
     */
    initComponentLibrary() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-insert-component]')) {
                const componentType = e.target.dataset.insertComponent;
                this.insertComponent(componentType);
            }
        });
    },

    /**
     * Collecte les données des éditeurs
     */
    collectEditorData() {
        const editors = window.OpacTemplateEditor.editors;

        return {
            layout: editors.layout ? editors.layout.state.doc.toString() : '',
            custom_css: editors.css ? editors.css.state.doc.toString() : '',
            custom_js: editors.js ? editors.js.state.doc.toString() : '',
            variables: this.collectVariables()
        };
    },

    /**
     * Collecte les variables du template
     */
    collectVariables() {
        const variables = {};
        document.querySelectorAll('[data-template-variable]').forEach(input => {
            const key = input.dataset.templateVariable;
            variables[key] = input.value;
        });
        return variables;
    },

    /**
     * Met à jour le statut de sauvegarde
     */
    updateSaveStatus(message, type = 'success') {
        const statusElement = document.getElementById('save-status');
        if (statusElement) {
            statusElement.textContent = message;
            statusElement.className = `save-status save-status-${type}`;
        }
    },

    /**
     * Met à jour la prévisualisation
     */
    async updatePreview(deviceType = 'desktop') {
        const editor = window.OpacTemplateEditor;
        if (!editor.currentTemplate) return;

        const data = this.collectEditorData();

        try {
            const result = await TemplateAPI.generatePreview(
                editor.currentTemplate.id,
                data,
                deviceType
            );

            this.displayPreview(result.html, deviceType);
        } catch (error) {
            console.error('Erreur update preview:', error);
        }
    },

    /**
     * Affiche la prévisualisation
     */
    displayPreview(html, deviceType) {
        const previewFrame = document.getElementById('template-preview-frame');
        if (previewFrame) {
            const doc = previewFrame.contentDocument || previewFrame.contentWindow.document;
            doc.open();
            doc.write(html);
            doc.close();

            // Ajuster la classe pour le responsive
            previewFrame.className = `preview-frame preview-${deviceType}`;
        }
    },

    /**
     * Valide un champ
     */
    async validateField(field) {
        const content = field.value;
        const type = field.dataset.contentType || 'html';

        try {
            EditorUtils.validateContent(content, type);
            this.clearFieldError(field);
        } catch (error) {
            this.showFieldError(field, error.message);
        }
    },

    /**
     * Affiche une erreur de champ
     */
    showFieldError(field, message) {
        field.classList.add('field-error');

        let errorElement = field.parentNode.querySelector('.field-error-message');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'field-error-message';
            field.parentNode.appendChild(errorElement);
        }
        errorElement.textContent = message;
    },

    /**
     * Efface l'erreur d'un champ
     */
    clearFieldError(field) {
        field.classList.remove('field-error');
        const errorElement = field.parentNode.querySelector('.field-error-message');
        if (errorElement) {
            errorElement.remove();
        }
    },

    /**
     * Change le device de prévisualisation
     */
    switchPreviewDevice(deviceType) {
        // Mettre à jour les boutons
        document.querySelectorAll('[data-device-toggle]').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.deviceToggle === deviceType);
        });

        this.updatePreview(deviceType);
    },

    /**
     * Insère un composant
     */
    async insertComponent(componentType) {
        try {
            const response = await fetch(`/api/opac-templates/render-component`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({
                    component_type: componentType,
                    template_id: window.OpacTemplateEditor.currentTemplate?.id
                })
            });

            const result = await response.json();

            if (result.html) {
                this.insertIntoEditor(result.html);
            }
        } catch (error) {
            console.error('Erreur insertion composant:', error);
            TemplateAPI.showNotification('Erreur d\'insertion du composant', 'error');
        }
    },

    /**
     * Insère du contenu dans l'éditeur actif
     */
    insertIntoEditor(content) {
        const layoutEditor = window.OpacTemplateEditor.editors.layout;
        if (layoutEditor) {
            const cursor = layoutEditor.state.selection.main.head;
            layoutEditor.dispatch({
                changes: {
                    from: cursor,
                    insert: content
                }
            });
        }
    }
};

// Initialisation au chargement
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les éditeurs CodeMirror
    window.OpacTemplateEditor.editors = {
        layout: EditorUtils.initEditor('layout-editor', 'html'),
        css: EditorUtils.initEditor('css-editor', 'css'),
        js: EditorUtils.initEditor('js-editor', 'javascript')
    };

    // Initialiser les gestionnaires d'événements
    EventHandlers.init();

    // Charger le template actuel si présent
    const templateData = document.getElementById('current-template-data');
    if (templateData) {
        try {
            window.OpacTemplateEditor.currentTemplate = JSON.parse(templateData.textContent);
        } catch (error) {
            console.error('Erreur chargement template:', error);
        }
    }

    console.log('OPAC Template Editor initialisé');
});

// Export pour usage global
window.EditorUtils = EditorUtils;
window.TemplateAPI = TemplateAPI;
window.EventHandlers = EventHandlers;
