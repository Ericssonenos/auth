// tabela permissões de grupo
let tb_modal_grupo_permissao;

// abrir Tabelas - de permissões no modal
function Carregar_Tb_Modal_Grupo_Permissao(dados_permissoes) {

    // Atualizar o titulo do modal
    $('#titulo_modal_permissao_grupo').text('Permissões do Grupo'+ dados_permissoes?.nome_Grupo);

    // Atualizar variável global do id do grupo selecionado
    const id_grupo_selecionado = dados_permissoes.id_Grupo;

    // Inicializar DataTable de permissões no modal, se ainda não estiver inicializado
    if (!tb_modal_grupo_permissao) {
        tb_modal_grupo_permissao = $('#tb_modal_grupo_permissao').DataTable({
            ajax: {
                type: 'POST',
                url: '/api/rh/permissao/dados',
                contentType: 'application/json',
                data: function (d) {
                    // Adicionar o ID do grupo selecionado aos dados da requisição
                    d.grupo_id = id_grupo_selecionado;
                    d.fn = 'fn-grupo-status';
                    return JSON.stringify(d);
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
                    window.alerta.erroPermissoes(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoes_necessarias);
                }
            },
            // COLUNAS / DEFINIÇÕES
            columns: [
                {
                    data: 'cod_permissao',
                    title: 'Nome da Permissão',
                    orderable: true,
                    className: 'text-start',
                    searchPanes: { show: true }
                },
                {
                    data: 'descricao_permissao',
                    title: 'Descrição da Permissão',
                    orderable: true,
                    className: 'text-start',
                    searchPanes: { show: true }
                },
                {
                    data: 'id_rel_grupo_permissao',
                    title: 'Ação <i class="bi bi-gear"></i>',
                    className: 'text-center',
                    render: function (data, type, row) {
                        let retorno = '<div class="d-flex align-items-center gap-1">';
                        if (row.ativo_Grupo) {
                            // criar um bolinha pequena com title "Vinculado por grupo"
                            retorno += '<span class="badge bg-success" title="Vinculado por grupo">G</span> ';
                        }
                        if (row.id_rel_grupo_permissao) {
                            retorno
                                += `<button class="btn btn-sm btn-danger btn-modal-permissao-toggle"  data-action="remover">Remover</button>`;
                        } else {
                            retorno += `<button class="btn btn-sm btn-success btn-modal-permissao-toggle"  data-action="adicionar">Adicionar</button>`;
                        }
                        retorno += '</div>';


                        return retorno;
                    }
                }
            ],
            // Outras opções do DataTable, se necessário
        });
    }


}


// Interaçao dos botões da tabela da tabela grupo

$('#tb_grupo').on('click', '.aba-usuario-tb-permissao', function () {
    console.log('Clicou no botão de permissões do grupo');
    const dados_grupo = tb_grupo.row($(this).closest('tr')).data();
    Carregar_Tb_Modal_Grupo_Permissao(dados_grupo);

    // Abrir o modal de permissões
    const modalElement = document.getElementById('modal_grupo_permissao');
    const modalInstance = new bootstrap.Modal(modalElement);
    modalInstance.show();
});
