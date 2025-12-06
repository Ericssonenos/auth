@push('scripts')
    <script src="{{ asset('/views/orcamento/modal/orcamento-modal.js') }}"></script>
@endpush

<!-- Modal Novo/Editar Orçamento (reaproveitável) -->
<div class="modal fade" id="modal_orcamento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="fm_modal_orcamento">

                <div class="modal-header">
                    <h5 class="modal-title" id="titulo_modal_orcamento"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body">

                    <!-- Informações do Cliente -->
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-person"></i>
                                Informações do Cliente
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <label for="id_cliente_modal" class="form-label">Cliente *</label>
                                    <div class="d-flex gap-2 align-items-start">
                                        <div class="flex-grow-1 position-relative">
                                            <input type="text" id="id_cliente_modal_search" class="form-control"
                                                placeholder="Digite para buscar cliente..." autocomplete="off">
                                            <div id="client_suggestions" class="list-group position-absolute w-100"
                                                style="z-index:1000; display:none;"></div>
                                        </div>
                                        <input type="hidden" id="id_cliente_modal" name="id_cliente">
                                        <button type="button" id="btn_add_cliente_teste"
                                            class="btn btn-outline-secondary">Criar cliente teste</button>
                                    </div>
                                    <div id="cliente_info_display" class="mt-2 row g-2" style="display:none;">
                                        <div class="col-12 col-lg-6 col-xl-4 col-print-4">
                                            <label class="form-label">Nome</label>
                                            <input type="text" id="cli_nome" class="form-control" readonly>
                                        </div>
                                        <div class="col-12 col-sm-6 col-lg-4 col-xl-3 col-print-3">
                                            <label class="form-label">CNPJ/CPF</label>
                                            <input type="text" id="cli_cnpj" class="form-control" readonly>
                                        </div>
                                        <div class="col-12 col-print-12">
                                            <label class="form-label">Endereço</label>
                                            <input type="text" id="cli_endereco" class="form-control" readonly>
                                        </div>
                                        <div class="col-12 col-md-6 col-xl-3 col-print-3">
                                            <label class="form-label">Telefone</label>
                                            <input type="text" id="cli_telefone" class="form-control" readonly>
                                        </div>
                                        <div class="col-12 col-md-6 col-xl-4 col-print-4">
                                            <label class="form-label">E-mail</label>
                                            <input type="text" id="cli_email" class="form-control" readonly>
                                        </div>
                                        <div class="col-12 col-md-6 col-xl-3 col-print-3">
                                            <label class="form-label">Contato</label>
                                            <input type="text" id="cli_contato" class="form-control" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informações do Orçamento -->
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-file-text"></i>
                                Informações do Orçamento
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="descricao_modal" class="form-label">Descrição *</label>
                                    <input type="text" id="descricao_modal" name="descricao" class="form-control"
                                        maxlength="255" required placeholder="Descrição do orçamento">
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <div class="flex-fill">
                                    <label for="prazo_entrega_modal" class="form-label">Prazo de Entrega</label>
                                    <input type="date" id="prazo_entrega_modal" name="prazo_entrega"
                                        class="form-control">
                                </div>
                                <div class="flex-fill">
                                    <label for="validade_orcamento_modal" class="form-label">Validade do
                                        Orçamento</label>
                                    <input type="date" id="validade_orcamento_modal" name="validade_orcamento"
                                        class="form-control">
                                </div>
                                <div id="div_comissao_modal" class="flex-fill">
                                    <label for="comissao_modal" class="form-label">Comissão (%)</label>
                                    <input type="number" id="comissao_modal" name="comissao" class="form-control"
                                        min="0" max="100" step="0.01" placeholder="0.00">
                                </div>
                                <div id="div_comissao_modal" class="flex-fill">
                                    <label for="id_vendedor_modal" class="form-label">Vendedor *</label>
                                    <div class="position-relative">
                                        <input type="text" id="id_vendedor_modal_search" class="form-control"
                                            placeholder="Digite para buscar vendedor..." autocomplete="off" required>
                                        <div id="vendedor_suggestions" class="list-group position-absolute w-100"
                                            style="z-index:1000; display:none;"></div>
                                    </div>
                                    <input type="hidden" id="id_vendedor_modal" name="id_vendedor">
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Itens do Orçamento -->
                    <div class="card">
                        <div
                            class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="bi bi-list"></i>
                                Itens do Orçamento
                            </h6>
                            <button type="button" id="btn_adicionar_item_modal" class="btn btn-sm btn-outline-dark">
                                <i class="bi bi-plus"></i>
                                Adicionar Item
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="container_itens_modal">
                                <!-- Itens serão inseridos dinamicamente aqui -->
                            </div>

                            <!-- Totalizadores -->
                            <div class="row mt-3">
                                <div class="col-md-6 offset-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body p-2">
                                            <div class="row">
                                                <div class="col-6"><strong>Subtotal:</strong></div>
                                                <div class="col-6 text-end">
                                                    <span id="total_subtotal_modal">R$ 0,00</span>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-6"><strong>Desconto:</strong></div>
                                                <div class="col-6 text-end">
                                                    <span id="total_desconto_modal">R$ 0,00</span>
                                                </div>
                                            </div>
                                            <hr class="my-1">
                                            <div class="row">
                                                <div class="col-6">
                                                    <h6><strong>Total:</strong></h6>
                                                </div>
                                                <div class="col-6 text-end">
                                                    <h6><strong><span id="total_geral_modal">R$ 0,00</span></strong>
                                                    </h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" id="btn_modal_orcamento_excluir" class="btn btn-outline-danger d-none">
                        Excluir
                    </button>
                    <button type="button" data-bs-dismiss="modal" class="btn btn-outline-dark">
                        Cancelar
                    </button>
                    <button type="button" id="btn_modal_orcamento_imprimir" class="btn btn-outline-warning d-none">
                        Imprimir
                    </button>
                    <button type="button" id="btn_modal_orcamento_workflow" class="btn btn-outline-primary d-none">
                        Enviar p/ Workflow
                    </button>
                    <button type="button" id="btn_modal_orcamento_salvar" class="btn btn-outline-success">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Template para item do orçamento -->
