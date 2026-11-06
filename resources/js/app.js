import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

if (document.getElementById('dashboard-chart-data')) {
    import('./dashboard.js');
}
