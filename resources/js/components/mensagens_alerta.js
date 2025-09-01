// Mensagens de alerta em JavaScript puro (sem jQuery)
(function () {
    const containerId = 'mensagens_alerta_container';
    const styleId = 'mensagens_alerta_styles';

    function injectStyles() {
        if (document.getElementById(styleId)) return;
        const css = `
            #${containerId} { position: fixed; top: 1rem; right: 1rem; z-index: 1100; display:flex; flex-direction:column; gap:0.5rem; max-width:380px; }
            .mensagem-alerta { background: #fff; border-radius:8px; box-shadow: 0 6px 18px rgba(15,23,42,.12); padding:12px 14px; border-left:6px solid; overflow:hidden; opacity:0; transform:translateY(-6px); transition:opacity .25s ease, transform .25s ease; }
            .mensagem-alerta.show { opacity:1; transform:translateY(0); }
            .mensagem-alerta.success { border-color:#10b981; }
            .mensagem-alerta.error { border-color:#ef4444; }
            .mensagem-alerta .titulo { font-weight:700; margin-bottom:6px; color:#0f172a; }
            .mensagem-alerta .texto { font-size:0.95rem; color:#334155; }
            .mensagem-alerta .fechar { position:absolute; top:6px; right:8px; background:transparent; border:none; font-size:18px; cursor:pointer; color:#64748b; }
        `;
        const style = document.createElement('style');
        style.id = styleId;
        style.appendChild(document.createTextNode(css));
        document.head.appendChild(style);
    }

    function ensureContainer(position = 'top-right') {
        let c = document.getElementById(containerId);
        if (!c) {
            c = document.createElement('div');
            c.id = containerId;
            c.setAttribute('aria-live', 'polite');
            c.setAttribute('aria-atomic', 'true');
            document.body.appendChild(c);
        }
        // posicionamento suportado
        c.style.top = '';
        c.style.bottom = '';
        c.style.left = '';
        c.style.right = '';
        if (position === 'top-right') { c.style.top = '1rem'; c.style.right = '1rem'; }
        else if (position === 'top-left') { c.style.top = '1rem'; c.style.left = '1rem'; }
        else if (position === 'bottom-right') { c.style.bottom = '1rem'; c.style.right = '1rem'; }
        else if (position === 'bottom-left') { c.style.bottom = '1rem'; c.style.left = '1rem'; }
        return c;
    }

    function criarAlerta(heading, text, icon) {
        const id = 'mensagem_alerta_' + Date.now();
        const container = document.createElement('div');
        container.className = 'mensagem-alerta ' + (icon === 'success' ? 'success' : (icon === 'error' ? 'error' : ''));
        container.id = id;
        container.style.position = 'relative';

        const btn = document.createElement('button');
        btn.className = 'fechar';
        btn.setAttribute('aria-label', 'fechar');
        btn.innerHTML = '&times;';
        btn.addEventListener('click', function () { removerAlerta(container); });

        const titulo = document.createElement('div');
        titulo.className = 'titulo';
        titulo.innerHTML = heading || '';

        const texto = document.createElement('div');
        texto.className = 'texto';
        texto.innerHTML = text || '';

        container.appendChild(btn);
        container.appendChild(titulo);
        container.appendChild(texto);

        return container;
    }

    function removerAlerta(el) {
        if (!el) return;
        el.classList.remove('show');
        setTimeout(() => { if (el.parentNode) el.parentNode.removeChild(el); }, 300);
    }

    function mostrar(options) {
        injectStyles();
        const defaults = { heading: '', text: '', icon: 'success', showHideTransition: 'fade', position: 'top-right', hideAfter: 6000 };
        const opts = Object.assign({}, defaults, options || {});
        if (!opts.heading) opts.heading = opts.icon === 'success' ? 'Sucesso' : 'Erro';

        const c = ensureContainer(opts.position);
        const alertaEl = criarAlerta(opts.heading, opts.text, opts.icon);
        c.appendChild(alertaEl);
        // forçar reflow para transição
        void alertaEl.offsetWidth;
        alertaEl.classList.add('show');

        if (opts.hideAfter && opts.hideAfter > 0) {
            setTimeout(() => removerAlerta(alertaEl), opts.hideAfter);
        }

        return alertaEl;
    }

    // Expõe API em português sem usar $ ou jQuery
    window.alerta = {
        mostrar(opts) { return mostrar(opts); },
        sucesso(text, heading = 'Sucesso', hideAfter = 5000) { return mostrar({ heading, text, icon: 'success', hideAfter }); },
        erro(text, heading = 'Erro', hideAfter = 7000) { return mostrar({ heading, text, icon: 'error', hideAfter }); }
    };

})();
