@push('scripts')
	<script src="{{ asset('/views/rh/aba/permissao/modal/usuario-modal.js') }}"></script>
@endpush

<!-- Modal para relacionar usuários à permissão -->
<div class="modal fade" id="modal_permissao_usuario" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 id="titulo_modal_permissao_usuario" class="modal-title"></h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
			</div>
			<div class="modal-body">
				<table id="tb_modal_permissao_usuario" class="table table-sm table-striped" style="width:100%"></table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
			</div>
		</div>
	</div>
</div>
