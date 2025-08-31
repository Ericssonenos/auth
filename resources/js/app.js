import './bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import * as bootstrap from 'bootstrap';
// Expor objeto bootstrap globalmente para compatibilidade com scripts inline que usam `new bootstrap.Modal(...)`
window.bootstrap = bootstrap;

// jQuery + DataTables via Vite
import $ from 'jquery';
window.$ = window.jQuery = $;

// DataTables (usa jQuery) e seu CSS
import 'datatables.net';
import 'datatables.net-dt/css/jquery.dataTables.min.css';

// Configurar CSRF para ajax do jQuery usando a meta tag (inserida no layout)
try {
	const tokenMeta = document.querySelector('meta[name="csrf-token"]');
	const token = tokenMeta ? tokenMeta.getAttribute('content') : null;
	if (token) {
		$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': token } });
	}
} catch (e) {
	// ambiente onde document não está disponível (ex: SSR) - ignore
}

// Importar scripts específicos por página
import './pages/usuarios';
