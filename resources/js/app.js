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
// [ ] lugar errado :import './pages/usuarios';
import './pages/grupos';



// chamar ao carregar a página
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.alerta.erroPermissoes);
} else {
    window.alerta.erroPermissoes();
}

import jszip from 'jszip';
import pdfmake from 'pdfmake';
import DataTable from 'datatables.net-dt';
import 'datatables.net-autofill-dt';
import 'datatables.net-buttons-dt';
import 'datatables.net-buttons/js/buttons.colVis.mjs';
import 'datatables.net-buttons/js/buttons.html5.mjs';
import 'datatables.net-buttons/js/buttons.print.mjs';
import 'datatables.net-colreorder-dt';
import 'datatables.net-columncontrol-dt';
import DateTime from 'datatables.net-datetime';
import 'datatables.net-fixedcolumns-dt';
import 'datatables.net-fixedheader-dt';
import 'datatables.net-keytable-dt';
import 'datatables.net-responsive-dt';
import 'datatables.net-rowgroup-dt';
import 'datatables.net-rowreorder-dt';
import 'datatables.net-scroller-dt';
import 'datatables.net-searchbuilder-dt';
import 'datatables.net-searchpanes-dt';
import 'datatables.net-select-dt';
import 'datatables.net-staterestore-dt';


