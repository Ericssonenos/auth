// Utilitário de impressão reutilizável (sem jQuery)
(function () {
    const namespace = window.impressao = window.impressao || {};

    if (typeof namespace.imprimirConteudoModal === 'function') {
        return;
    }

    namespace.imprimirConteudoModal = function (modalElement) {
        if (!modalElement) return;
        const modalContent = modalElement.querySelector('.modal-content');
        if (!modalContent) return;

        const clone = modalContent.cloneNode(true);
        const origControls = modalContent.querySelectorAll('input, textarea, select');
        const cloneControls = clone.querySelectorAll('input, textarea, select');

        origControls.forEach(function (origControl, index) {
            const cloneControl = cloneControls[index];
            if (!cloneControl) return;

            if (origControl.tagName === 'SELECT') {
                Array.prototype.forEach.call(origControl.options, function (option, optionIndex) {
                    if (cloneControl.options[optionIndex]) {
                        cloneControl.options[optionIndex].selected = option.selected;
                    }
                });
            } else if (origControl.type === 'checkbox' || origControl.type === 'radio') {
                cloneControl.checked = origControl.checked;
            } else {
                cloneControl.value = origControl.value;
            }

            if (origControl.disabled) {
                cloneControl.disabled = origControl.disabled;
            }
        });

        const printWindow = window.open('', '_blank', 'width=900,height=700');
        if (!printWindow) {
            window.alerta?.erro?.('Não foi possível abrir a janela de impressão. Verifique o bloqueador de pop-ups.');
            return;
        }

        try { printWindow.opener = null; } catch (e) { }

        const stylesHtml = Array.prototype.map.call(
            document.querySelectorAll('link[rel="stylesheet"], style'),
            function (node) { return node.outerHTML; }
        ).join('');

        const html = `<!DOCTYPE html>
            <html lang="pt-BR">
            <head>
            <meta charset="utf-8" />
            <title>Impressão</title>
            ${stylesHtml}
            <style>
                @page { size: auto; margin: 12mm; }
                html, body { height: auto !important; min-height: 0 !important; margin: 0; padding: 0; }
                .modal { position: static !important; display: block !important; opacity: 1 !important; }
                .modal-dialog { margin: 0 auto !important; max-width: 100% !important; transform: none !important; min-height: auto !important; }
                .modal-content { box-shadow: none !important; border: none !important; }
                .modal-body { overflow: visible !important; }
                .modal-backdrop { display: none !important; }
            </style>
            </head>
            <body>
            ${clone.outerHTML}
            </body>
            </html>`;

        printWindow.document.open();
        printWindow.document.write(html);
        printWindow.document.close();
        printWindow.document.title = 'Impressão - Usuário';

        const dispararImpressao = function () {
            printWindow.removeEventListener('load', dispararImpressao);
            printWindow.document.removeEventListener('DOMContentLoaded', dispararImpressao);
            printWindow.focus();
            printWindow.print();
            printWindow.setTimeout(function () {
                printWindow.close();
            }, 300);
        };

        printWindow.addEventListener('load', dispararImpressao);
        printWindow.document.addEventListener('DOMContentLoaded', dispararImpressao);
    };

})();
