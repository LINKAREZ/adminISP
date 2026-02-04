/**
 * Punto de entrada alternativo para aplicaciones sin AdminLTE
 * Para páginas que solo usan jQuery/Bootstrap sin AdminLTE
 *
 * NOTA: Para páginas con AdminLTE, usar adminlte.js en su lugar
 */

// Configuración base
import './bootstrap';

// Logger helper
import './logger';

// Librerías globales
import Chart from 'chart.js/auto';
window.Chart = Chart;
