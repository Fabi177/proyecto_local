import './bootstrap';

import Alpine from 'alpinejs';
import { registrarComponentesMapa } from './mapa-comercio';
import { registrarComponenteLogo } from './logo-comercio';
import { registrarComponentesHorariosPagos } from './horarios-pagos';
import { registrarBuscadorComercios } from './buscador-comercios';
import { registrarAutocompletadoComercios } from './autocompletado-comercios';
import { registrarAutocompletadoLocalidades } from './autocompletado-localidades';

window.Alpine = Alpine;

// Componentes del mapa (geolocalización de comercios). Deben registrarse antes de Alpine.start().
registrarComponentesMapa(Alpine);

// Componente del selector de logo del comercio. También antes de Alpine.start().
registrarComponenteLogo(Alpine);

// Componentes de horarios de atención, días no laborales y formas de pago. También antes de Alpine.start().
registrarComponentesHorariosPagos(Alpine);

// Buscador en vivo de los comercios del panel del comerciante. También antes de Alpine.start().
registrarBuscadorComercios(Alpine);

// Sugerencias en vivo (autocompletado) de los buscadores públicos. También antes de Alpine.start().
registrarAutocompletadoComercios(Alpine);

// Selector de ciudad / código postal con sugerencias (buscador público). También antes de Alpine.start().
registrarAutocompletadoLocalidades(Alpine);

Alpine.start();
