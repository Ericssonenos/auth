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
                                <div class="col-md-6">
                                    <label for="id_cliente_modal" class="form-label">Cliente *</label>
                                    <select id="id_cliente_modal" name="id_cliente" class="form-select" required>
                                        <option value="">Selecione um cliente</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="contato_cliente_modal" class="form-label">Contato</label>
                                    <input type="text" id="contato_cliente_modal" name="contato_cliente"
                                           class="form-control" maxlength="255"
                                           placeholder="Nome do contato no cliente">
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
                                    <input type="text" id="descricao_modal" name="descricao"
                                           class="form-control" maxlength="255" required
                                           placeholder="Descrição do orçamento">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="prazo_entrega_modal" class="form-label">Prazo de Entrega</label>
                                    <input type="date" id="prazo_entrega_modal" name="prazo_entrega"
                                           class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label for="validade_orcamento_modal" class="form-label">Validade do Orçamento</label>
                                    <input type="date" id="validade_orcamento_modal" name="validade_orcamento"
                                           class="form-control">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <label for="observacoes_modal" class="form-label">Observações</label>
                                    <textarea id="observacoes_modal" name="observacoes" class="form-control"
                                            rows="2" placeholder="Observações adicionais"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Itens do Orçamento -->
                    <div class="card">
                        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
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
                                                <div class="col-6"><h6><strong>Total:</strong></h6></div>
                                                <div class="col-6 text-end">
                                                    <h6><strong><span id="total_geral_modal">R$ 0,00</span></strong></h6>
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
        <div class="card-body p-3">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Produto/Serviço *</label>
                    <select name="itens[INDEX][id_produto]" class="form-select produto-select-modal" required>
                        <option value="">Selecione um produto/serviço</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Quantidade *</label>
                    <input type="number" name="itens[INDEX][quantidade]"
                           class="form-control quantidade-input-modal"
                           min="1" step="0.01" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Valor Unitário *</label>
                    <input type="number" name="itens[INDEX][valor_unitario]"
                           class="form-control valor-unitario-input-modal"
                           min="0" step="0.01" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Desconto (%)</label>
                    <input type="number" name="itens[INDEX][desconto_percentual]"
                           class="form-control desconto-input-modal"
                           min="0" max="100" step="0.01">
                </div>
                <div class="col-md-1">
                    <label class="form-label">Total</label>
                    <input type="text" class="form-control total-item-display-modal" readonly>
                    <input type="hidden" name="itens[INDEX][valor_total]" class="total-item-hidden-modal">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-danger btn-sm btn-remover-item-modal">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-12">
                    <label class="form-label">Observações do Item</label>
                    <textarea name="itens[INDEX][observacoes]" class="form-control observacoes-item-modal"
                            rows="1" placeholder="Observações específicas deste item"></textarea>
                </div>
            </div>
        </div>
    </div>
</template>
