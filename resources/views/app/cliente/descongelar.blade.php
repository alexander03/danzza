<?php
// Variables que envía ClienteController@descongelar:
//   $cliente, $congelamiento, $pago (pago del plan congelado o null), $dias (días que se suman),
//   $vencimientoNuevo (d/m/Y o null), $formData, $entidad, $boton, $listar
?>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::open($formData) !!}
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	<div class="row">
		<div class="col-md-12">
			<table class="table table-sm mb-2">
				<tr><th style="width: 160px;">Cliente</th><td>{{ $cliente->apellidopaterno.' '.$cliente->apellidomaterno.' '.$cliente->nombres }}</td></tr>
				<tr><th>Plan</th><td>{{ optional($cliente->plan)->nombre ?? '-' }} · {{ (int) $cliente->creditos }} créditos</td></tr>
				<tr><th>Congelado desde</th><td>{{ \Carbon\Carbon::parse($congelamiento->fechainicio)->format('d/m/Y') }}</td></tr>
				@if($congelamiento->motivo)
					<tr><th>Motivo</th><td>{{ $congelamiento->motivo }}</td></tr>
				@endif
				<tr><th>Días a sumar</th><td>{{ $dias }}</td></tr>
				<tr><th>Vencimiento actual</th><td>{{ ($pago && $pago->fechavencimiento) ? \Carbon\Carbon::parse($pago->fechavencimiento)->format('d/m/Y') : '-' }}</td></tr>
				<tr><th>Nuevo vencimiento</th><td><b>{{ $vencimientoNuevo ?: '-' }}</b></td></tr>
			</table>
		</div>
		<div class="col-md-12">
			<div class="form-group">
				<div class="col-lg-12 col-md-12 col-sm-12 text-right">
					{!! Form::button('<i class="fa fa-check"></i> '.$boton, array('class' => 'btn btn-primary btn-sm', 'id' => 'btnGuardar', 'onclick' => 'guardar(\''.$entidad.'\', this)')) !!}
					{!! Form::button('<i class="fa fa-undo"></i> Cancelar', array('class' => 'btn btn-default btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
				</div>
			</div>
		</div>
	</div>
{!! Form::close() !!}
<style type="text/css">
	#divMensajeError{!! $entidad !!} {
		color: red;
	}
</style>
<script type="text/javascript">
$(document).ready(function() {
	configurarAnchoModal('550');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
});
</script>
