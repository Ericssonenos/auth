import './bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import * as bootstrap from 'bootstrap';
// Expor objeto bootstrap globalmente para compatibilidade com scripts inline que usam `new bootstrap.Modal(...)`
window.bootstrap = bootstrap;

// jQuery + DataTables via Vite
import $ from 'jquery';
window.$ = window.jQuery = $;

// DataTables (usa jQuery) e seu CSS
import 'datatables.net';
import 'datatables.net-dt/css/jquery.dataTables.min.css';

// Plugin custom de toast (API local: window.alerta)
import './components/mensagens_alerta';

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
import './pages/grupos';



// chamar ao carregar a página
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.alerta.erroPermissoes);
} else {
    window.alerta.erroPermissoes();
}
