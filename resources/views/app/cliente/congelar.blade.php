<?php
// Variables que envía ClienteController@congelar:
//   $cliente, $pago (pago vigente o null), $error (motivo por el que no se puede congelar o null), $hoy,
//   $formData, $entidad, $boton, $listar
?>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::open($formData) !!}
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	<div class="row">
		<div class="col-md-12">
			<table class="table table-sm mb-2">
				<tr><th style="width: 140px;">Cliente</th><td>{{ $cliente->apellidopaterno.' '.$cliente->apellidomaterno.' '.$cliente->nombres }}</td></tr>
				<tr><th>Plan</th><td>{{ optional($cliente->plan)->nombre ?? '-' }} · {{ (int) $cliente->creditos }} créditos</td></tr>
				<tr><th>Vence</th><td>{{ ($pago && $pago->fechavencimiento) ? \Carbon\Carbon::parse($pago->fechavencimiento)->format('d/m/Y') : '-' }}</td></tr>
			</table>
		</div>
		@if($error)
			<div class="col-md-12">
				<div class="alert alert-warning py-2">{{ $error }}</div>
			</div>
		@else
			<div class="col-md-12">
				<div class="form-group">
					{!! Form::label('fechainicio', 'Congelar desde:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
					<div class="col-lg-12 col-md-12 col-sm-12">
						{!! Form::date('fechainicio', $hoy, array('class' => 'form-control input-xs', 'id' => 'fechainicio', 'min' => $hoy)) !!}
					</div>
				</div>
			</div>
			<div class="col-md-12">
				<div class="form-group">
					{!! Form::label('motivo', 'Motivo (opcional):', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
					<div class="col-lg-12 col-md-12 col-sm-12">
						{!! Form::text('motivo', null, array('class' => 'form-control input-xs', 'id' => 'motivo', 'maxlength' => '200', 'placeholder' => 'Ej. Vacaciones')) !!}
					</div>
				</div>
			</div>
			<div class="col-md-12">
				<div class="alert alert-info py-2">
					El cliente no podrá reservar desde esa fecha. Sus reservas desde esa fecha se cancelarán y se devolverán los créditos.
					El plan seguirá congelado hasta que presiones <b>"Descongelar"</b>; entonces los días congelados se sumarán a su vencimiento.
				</div>
			</div>
		@endif
		<div class="col-md-12">
			<div class="form-group">
				<div class="col-lg-12 col-md-12 col-sm-12 text-right">
					@if(!$error)
						{!! Form::button('<i class="fa fa-check"></i> '.$boton, array('class' => 'btn btn-primary btn-sm', 'id' => 'btnGuardar', 'onclick' => 'guardar(\''.$entidad.'\', this)')) !!}
					@endif
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
