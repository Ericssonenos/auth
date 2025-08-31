import './bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import * as bootstrap from 'bootstrap';
// Expor objeto bootstrap globalmente para compatibilidade com scripts inline que usam `new bootstrap.Modal(...)`
window.bootstrap = bootstrap;
