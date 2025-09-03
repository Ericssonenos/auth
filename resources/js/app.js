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

// Consumidor de sessões para alertas (usa window.alerta)
function consumirAppErro() {
    try {
        const mensagem = window.AppErro.mensagem ? window.AppErro.mensagem : null;
        const cod_permissoes = window.AppErro.cod_permissoes ? window.AppErro.cod_permissoes : [];
        console.log(mensagem, cod_permissoes);
        console.log("teeste")
        if (mensagem) {

            let body = mensagem + '<br>';

            if (Array.isArray(cod_permissoes) && cod_permissoes.length) {
                body += '<br><small><strong>Permissões necessárias:</strong> ' + cod_permissoes.join(', ') + '</small>';
            }

            window.alerta.erro(body, 'Acesso negado', 30000);

        }

    } catch (e) {
        // safe fallback
        console.warn('Erro ao processar alertas de sessão:', e);
    }
}

// chamar ao carregar a página
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', consumirAppErro);
} else {
    consumirAppErro();
}
