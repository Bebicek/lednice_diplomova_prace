import './bootstrap';
import { createPopper } from '@popperjs/core';
import ApexCharts from 'apexcharts';

// flatpickr
import flatpickr from 'flatpickr';
import { Czech } from 'flatpickr/dist/l10n/cs.js';
import 'flatpickr/dist/flatpickr.min.css';
flatpickr.localize(Czech);
// FullCalendar
import { Calendar } from '@fullcalendar/core';

window.createPopper = createPopper;
window.ApexCharts = ApexCharts;
window.flatpickr = flatpickr;
window.FullCalendar = Calendar;

// Alpine.js is getting started automatically no need for it to start manually due to Livewire 4 update
// import Alpine from 'alpinejs';
// window.Alpine = Alpine;
// Alpine.start();

// Initialize components on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    // Map imports
    if (document.querySelector('#mapOne')) {
        import('./components/map').then(module => module.initMap());
    }

    // Chart imports
    if (document.querySelector('#chartOne')) {
        import('./components/chart/chart-1').then(module => module.initChartOne());
    }
    if (document.querySelector('#chartTwo')) {
        import('./components/chart/chart-2').then(module => module.initChartTwo());
    }
    if (document.querySelector('#chartThree')) {
        import('./components/chart/chart-3').then(module => module.initChartThree());
    }
    if (document.querySelector('#chartSix')) {
        import('./components/chart/chart-6').then(module => module.initChartSix());
    }
    if (document.querySelector('#chartEight')) {
        import('./components/chart/chart-8').then(module => module.initChartEight());
    }
    if (document.querySelector('#chartThirteen')) {
        import('./components/chart/chart-13').then(module => module.initChartThirteen());
    }

    // Calendar init
    if (document.querySelector('#calendar')) {
        import('./components/calendar-init').then(module => module.calendarInit());
    }
});
