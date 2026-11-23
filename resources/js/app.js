import './bootstrap';
import './track';
import './swap-tabs';
import './local-time';

// Alpine paketle birlikte gelir (eskiden ayrı bir CDN'den iniyordu: telefonda ek bağlantı ve gecikme)
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();
import './webmcp';
