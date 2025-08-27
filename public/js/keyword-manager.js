/**
 * Gestionnaire de mots-clés pour les formulaires
 * Permet la saisie de mots-clés séparés par des points-virgules avec auto-complétion
 */
class KeywordManager {
    constructor(inputSelector, options = {}) {
        this.input = document.querySelector(inputSelector);
        this.options = {
            searchUrl: '/keywords/search',
            minChars: 2,
            debounceDelay: 300,
            maxSuggestions: 10,
            placeholder: 'Entrez les mots-clés séparés par des points-virgules (;)',
            ...options
        };

        this.suggestions = [];
        this.currentFocus = -1;
        this.debounceTimer = null;

        this.init();
    }

    init() {
        if (!this.input) {
            console.error('Input element not found');
            return;
        }

        this.setupInput();
        this.createSuggestionContainer();
        this.bindEvents();
    }

    setupInput() {
        this.input.setAttribute('placeholder', this.options.placeholder);
        this.input.setAttribute('autocomplete', 'off');
        this.input.classList.add('keyword-input');

        // Wrapper pour le positionnement
        const wrapper = document.createElement('div');
        wrapper.classList.add('keyword-input-wrapper');
        wrapper.style.position = 'relative';

        this.input.parentNode.insertBefore(wrapper, this.input);
        wrapper.appendChild(this.input);

        this.wrapper = wrapper;
    }

    createSuggestionContainer() {
        this.suggestionContainer = document.createElement('div');
        this.suggestionContainer.classList.add('keyword-suggestions');
        this.suggestionContainer.style.cssText = `
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        `;

        this.wrapper.appendChild(this.suggestionContainer);
    }

    bindEvents() {
        // Input events
        this.input.addEventListener('input', this.handleInput.bind(this));
        this.input.addEventListener('keydown', this.handleKeydown.bind(this));
        this.input.addEventListener('blur', this.handleBlur.bind(this));
        this.input.addEventListener('focus', this.handleFocus.bind(this));

        // Click outside to close
        document.addEventListener('click', (e) => {
            if (!this.wrapper.contains(e.target)) {
                this.hideSuggestions();
            }
        });
    }

    handleInput(e) {
        const value = e.target.value;
        const cursorPos = e.target.selectionStart;

        // Trouver le mot-clé actuel à la position du curseur
        const currentKeyword = this.getCurrentKeyword(value, cursorPos);

        if (currentKeyword.length >= this.options.minChars) {
            this.debouncedSearch(currentKeyword);
        } else {
            this.hideSuggestions();
        }
    }

    handleKeydown(e) {
        const suggestionItems = this.suggestionContainer.querySelectorAll('.keyword-suggestion-item');

        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.currentFocus = Math.min(this.currentFocus + 1, suggestionItems.length - 1);
                this.updateFocus(suggestionItems);
                break;

            case 'ArrowUp':
                e.preventDefault();
                this.currentFocus = Math.max(this.currentFocus - 1, -1);
                this.updateFocus(suggestionItems);
                break;

            case 'Enter':
                if (this.currentFocus >= 0 && suggestionItems[this.currentFocus]) {
                    e.preventDefault();
                    this.selectSuggestion(suggestionItems[this.currentFocus].textContent);
                }
                break;

            case 'Escape':
                this.hideSuggestions();
                break;
        }
    }

    handleBlur() {
        // Délai pour permettre le clic sur les suggestions
        setTimeout(() => {
            this.hideSuggestions();
        }, 200);
    }

    handleFocus() {
        const value = this.input.value;
        const cursorPos = this.input.selectionStart;
        const currentKeyword = this.getCurrentKeyword(value, cursorPos);

        if (currentKeyword.length >= this.options.minChars) {
            this.debouncedSearch(currentKeyword);
        }
    }

    getCurrentKeyword(value, cursorPos) {
        // Trouver le début et la fin du mot-clé actuel
        let start = cursorPos;
        let end = cursorPos;

        // Chercher le début (dernier point-virgule avant le curseur)
        while (start > 0 && value[start - 1] !== ';') {
            start--;
        }

        // Chercher la fin (prochain point-virgule après le curseur)
        while (end < value.length && value[end] !== ';') {
            end++;
        }

        return value.substring(start, end).trim();
    }

    debouncedSearch(query) {
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => {
            this.searchKeywords(query);
        }, this.options.debounceDelay);
    }

    async searchKeywords(query) {
        try {
            const response = await fetch(`${this.options.searchUrl}?q=${encodeURIComponent(query)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                }
            });

            if (response.ok) {
                const keywords = await response.json();
                this.displaySuggestions(keywords);
            }
        } catch (error) {
            console.error('Error searching keywords:', error);
        }
    }

    displaySuggestions(keywords) {
        this.suggestions = keywords.slice(0, this.options.maxSuggestions);
        this.currentFocus = -1;

        if (this.suggestions.length === 0) {
            this.hideSuggestions();
            return;
        }

        this.suggestionContainer.innerHTML = '';

        this.suggestions.forEach((keyword, index) => {
            const item = document.createElement('div');
            item.classList.add('keyword-suggestion-item');
            item.textContent = keyword;
            item.style.cssText = `
                padding: 8px 12px;
                cursor: pointer;
                border-bottom: 1px solid #eee;
            `;

            item.addEventListener('mousedown', (e) => {
                e.preventDefault(); // Empêche le blur de l'input
                this.selectSuggestion(keyword);
            });

            item.addEventListener('mouseenter', () => {
                this.currentFocus = index;
                this.updateFocus([item]);
            });

            this.suggestionContainer.appendChild(item);
        });

        this.showSuggestions();
    }

    updateFocus(items) {
        items.forEach((item, index) => {
            if (index === this.currentFocus) {
                item.style.backgroundColor = '#f0f0f0';
            } else {
                item.style.backgroundColor = '';
            }
        });
    }

    selectSuggestion(keyword) {
        const value = this.input.value;
        const cursorPos = this.input.selectionStart;

        // Trouver la position du mot-clé actuel
        let start = cursorPos;
        let end = cursorPos;

        while (start > 0 && value[start - 1] !== ';') {
            start--;
        }

        while (end < value.length && value[end] !== ';') {
            end++;
        }

        // Remplacer le mot-clé actuel
        const beforeKeyword = value.substring(0, start);
        const afterKeyword = value.substring(end);
        const newValue = beforeKeyword + keyword + afterKeyword;

        this.input.value = newValue;

        // Positionner le curseur après le mot-clé inséré
        const newCursorPos = start + keyword.length;
        this.input.setSelectionRange(newCursorPos, newCursorPos);

        this.hideSuggestions();
        this.input.focus();
    }

    showSuggestions() {
        this.suggestionContainer.style.display = 'block';
    }

    hideSuggestions() {
        this.suggestionContainer.style.display = 'none';
        this.currentFocus = -1;
    }

    // Méthode pour obtenir les mots-clés sous forme de tableau
    getKeywords() {
        return this.input.value
            .split(';')
            .map(keyword => keyword.trim())
            .filter(keyword => keyword.length > 0);
    }

    // Méthode pour définir les mots-clés
    setKeywords(keywords) {
        if (Array.isArray(keywords)) {
            this.input.value = keywords.join('; ');
        } else if (typeof keywords === 'string') {
            this.input.value = keywords;
        }
    }
}

// Export pour utilisation globale
window.KeywordManager = KeywordManager;
