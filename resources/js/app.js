import './bootstrap';
import * as bootstrap from 'bootstrap'; // Importa todas las funciones de bootstrap
window.bootstrap = bootstrap;          // Lo hace global

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();