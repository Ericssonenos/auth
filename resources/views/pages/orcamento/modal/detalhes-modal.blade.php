@push('scripts')
    <script src="{{ asset('/views/orcamento/modal/detalhes-modal.js') }}"></script>
@endpush

<!-- Modal para visualizar detalhes do orçamento -->
<div class="modal fade" id="modal_detalhes_orcamento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="titulo_modal_detalhes">
                    <i class="bi bi-eye"></i>
                    Detalhes do Orçamento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body">

                <!-- Informações Gerais -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">
                                    <i class="bi bi-info-circle"></i>
                                    Informações Gerais
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Cliente:</strong> <span id="detalhes_cliente">N/A</span></p>
                                        <p><strong>Contato:</strong> <span id="detalhes_contato">N/A</span></p>
                                        <p><strong>Descrição:</strong> <span id="detalhes_descricao">N/A</span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Data Criação:</strong> <span id="detalhes_data_criacao">N/A</span></p>
                                        <p><strong>Prazo Entrega:</strong> <span id="detalhes_prazo_entrega">N/A</span></p>
                                        <p><strong>Validade:</strong> <span id="detalhes_validade">N/A</span></p>
                                    </div>
                                </div>
                                <div class="row" id="container_observacoes_detalhes" style="display: none;">
                                    <div class="col-12">
                                        <p><strong>Observações:</strong></p>
                                        <div class="border rounded p-2 bg-light" id="detalhes_observacoes"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">
                                    <i class="bi bi-graph-up"></i>
                                    Status e Totais
                                </h6>
                            </div>
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <span class="badge fs-6" id="detalhes_status_badge">N/A</span>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Valor Total</small>
                                    <h4 class="text-success" id="detalhes_valor_total">R$ 0,00</h4>
                                </div>
                                <div class="row text-center">
                                    <div class="col-6">
                                        <small class="text-muted">Itens</small>
                                        <div id="detalhes_total_itens">0</div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Desconto</small>
                                        <div id="detalhes_total_desconto">R$ 0,00</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Itens do Orçamento -->
                <div class="card mb-3">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-list-ul"></i>
                            Itens do Orçamento
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Produto/Serviço</th>
                                        <th class="text-center">Qtd</th>
                                        <th class="text-end">Vl. Unit.</th>
                                        <th class="text-center">Desc.</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="tabela_itens_detalhes">
                                    <!-- Itens carregados dinamicamente -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Histórico do Workflow -->
                <div class="card" id="container_historico_workflow" style="display: none;">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-clock-history"></i>
                            Histórico do Workflow
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="historico_movimentos">
                            <!-- Movimentos carregados dinamicamente -->
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" id="btn_detalhes_duplicar" class="btn btn-outline-secondary">
                    <i class="bi bi-copy"></i>
                    Duplicar
                </button>
                <button type="button" id="btn_detalhes_gerar_pdf" class="btn btn-outline-info d-none">
                    <i class="bi bi-file-pdf"></i>
                    Gerar PDF
                </button>
                <button type="button" id="btn_detalhes_editar" class="btn btn-outline-warning d-none">
                    <i class="bi bi-pencil"></i>
                    Editar
                </button>
                <button type="button" data-bs-dismiss="modal" class="btn btn-outline-dark">
                    Fechar
                </button>
            </div>

        </div>
    </div>
</div>