<template id="template_item_orcamento_modal">
    <div class="item-orcamento-modal card mb-2" data-item-index="INDEX">
        <div class="card-body p-2">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-4 col-lg-2 col-xl-2 col-print-2 produto-info" style="display:none;">
                    <label class="form-label">Código</label>
                    <input type="text" class="form-control produto-codigo-display" readonly>
                    <input type="hidden" name="itens[INDEX][codigo]" class="produto-codigo-hidden">
                </div>
                <div class="col-12 col-lg-4 col-xl-3 col-print-4 position-relative">
                    <label class="form-label">Produto/Serviço *</label>
                    <input type="text" class="form-control produto-search-modal"
                        placeholder="Digite para buscar produto..." autocomplete="off">
                    <div class="produto-suggestions list-group position-absolute w-100"
                        style="z-index:1000; display:none;"></div>
                    <input type="hidden" name="itens[INDEX][id_produto]" class="produto-id-modal">
                </div>
                <div class="col-6 col-md-4 col-lg-2 col-xl-2 col-print-2 produto-info" style="display:none;">
                    <label class="form-label">NCM</label>
                    <input type="text" class="form-control produto-ncm-display" readonly>
                    <input type="hidden" name="itens[INDEX][ncm]" class="produto-ncm-hidden">
                </div>
                <div class="col-6 col-md-4 col-lg-2 col-xl-2 col-print-2 produto-info" style="display:none;">
                    <label class="form-label">Marca</label>
                    <input type="text" class="form-control produto-marca-display" readonly>
                    <input type="hidden" name="itens[INDEX][marca]" class="produto-marca-hidden">
                </div>
                <div class="col-6 col-sm-4 col-lg-2 col-xl-1 col-print-1-5">
                    <label class="form-label">Quantidade *</label>
                    <input type="number" name="itens[INDEX][quantidade]" class="form-control quantidade-input-modal"
                        min="1" step="0.01" value="1" required>
                </div>
                <div class="col-12 col-md-4 col-lg-3 col-xl-2 col-print-2 produto-info" style="display:none;">
                    <label class="form-label">Impostos (% / R$)</label>
                    <input type="text" class="form-control produto-impostos-display" readonly>
                    <input type="hidden" name="itens[INDEX][impostos_json]" class="produto-impostos-json-hidden">
                    <input type="hidden" class="produto-impostos-percent-hidden">
                </div>
                <div class="col-6 col-sm-4 col-lg-2 col-xl-2 col-print-2 margem-percentual">
                    <label class="form-label">Margem (%)</label>
                    <input type="number" name="itens[INDEX][margem_percentual]"
                        class="form-control margem-input-modal" min="0" max="100" step="0.01"
                        value="10">
                </div>
                <div class="col-6 col-sm-4 col-lg-2 col-xl-2 col-print-2">
                    <label class="form-label">Desconto (%)</label>
                    <input type="number" name="itens[INDEX][desconto_percentual]"
                        class="form-control desconto-input-modal" min="0" max="100" step="0.01"
                        value="0">
                </div>
                <div class="col-6 col-sm-6 col-lg-3 col-xl-2 col-print-2">
                    <label class="form-label">Valor Unitário</label>
                    <input type="text" class="form-control valor-unitario-display-modal" readonly>
                    <input type="hidden" name="itens[INDEX][valor_unitario]" class="valor-unitario-hidden-modal">
                </div>
                <div class="col-6 col-sm-6 col-lg-3 col-xl-2 col-print-2">
                    <label class="form-label">Total</label>
                    <input type="text" class="form-control total-item-display-modal" readonly>
                    <input type="hidden" name="itens[INDEX][valor_total]" class="total-item-hidden-modal">
                </div>
                <div class="col-auto col-print-auto">
                    <button type="button" class="btn btn-outline-danger btn-sm btn-remover-item-modal">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
            <div class="mt-3 row g-2 produto-info" style="display:none;">
                <div class="col-12 col-lg-8 col-print-8 produto-descricao-display">
                    <label class="form-label">Descrição</label>
                    <input type="text" class="form-control produto-descricao-display" readonly>
                    <input type="hidden" name="itens[INDEX][descricao]" class="produto-descricao-hidden">
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-xl-3 col-print-3 produto-preco-compra-display">
                    <label class="form-label">Preço Compra</label>
                    <input type="text" class="form-control produto-preco-compra-display" readonly>
                    <input type="hidden" name="itens[INDEX][preco_compra]" class="produto-preco-compra-hidden">
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-xl-3 col-print-3 produto-margem-valor-display">
                    <label class="form-label">Margem R$</label>
                    <input type="text" class="form-control produto-margem-valor-display" readonly>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-xl-3 col-print-3 produto-comissao-valor-display">
                    <label class="form-label">Comissão R$</label>
                    <input type="text" class="form-control produto-comissao-valor-display" readonly>
                </div>
                <div class="col-12 col-print-12 observacoes-item-modal">
                    <label class="form-label">Observações do Item</label>
                    <textarea name="itens[INDEX][observacoes]" class="form-control observacoes-item-modal" rows="1"
                        placeholder="Observações específicas deste item"></textarea>
                </div>
            </div>
        </div>
    </div>
