<?php
// Variables que envía ClienteController@asignarPlan:
//   $cliente, $error (motivo por el que no se puede asignar o null), $cboPlanPago, $hoy,
//   $formData, $entidad, $boton, $listar
?>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::open($formData) !!}
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	<div class="row">
		<div class="col-md-12">
			<table class="table table-sm mb-2">
				<tr><th style="width: 140px;">Cliente</th><td>{{ $cliente->apellidopaterno.' '.$cliente->apellidomaterno.' '.$cliente->nombres }}</td></tr>
				<tr><th>DNI</th><td>{{ $cliente->documento }}</td></tr>
				@if($cliente->plan)
					<tr><th>Plan actual</th><td>{{ $cliente->plan->nombre }} · {{ (int) $cliente->creditos }} clases</td></tr>
				@endif
				@if(!empty($motivo))
					<tr><th>Motivo</th><td><span class="badge badge-warning">{{ $motivo }}</span></td></tr>
				@endif
			</table>
		</div>
		@if($error)
			<div class="col-md-12">
				<div class="alert alert-warning py-2">{{ $error }}</div>
			</div>
		@else
			<div class="col-md-12">
				<div class="form-group">
					{!! Form::label('planpago_plan_id', 'Plan:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
					<div class="col-lg-12 col-md-12 col-sm-12">
						{!! Form::select('planpago_plan_id', $cboPlanPago, null, array('class' => 'form-control input-xs', 'id' => 'planpago_plan_id')) !!}
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					{!! Form::label('operacion', 'N.° de operación:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
					<div class="col-lg-12 col-md-12 col-sm-12">
						{!! Form::text('operacion', null, array('class' => 'form-control input-xs', 'id' => 'operacion', 'maxlength' => '30', 'placeholder' => 'Ingrese N.° de operación')) !!}
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					{!! Form::label('fechapago', 'Fecha de pago:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
					<div class="col-lg-12 col-md-12 col-sm-12">
						{!! Form::date('fechapago', $hoy, array('class' => 'form-control input-xs', 'id' => 'fechapago', 'max' => $hoy)) !!}
					</div>
				</div>
			</div>
			<div class="col-md-12">
				<div class="alert alert-info py-2">
					El pago quedará <b>PENDIENTE</b>. El plan se activa cuando lo valides con <b>"Validar Pago"</b> en la lista de clientes.
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
