import './bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import * as bootstrap from 'bootstrap';
// Expor objeto bootstrap globalmente para compatibilidade com scripts inline que usam `new bootstrap.Modal(...)`
window.bootstrap = bootstrap;



// Plugin custom de toast (API local: window.alerta)
import './components/mensagens_alerta';
import './components/impressao';

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

// Nota: após alterar este arquivo ou adicionar novos arquivos CSS/JS,

