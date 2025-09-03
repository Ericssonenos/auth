// Mensagens de alerta em JavaScript puro (sem jQuery)
import '../../css/mensagens_alerta.css';

(function () {
    const containerId = 'mensagens_alerta_container';

    function criarContainer() {
        let elementoContainer = document.getElementById(containerId);
        if (!elementoContainer) {
            elementoContainer = document.createElement('div');
            elementoContainer.id = containerId;
            elementoContainer.setAttribute('aria-live', 'polite');
            elementoContainer.setAttribute('aria-atomic', 'true');
            document.body.appendChild(elementoContainer);
        }
        return elementoContainer;
    }

    function criarAlerta(heading, text, icon) {
        const id = 'mensagem_alerta_' + Date.now();
        const elementoAlerta = document.createElement('div');
        elementoAlerta.className = 'mensagem-alerta ' + (icon === 'success' ? 'success' : (icon === 'error' ? 'error' : ''));
        elementoAlerta.id = id;

        const btn = document.createElement('button');
        btn.className = 'fechar';
        btn.setAttribute('aria-label', 'fechar');
        btn.innerHTML = '&times;';
        btn.addEventListener('click', function () { removerAlerta(elementoAlerta); });

        const titulo = document.createElement('div');
        titulo.className = 'titulo';
        titulo.innerHTML = heading || '';

        const texto = document.createElement('div');
        texto.className = 'texto';
        texto.innerHTML = text || '';

        elementoAlerta.appendChild(btn);
        elementoAlerta.appendChild(titulo);
        elementoAlerta.appendChild(texto);

        return elementoAlerta;
    }

    function removerAlerta(elementoAlerta) {
        if (!elementoAlerta) return;
        elementoAlerta.classList.remove('show');
        setTimeout(() => { if (elementoAlerta.parentNode) elementoAlerta.parentNode.removeChild(elementoAlerta); }, 300);
    }

    function mostrar(opcoes) {

        const defaults = { heading: '', text: '', icon: 'success', showHideTransition: 'fade', hideAfter: 6000 };
        const opts = Object.assign({}, defaults, opcoes || {});
        if (!opts.heading)
            opts.heading = opts.icon === 'success' ? 'Sucesso' : 'Erro';

        // container e posicionamento são controlados via CSS em mensagens_alerta.css
        const elementoContainer = criarContainer();
        const elementoAlerta = criarAlerta(opts.heading, opts.text, opts.icon);
        elementoContainer.appendChild(elementoAlerta);
        // forçar reflow para transição
        void elementoAlerta.offsetWidth;
        elementoAlerta.classList.add('show');

        if (opts.hideAfter && opts.hideAfter > 0) {
            setTimeout(() => removerAlerta(elementoAlerta), opts.hideAfter);
        }

        return elementoAlerta;
    }

    // Expõe API em português sem usar $ ou jQuery
    window.alerta = {
        mostrar(opts) { return mostrar(opts); },
        sucesso(text, heading = 'Sucesso', hideAfter = 5000) { return mostrar({ heading, text, icon: 'success', hideAfter }); },
        erro(text, heading = 'Erro', hideAfter = 7000) { return mostrar({ heading, text, icon: 'error', hideAfter }); }
    };

})();
