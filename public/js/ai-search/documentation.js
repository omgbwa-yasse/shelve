/**
 * Gestionnaire pour la navigation dans la documentation
 */
class DocumentationNavigation {
    constructor() {
        this.init();
    }

    init() {
        this.setupSmoothScrolling();
        this.setupScrollSpy();
    }

    setupSmoothScrolling() {
        // Navigation fluide pour les liens
        document.querySelectorAll('.list-group-item-action').forEach((link) => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = link.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);

                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });

                    // Mettre à jour l'état actif
                    this.updateActiveState(link);
                }
            });
        });
    }

    setupScrollSpy() {
        // Observer pour mettre en surbrillance la section courante
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const id = entry.target.id;
                    const correspondingLink = document.querySelector(`[href="#${id}"]`);
                    if (correspondingLink) {
                        this.updateActiveState(correspondingLink);
                    }
                }
            });
        }, {
            rootMargin: '-50px 0px -50px 0px'
        });

        // Observer toutes les sections
        document.querySelectorAll('section').forEach((section) => {
            observer.observe(section);
        });
    }

    updateActiveState(activeLink) {
        document.querySelectorAll('.list-group-item-action').forEach((item) => {
            item.classList.remove('active');
        });
        activeLink.classList.add('active');
    }
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    new DocumentationNavigation();
});
