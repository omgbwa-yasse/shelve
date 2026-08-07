import './bootstrap';
import jQuery from 'jquery';
import * as bootstrap from 'bootstrap';

// Rendre jQuery et Bootstrap disponibles globalement pour les scripts inline
window.$ = window.jQuery = jQuery;
window.bootstrap = bootstrap;

console.log('✅ Vite: Bootstrap et jQuery chargés');
