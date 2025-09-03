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
function consumirAppDataSessao() {
	try {
		const erro =  window.AppData.session_status_autenticacao ? window.AppData.session_status_autenticacao : null;
		const permissoes = window.AppData && window.AppData.session_permissoes_necessarias ? window.AppData.session_permissoes_necessarias : [];
		const naoAutorizado = window.AppData && window.AppData.session_usuario_autorizado ? window.AppData.session_usuario_autorizado : false;

		if (erro) {
			let body = "";
			if (Array.isArray(permissoes) && permissoes.length) {
				body += '<br><small><strong>Permissões necessárias:</strong> ' + permissoes.join(', ') + '</small>';
			}
			if (window.alerta && typeof window.alerta.erro === 'function') {
				window.alerta.erro(body, 'Acesso negado', 30000);
			} else {
				console.warn('Alerta indisponível: ', body);
			}
		} else if (naoAutorizado) {
			if (window.alerta && typeof window.alerta.erro === 'function') {
				window.alerta.erro('Usuário não autorizado.', 'Não autorizado', 5000);
			} else {
				console.warn('Alerta indisponível: usuário não autorizado');
			}
		}
	} catch (e) {
		// safe fallback
		console.warn('Erro ao processar alertas de sessão:', e);
	}
}

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', consumirAppDataSessao);
} else {
	consumirAppDataSessao();
}
