import './bootstrap';

import Alpine from 'alpinejs';
import { registrarComponentesMapa } from './mapa-comercio';

window.Alpine = Alpine;

// Componentes del mapa (geolocalización de comercios). Deben registrarse antes de Alpine.start().
registrarComponentesMapa(Alpine);

Alpine.start();
