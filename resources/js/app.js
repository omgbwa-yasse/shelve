import './bootstrap';
import jQuery from 'jquery';

// Rendre jQuery disponible globalement pour les scripts inline
window.$ = window.jQuery = jQuery;

// Import Bootstrap JavaScript (déjà fait dans bootstrap.js mais on s'assure)
import 'bootstrap';

console.log('✅ Vite: Bootstrap et jQuery chargés');
