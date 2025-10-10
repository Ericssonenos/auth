// JS específico para a página de Usuários
// Este arquivo é importado pelo bundle principal (resources/js/app.js)
import $ from 'jquery';

/**
 * Classe responsável por gerenciar a lógica de negócio dos usuários
 * Segue os princípios SOLID e Clean Code
 */
class GerenciadorUsuarios {
    constructor() {
        this.usuarios_id_Selecionado = null;
    }

    /**
     * Gerencia o estado e visibilidade dos campos de senha no modal
     * @param {boolean} mostrar - Se true, mostra os campos de senha
     * @param {string|null} valorSenha - Valor da senha para preencher (opcional)
     */
    gerenciarCamposSenha(mostrar, valorSenha = null) {
        if (mostrar) {
            $('#divSenhaModal').removeClass('d-none');
            $('#btnMostrarSenha').removeClass('d-none');
            if (valorSenha) {
                $('#senha_Modal').val(valorSenha);
            }
        } else {
            $('#divSenhaModal').addClass('d-none');
            $('#btnMostrarSenha').addClass('d-none');
            $('#senha_Modal').val('');
        }
    }

    /**
     * Reseta o formulário e estado do modal para criação de novo usuário
     */
    resetarModalParaNovoUsuario() {
        this.usuarios_id_Selecionado = null;
        $('#modalUsuarioTitulo').text('Novo usuário');
        $('#formUser')[0].reset();
        $('#btnGerarNovaSenha').addClass('d-none');
        $('#email_Modal').prop('disabled', false);
        this.gerenciarCamposSenha(false);
    }

    /**
     * Configura o modal para edição de usuário existente
     * @param {Object} usuario - Dados do usuário
     */
    configurarModalParaEdicao(usuario) {
        this.usuarios_id_Selecionado = usuario.id_Usuario;
        $('#modalUsuarioTitulo').text('Editar usuário');
        $('#nome_Completo_Modal').val(usuario.nome_Completo);
        $('#email_Modal').val(usuario.email);
        $('#email_Modal').prop('disabled', true);
        $('#btnGerarNovaSenha').removeClass('d-none');

        if (usuario?.senha) {
            this.gerenciarCamposSenha(true, usuario.senha);
        } else {
            this.gerenciarCamposSenha(false);
        }
    }

    /**
     * Abre o modal de usuário (Bootstrap 5)
     */
    abrirModalUsuario() {
        new bootstrap.Modal(document.getElementById('modalUser')).show();
    }

    /**
     * Fecha o modal de usuário
     */
    fecharModalUsuario() {
        const modalEl = document.getElementById('modalUser');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    }

    /**
     * Mostra a senha temporariamente no campo
     * @param {number} duracao - Duração em milissegundos
     */
    mostrarSenhaTemporariamente(duracao = 8000) {
        const $senhaInput = $('#senha_Modal');
        $senhaInput.attr('type', 'text');
        setTimeout(() => {
            $senhaInput.attr('type', 'password');
        }, duracao);
    }

    /**
     * Atualiza o estado do usuário após criação
     * @param {number} idUsuario - ID do usuário criado
     */
    atualizarEstadoAposCriacao(idUsuario) {
        this.usuarios_id_Selecionado = idUsuario;
        $('#email_Modal').prop('disabled', true);
        $('#btnGerarNovaSenha').removeClass('d-none');
    }

    /**
     * Obtém o ID do usuário selecionado
     * @returns {number|null}
     */
    obterUsuarioSelecionado() {
        return this.usuarios_id_Selecionado;
    }

    /**
     * Define o ID do usuário selecionado
     * @param {number|null} id
     */
    setUsuarioSelecionado(id) {
        this.usuarios_id_Selecionado = id;
    }

    /**
     * Reseta o ID do usuário selecionado
     */
    resetarUsuarioSelecionado() {
        this.usuarios_id_Selecionado = null;
    }
}

// ========== INICIALIZAÇÃO E EVENT HANDLERS ==========