</template>
@push('styles')
    <style>
        /* Impressão: mostrar apenas o modal quando imprimir se ele estiver aberto */
        @media print {

            body * {
                visibility: hidden;
            }

            body {
                zoom: 70%;
            }

            #modal_orcamento,
            #modal_orcamento * {
                visibility: visible;
            }

            .modal-body,
            .modal-content,
            .modal-dialog {
                margin: 0 !important;
                padding: 0 !important;
                border-style: none !important;
            }

            #modal_orcamento>.modal-dialog {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                --bs-modal-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            /* Garantir que d-flex e flex-wrap funcionem corretamente na impressão */
            #modal_orcamento .d-flex {
                display: flex !important;
            }

            #modal_orcamento .flex-wrap {
                flex-wrap: wrap !important;
            }

            #modal_orcamento .flex-fill {
                flex: 1 1 auto !important;
            }

            /* Ocultar todos os botões na impressão */
            #modal_orcamento button,
            #modal_orcamento .btn,
            #modal_orcamento .modal-footer {
                display: none !important;
            }

            #div_comissao_modal,
            .margem-percentual,
            .produto-preco-compra-display,
            .produto-margem-valor-display,
            .produto-comissao-valor-display,
            .produto-descricao-display,
            .observacoes-item-modal {
                display: none !important;
            }

            .col-print-12 {
                flex: 0 0 auto !important;
                width: 100% !important;
            }

            .col-print-6 {
                flex: 0 0 auto !important;
                width: 50% !important;
            }

            .col-print-4 {
                flex: 0 0 auto !important;
                width: 33.3333% !important;
            }

            .col-print-3 {
                flex: 0 0 auto !important;
                width: 25% !important;
            }

            .col-print-2 {
                flex: 0 0 auto !important;
                width: 16.6667% !important;
            }

            .col-print-1-5 {
                flex: 0 0 auto !important;
                width: 12.5% !important;
            }

            .col-print-auto {
                flex: 0 0 auto !important;
                width: auto !important;
            }

            .col-print-6,
            .col-print-4,
            .col-print-3,
            .col-print-2,
            .col-print-1-5,
            .col-print-auto {
                max-width: none !important;
            }

        }
    </style>
@endpush

<!-- client autocomplete and behavior moved to public JS file: /public/views/orcamento/modal/orcamento-modal.js -->
