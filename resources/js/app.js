import './bootstrap';

import Alpine from 'alpinejs';
import { registrarComponentesMapa } from './mapa-comercio';
import { registrarComponenteLogo } from './logo-comercio';

window.Alpine = Alpine;

// Componentes del mapa (geolocalización de comercios). Deben registrarse antes de Alpine.start().
registrarComponentesMapa(Alpine);

// Componente del selector de logo del comercio. También antes de Alpine.start().
registrarComponenteLogo(Alpine);

Alpine.start();