$(function () {
    // se a tabela não existir nesta página, aborta
    if (!document.querySelector('#dataTable_Usuarios')) return;

    // Instancia o gerenciador de usuários
    const gerenciador = new GerenciadorUsuarios();

    // DataTables e estados
    let dataTable_Permissoes_Modal = null;
    let dataTable_Grupos_Modal = null;
    const dataTable_SubPermissoes = {}; // cache de DataTables por id_Grupo

    // ========== EVENT HANDLERS ==========

    $('#btnNovo').off('click').on('click', function () {
        gerenciador.resetarModalParaNovoUsuario();
        gerenciador.abrirModalUsuario();
    });

    const dataTable_Usuario = $('#dataTable_Usuarios').DataTable({
        ajax: {
            method: 'POST',
            url: '/rh/api/usuarios/dados', // rota para buscar os dados (deve retornar JSON no formato DataTables)
            // dataSrc como função para validar e lidar com respostas inesperadas
            dataSrc: function (json) {
                try {
                    if (!json) {
                        // resposta vazia
                        window.alerta.erroPermissoes(mensagem = 'Acesso negado');
                        return [];
                    }
                    // se a propriedade data estiver presente e for um array, devolve-a
                    if (Array.isArray(json.data)) return json.data;

                    // se a própria resposta já for um array (endpoint simples), devolve-a
                    if (Array.isArray(json)) return json;

                } catch (e) {
                    window.alerta.erroPermissoes({ mensagem: String(e) });
                    return [];
                }
            },
            error: function (xhr, status, error) {
                window.alerta.erroPermissoes(xhr.responseJSON.mensagem, xhr.responseJSON.cod_permissoesNecessarias);
            }
        },
        columns: [
            { data: 'nome_Completo', title: 'Nome' },
            { data: 'email', title: 'Email' },
            {
                data: null,
                orderable: false,
                render: function (row) {
                    return `
                        <button class="btn btn-sm btn-primary btn-edit" data-id="${row.id_Usuario}">Editar</button>
                        <button class="btn btn-sm btn-secondary btn-atribuir-grupo" data-id="${row.id_Usuario}">Atribuir grupo</button>
                        <button class="btn btn-sm btn-info btn-permissoes" data-id="${row.id_Usuario}">Permissões</button>
                    `;
                }
            }
        ]
    });

    $('#dataTable_Usuarios').off('click', '.btn-edit').on('click', '.btn-edit', function () {
        const $tr = $(this).closest('tr');
        const rowData = dataTable_Usuario.row($tr).data();

        gerenciador.configurarModalParaEdicao(rowData);
        gerenciador.abrirModalUsuario();
    });

    // habilitar botão Excluir no modal e adicionar handler
    $('#btnExcluirUsuario').off('click').on('click', function (e) {
        e.preventDefault();
        if (!gerenciador.obterUsuarioSelecionado()) {
            window.alerta?.erroPermissoes?.({ mensagem: 'Nenhum usuário selecionado para exclusão.' });
            return;
        }

        if (!confirm('Deseja realmente excluir este usuário? Esta ação é irreversível (soft-delete).')) return;

        const $btn = $(this);
        $btn.prop('disabled', true).text('Excluindo...');

        $.ajax({
            url: '/rh/api/usuario/deletar/' + encodeURIComponent(gerenciador.obterUsuarioSelecionado()),
            method: 'DELETE',
            dataType: 'json',
            success: function (resp) {
                if (resp && resp.status == 200) {
                    gerenciador.fecharModalUsuario();

                    // recarregar tabela de usuários
                    try { dataTable_Usuario.ajax.reload(null, false); } catch (e) { }

                    window.alerta.sucesso?.(resp.mensagem);
                } else {
                    window.alerta.erroPermissoes(xhr.responseJSON.mensagem, xhr.responseJSON.cod_permissoesNecessarias);
                }
            },
            error: function (xhr, status, error) {
                window.alerta.erroPermissoes(xhr.responseJSON.mensagem, xhr.responseJSON.cod_permissoesNecessarias);
            },
            complete: function () {
                $btn.prop('disabled', false).text('Excluir');
            }
        });
    });

    $('#dataTable_Usuarios').off('click', '.btn-atribuir-grupo').on('click', '.btn-atribuir-grupo', function () {
        const $tr = $(this).closest('tr');
        const rowData = dataTable_Usuario.row($tr).data();
        gerenciador.setUsuarioSelecionado(rowData.id_Usuario);

        $('#modalGruposTitulo').text('Grupos do usuário: ' + (rowData?.email || '??'));
        const modalEl = document.getElementById('modalGrupos');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        // inicializar ou recarregar DataTable de grupos
        if (!dataTable_Grupos_Modal) {
            dataTable_Grupos_Modal = $('#dataTable_Grupos_Modal').DataTable({
                ajax: {
                    method: 'POST',
                    url: '/rh/api/grupos/dados',
                    data: function (d) {
                        d.usuario_id = gerenciador.obterUsuarioSelecionado();
                        d.fn = 'btn-atribuir-grupo';
                        d.order_by = 'CASE WHEN rug.id_rel_usuario_grupo IS NOT NULL THEN 1 ELSE 0 END, g.nome_Grupo';
                        return d;
                    },
                    dataSrc: function (json) {
                        try {
                            if (!json) { window.alerta.erroPermissoes('Acesso negado'); return []; }
                            if (Array.isArray(json.data)) return json.data;
                            if (Array.isArray(json)) return json;
                            return [];
                        } catch (e) {
                            window.alerta.erroPermissoes(String(e));
                            return [];
                        }
                    },
                    error: function (xhr) {
                        window.alerta.erroPermissoes(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoesNecessarias);
                    }
                },
                columns: [
                    { data: 'id_Grupo', title: 'ID' },
                    { data: 'nome_Grupo', title: 'Grupo' },
                    {
                        data: null,
                        render: function (row) {
                            const assigned = row.id_rel_usuario_grupo;
                            const toggleBtn = assigned
                                ? `<button class="btn btn-sm btn-danger btn-atribuir-grupo-toggle" data-id="${row.id_rel_usuario_grupo}" data-action="remover">Remover</button>`
                                : `<button class="btn btn-sm btn-success btn-atribuir-grupo-toggle" data-id="${row.id_Grupo}" data-action="adicionar">Adicionar</button>`;
                            const expandBtn = `<button class="btn btn-sm btn-light btn-expand-grupo" data-grupo="${row.id_Grupo}">Permissões</button>`;
                            return expandBtn + ' ' + toggleBtn;
                        }
                    },
                    // coluna oculta para armazenar as permissões em XML usado no filtro
                    { data: 'permissoes_Grupo', title: 'Permissões (XML)', visible: false }

                ]
            });

            // expandir/mostrar subtabela de permissões do grupo
            $('#dataTable_Grupos_Modal tbody').off('click', '.btn-expand-grupo').on('click', '.btn-expand-grupo', function () {
                const $btn = $(this);
                const tr = $btn.closest('tr');
                const row = dataTable_Grupos_Modal.row(tr);
                const rowData = row.data();
                const grupo_id = rowData.id_Grupo;

                // alterna child row
                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                    return;
                }

                // garante id único e evita reuse de elemento antigo
                const childId = 'subPermissoes_' + grupo_id + '_' + Date.now();
                const childHtml = `<div style="padding:10px;"><table id="${childId}" class="table table-sm w-100"></table></div>`;
                row.child(childHtml).show();
                tr.addClass('shown');

                // destruir cache antigo referente ao mesmo grupo (se existir) para evitar sobreposição
                if (dataTable_SubPermissoes[grupo_id]) {
                    try { dataTable_SubPermissoes[grupo_id].destroy(); } catch (e) { }
                    delete dataTable_SubPermissoes[grupo_id];
                }

                dataTable_SubPermissoes[grupo_id] = $('#' + childId).DataTable({
                    ajax: {
                        method: 'POST',
                        url: '/rh/api/permissoes/dados',
                        data: function (requestData) {
                            requestData.grupo_id = grupo_id;
                            requestData.fn = 'btn-expand-grupo';
                            return requestData;
                        },
                        dataSrc: function (json) {
                            if (!json) { window.alerta.erroPermissoes('Erro ao ler permissões do grupo'); return []; }
                            if (Array.isArray(json.data)) return json.data;
                            if (Array.isArray(json)) return json;
                            return [];
                        },
                        error: function (xhr) {
                            window.alerta.erroPermissoes(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoesNecessarias);
                        }
                    },
                    // ocultar dataTable_Grupos_Modal_length
                    lengthChange: false,
                    pageLength: 3,
                    paging: true,
                    searching: false,
                    info: false,
                    columns: [
                        { data: 'cod_permissao', title: 'Código' },
                        { data: 'descricao_permissao', title: 'Descrição' }
                    ]
                });
            });

        } else {
            // recarregar existente, antes fechar child rows e destruir subtabelas para evitar sobreposição
            // fechar todos child rows abertos
            dataTable_Grupos_Modal.rows().every(function () {
                if (this.child.isShown()) {
                    this.child.hide();
                }
            });
            // destruir subtabelas cacheadas
            Object.keys(dataTable_SubPermissoes).forEach(k => {
                try { dataTable_SubPermissoes[k].destroy(); } catch (e) { }
                delete dataTable_SubPermissoes[k];
            });

            dataTable_Grupos_Modal.ajax.reload(null, false);
        }
    });

    // adicionar / remover grupo
    $('#dataTable_Grupos_Modal').off('click', '.btn-atribuir-grupo-toggle').on('click', '.btn-atribuir-grupo-toggle', function () {

        const tr = $(this).closest('tr');
        const rowData = dataTable_Grupos_Modal.row(tr).data();
        const grupo_id = rowData.id_Grupo; // id do grupo
        const id_rel_usuario_grupo = rowData.id_rel_usuario_grupo; // id do relacionamento (se existir)
        const usuario_id = gerenciador.obterUsuarioSelecionado();

        const $btn = $(this);
        $btn.prop('disabled', true).text('...');

        if (!id_rel_usuario_grupo) {
            $.ajax({
                url: '/rh/api/usuario/grupo/adicionar',
                method: 'POST',
                data: {
                    usuario_id: usuario_id,
                    grupo_id: grupo_id
                },
                dataType: 'json',
                success: function (resp) {
                    if (resp && resp.status === 201) {
                        window.alerta.sucesso?.(resp.mensagem || 'Grupo adicionado.');
                        dataTable_Grupos_Modal.ajax.reload(null, false);
                    } else {
                        window.alerta.erroPermissoes(resp?.mensagem || 'Erro ao adicionar grupo');
                        $btn.prop('disabled', false).text('Adicionar');
                    }
                },
                error: function (xhr) {
                    window.alerta.erroPermissoes(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoesNecessarias);
                }
            });
        } else {
            $.ajax({
                url: '/rh/api/usuario/grupo/remover/' + encodeURIComponent(id_rel_usuario_grupo),
                method: 'DELETE',
                dataType: 'json',
                success: function (resp) {
                    if (resp && resp.status === 204) {
                        window.alerta.sucesso?.(resp.mensagem || 'Grupo removido.');
                        dataTable_Grupos_Modal.ajax.reload(null, false);
                    } else {
                        window.alerta.erroPermissoes(resp?.mensagem || 'Erro ao remover grupo');
                        $btn.prop('disabled', false).text('Remover');
                    }
                },
                error: function (xhr) {
                    window.alerta.erroPermissoes(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoesNecessarias);
                }
            });
        }
    });

    // resetar usuarios_id_Selecionado para 0 quando qualquer modal relevante for fechado
    $('#modalUser, #modalGrupos, #modalPermissoes').on('hidden.bs.modal', function () {
        gerenciador.resetarUsuarioSelecionado();

        // cleanup: destruir DataTable de grupos e subtabelas para evitar sobreposição ao reabrir
        try {
            if (typeof dataTable_Grupos_Modal !== 'undefined' && dataTable_Grupos_Modal) {
                // fechar child rows se houver
                try { dataTable_Grupos_Modal.rows().every(function () { if (this.child.isShown()) this.child.hide(); }); } catch (e) { }
                try { dataTable_Grupos_Modal.destroy(); } catch (e) { }
                dataTable_Grupos_Modal = null;
            }
        } catch (e) { }

        try {
            if (typeof dataTable_SubPermissoes !== 'undefined') {
                Object.keys(dataTable_SubPermissoes).forEach(k => {
                    try { dataTable_SubPermissoes[k].destroy(); } catch (e) { }
                    try { delete dataTable_SubPermissoes[k]; } catch (e) { }
                });
            }
        } catch (e) { }
    });

    $('#dataTable_Usuarios').off('click', '.btn-permissoes').on('click', '.btn-permissoes', function () {
        const $tr = $(this).closest('tr');
        const rowData = dataTable_Usuario.row($tr).data();
        gerenciador.setUsuarioSelecionado(rowData.id_Usuario);
        // abrir modal
        const modal = new bootstrap.Modal(document.getElementById('modalPermissoes'));
        modal.show();

        $('#modalPermissoesTitulo').text('Permissões do usuário: ' + (rowData?.email || '??'));


        // inicializar ou recarregar DataTable de permissões
        // passar o id_Usuario pelo body da requisição POST
        if (!dataTable_Permissoes_Modal) {
            dataTable_Permissoes_Modal = $('#dataTable_Permissoes_Modal').DataTable({
                ajax: {
                    method: 'POST',
                    url: '/rh/api/permissoes/dados',
                    // enviar parametros dinamicamente a cada requisição
                    data: function (requestData) {
                        requestData.usuario_id = gerenciador.obterUsuarioSelecionado(); // variável atualizada antes do reload
                        requestData.fn = 'btn-permissoes';
                        requestData.order_by = 'CASE WHEN rup.id_rel_usuario_permissao IS NOT NULL THEN 1 ELSE 0 END, p.cod_permissao';
                        return requestData;
                    },
                    dataSrc: function (json) {
                        try {
                            if (!json) {
                                window.alerta.erroPermissoes(mensagem = 'Acesso negado');
                                return [];
                            }
                            if (Array.isArray(json.data)) return json.data;
                            if (Array.isArray(json)) return json;
                        } catch (e) {
                            window.alerta.erroPermissoes({ mensagem: String(e) });
                            return [];
                        }
                    },
                    error: function (xhr, status, error) {
                        window.alerta.erroPermissoes(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoesNecessarias);
                    }
                },
                columns: [
                    { data: 'cod_permissao', title: 'Código' },
                    { data: 'descricao_permissao', title: 'Descrição' },
                    {
                        data: 'id_rel_usuario_permissao',
                        render: function (data, type, row) {
                            let retorno = '';
                            if (row.ativo_Grupo) {
                                // criar um bolinha pequena com title "Vinculado por grupo"
                                retorno = '<span class="badge bg-success" title="Vinculado por grupo">G</span> ';
                            }
                            if (row.id_rel_usuario_permissao) {
                                retorno
                                    += `<button class="btn btn-sm btn-danger btn-permissao-toggle" data-id="${row.id_rel_usuario_permissao}" data-action="remover">Remover</button>`;
                            } else {
                                retorno += `<button class="btn btn-sm btn-success btn-permissao-toggle" data-id="${row.id_permissao}" data-action="adicionar">Adicionar</button>`;
                            }


                            return retorno;
                        }
                    }
                ]
            });
        } else {
            // atualizar variável com o id atual e recarregar (ajax.data() lerá usuarios_id_Selecionado)
            // (não é necessário mudar a URL)
            dataTable_Permissoes_Modal.ajax.reload(null, false); // false mantém a página atual
        }
    });

    // handler para add/remover permissões dentro do modal
    $('#dataTable_Permissoes_Modal').off('click', '.btn-permissao-toggle').on('click', '.btn-permissao-toggle', function () {

        const tr = $(this).closest('tr');
        const rowData = dataTable_Permissoes_Modal.row(tr).data();
        const usuario_id = gerenciador.obterUsuarioSelecionado();
        const id_rel_usuario_permissao = rowData.id_rel_usuario_permissao;
        const permissao_id = rowData.id_permissao;

        const $btn = $(this);
        $btn.prop('disabled', true).text('...');

        if (!id_rel_usuario_permissao) {
            $.ajax({
                url: '/rh/api/usuario/permissao/adicionar',
                method: 'POST',
                data: {
                    usuario_id: usuario_id,
                    permissao_id: permissao_id
                },
                dataType: 'json',
                success: function (resp) {
                    if (resp && resp.status === 201) {
                        window.alerta?.sucesso?.(resp.mensagem || 'Permissão adicionada.');
                        dataTable_Permissoes_Modal.ajax.reload(null, false);
                    } else {
                        window.alerta?.erro?.(resp.mensagem || 'Erro ao adicionar permissão.');
                        $btn.prop('disabled', false).text('Adicionar');
                    }
                },
                error: function (xhr) {
                    if (xhr.status === 403) {
                        window.alerta.erroPermissoes(xhr.responseJSON.mensagem, xhr.responseJSON.cod_permissoesNecessarias);
                        return;
                    } else {
                        window.alerta.erro('Erro: ' + (xhr.responseJSON?.mensagem || err), 'Erro', 7000);
                    }
                }
            });
        } else {
            // remover usa id_rel_usuario_permissao
            $.ajax({
                url: '/rh/api/usuario/permissao/remover/' + encodeURIComponent(id_rel_usuario_permissao),
                method: 'DELETE',
                dataType: 'json',
                success: function (resp) {
                    if (resp && resp.status === 204) {
                        window.alerta?.sucesso?.(resp.mensagem || 'Permissão removida.');
                        dataTable_Permissoes_Modal.ajax.reload(null, false);
                    } else {
                        window.alerta?.erro?.(resp.mensagem || 'Erro ao remover permissão.');
                        $btn.prop('disabled', false).text('Remover');
                    }
                },
                error: function (xhr) {
                    if (xhr.status === 403) {
                        window.alerta.erroPermissoes(xhr.responseJSON.mensagem, xhr.responseJSON.cod_permissoesNecessarias);
                        return;
                    } else {
                        window.alerta.erro('Erro: ' + (xhr.responseJSON?.mensagem || err), 'Erro', 7000);
                    }
                }
            });
        }
    });

    // onlclik para gera nova senha - chama API e preenche o campo senha_Modal com a senha retornada
    $('#btnGerarNovaSenha').off('click').on('click', function () {


        const $btn = $(this);
        $btn.prop('disabled', true).text('Gerando...');

        $.ajax({
            url: '/rh/usuario/' + encodeURIComponent(gerenciador.obterUsuarioSelecionado()) + '/gerar-senha',
            method: 'POST',
            dataType: 'json',
            success: function (resp) {
                if (resp && resp.status === 201 && resp.data && resp.data.senha) {
                    // preencher e mostrar campo de senha
                    gerenciador.gerenciarCamposSenha(true, resp.data.senha);

                    // mostrar senha temporariamente
                    gerenciador.mostrarSenhaTemporariamente(8000);

                    // feedback curto ao usuário
                    window.alerta?.sucesso?.('Senha temporária gerada com sucesso. Senha temporária: 10 minutos');

                    // refresca a tabela sem fechar o modal
                    dataTable_Usuario.ajax.reload(null, false); // false mantém a página atual
                } else {
                    window.alerta?.erro?.(resp.mensagem || 'Resposta inesperada ao gerar senha.');
                }
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.mensagem || xhr.responseJSON?.mensagem || 'Erro ao gerar senha.';
                if (xhr.status === 403) {
                    window.alerta.erroPermissoes?.(msg, xhr.responseJSON?.cod_permissoesNecessarias);
                } else {
                    window.alerta?.erro?.(msg);
                }
            },
            complete: function () {
                $btn.prop('disabled', false).text('Gerar Nova Senha');
            }
        });
    });
    // onlclik para gera nova senha - chama API e preenche o campo senha_Modal com a senha retornada
    $('#btnSalvarUsuario').on('click', function () {


        if (!gerenciador.obterUsuarioSelecionado()) {

            const payload = {
                nome_Completo: $('#nome_Completo_Modal').val(),
                email: $('#email_Modal').val(),
            };

            // criar
            $.ajax({
                url: '/rh/api/usuario/cadastrar',
                method: 'POST',
                data: payload,
                dataType: 'json',
                success: function (resp) {
                    if (resp && resp.status === 201) {
                        // se a API retornar senha (resp.data.senha) preenche e mostra como no fluxo gerar-senha
                        if (resp.data && resp.data.senha) {
                            gerenciador.gerenciarCamposSenha(true, resp.data.senha);
                            gerenciador.mostrarSenhaTemporariamente(8000);
                            window.alerta?.sucesso?.('Usuário criado. Senha temporária: 10 minutos');
                        } else {
                            window.alerta?.sucesso?.('Usuário criado com sucesso.');
                        }

                        // se a API retornar lastId, preencher o id no modal para permitir gerar nova senha / edição
                        if (resp.data && resp.data.lastId) {
                            gerenciador.atualizarEstadoAposCriacao(resp.data.lastId);
                        }

                        dataTable_Usuario.ajax.reload(null, false); // false mantém a página atual
                    } else {
                        window.alerta?.erro?.(resp.mensagem || 'Resposta inesperada do servidor.');
                    }
                },
                error: function (xhr, status, err) {

                    if (xhr.status === 403) {
                        window.alerta.erroPermissoes(xhr.responseJSON.mensagem, xhr.responseJSON.cod_permissoesNecessarias);
                        return;
                    } else {
                        window.alerta.erro('Erro: ' + (xhr.responseJSON?.mensagem || err), 'Erro', 7000);
                    }
                    dataTable_Usuario.ajax.reload();
                }
            });
        } else {
            const payload = {
                nome_Completo: $('#nome_Completo_Modal').val(),
            };
            // atualizar (usa mesmo molde de retorno/erros que o POST de cadastro)
            $.ajax({
                url: '/rh/api/usuario/atualizar/' + encodeURIComponent(gerenciador.obterUsuarioSelecionado()),
                method: 'PUT',
                data: payload,
                dataType: 'json',
                success: function (resp) {
                    if (resp && resp.status == 200) {
                        // se a API retornar senha (resp.data.senha) preenche e mostra como no fluxo gerar-senha
                        if (resp.data && resp.data.senha) {
                            gerenciador.gerenciarCamposSenha(true, resp.data.senha);
                            gerenciador.mostrarSenhaTemporariamente(8000);
                            window.alerta?.sucesso?.('Usuário atualizado. Senha temporária: 10 minutos');
                        } else {
                            window.alerta?.sucesso?.('Usuário atualizado com sucesso.');
                        }

                        //  atualiza tabela

                        dataTable_Usuario.ajax.reload();
                    } else {
                        window.alerta?.erro?.(resp.mensagem || 'Resposta inesperada do servidor.');
                    }
                },
                error: function (xhr, status, err) {
                    if (xhr.status === 403) {
                        window.alerta.erroPermissoes?.(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoesNecessarias);
                        return;
                    } else {
                        window.alerta?.erro?.('Erro: ' + (xhr.responseJSON?.mensagem || err), 'Erro', 7000);
                    }
                }
            });
        }
    });

    $('#btnMostrarSenha').off('click').on('click', function () {
        gerenciador.mostrarSenhaTemporariamente(15000);
    });
});
