<?php
$fechaPago = $reserva->fechapago ? \Carbon\Carbon::parse($reserva->fechapago)->format('Y-m-d') : null;
$cliente = $reserva->cliente;
$grupo = $reserva->reservagrupo_id ? $reserva->grupo : null;
?>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($reserva, $formData) !!}
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	{!! Form::hidden('accion', 'CONFIRMAR', array('id' => 'accion')) !!}
	<div class="row">
		<div class="col-md-12">
			<table class="table table-sm table-borderless mb-2">
				<tr>
					<th style="width: 35%;">Código:</th>
					<td>{{ $reserva->codigo }}</td>
				</tr>
				<tr>
					<th>Cliente:</th>
					<td>{{ $cliente ? $cliente->apellidopaterno.' '.$cliente->apellidomaterno.' '.$cliente->nombres : '-' }}</td>
				</tr>
				<tr>
					<th>Sede:</th>
					<td>{{ $reserva->sucursal ? $reserva->sucursal->nombre : '-' }}</td>
				</tr>
				<tr>
					<th>{{ $grupo ? 'Fechas / Horario:' : 'Fecha / Horario:' }}</th>
					<td>
						@if($grupo)
							Del {{ \Carbon\Carbon::parse($grupo->fechainicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($grupo->fechafin)->format('d/m/Y') }}
							<span class="badge badge-primary">{{ $grupo->dias }} días</span>
						@else
							{{ $reserva->fecha ? \Carbon\Carbon::parse($reserva->fecha)->format('d/m/Y') : '-' }}
						@endif
						· {{ $reserva->horario ? $reserva->horario->horainicio.' - '.$reserva->horario->horafin : '-' }}
					</td>
				</tr>
				<tr>
					<th>Estado:</th>
					<td>{{ $reserva->situacion }}</td>
				</tr>
			</table>
		</div>
		<div class="col-md-12 mb-2">
			@if($grupo)
				<div class="alert alert-primary py-2 mb-2">
					Esta reserva es parte de un <b>rango de {{ $grupo->dias }} días</b> (código {{ $grupo->codigo }}).
					Al confirmar o rechazar, el pago se aplica a <b>todos los días</b> del rango.
				</div>
			@endif
			@if(empty($reserva->operacion))
				<div class="alert alert-warning py-2 mb-0">
					El cliente aún no registró su pago. Si pagó por otro medio, ingresa el N.° de operación y la fecha para confirmarlo.
				</div>
			@else
				<div class="alert alert-info py-2 mb-0">
					Verifica que el N.° de operación y la fecha coincidan con el pago recibido en Yape antes de confirmar.
				</div>
			@endif
		</div>
		<div class="col-md-6">
			<div class="form-group">
				{!! Form::label('operacion', 'N.° de operación:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::text('operacion', null, array('class' => 'form-control input-xs', 'id' => 'operacion', 'placeholder' => 'Ingrese N.° de operación', 'maxlength' => '30')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">
				{!! Form::label('fechapago', 'Fecha de pago:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::date('fechapago', $fechaPago, array('class' => 'form-control input-xs', 'id' => 'fechapago')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-12">
			<div class="form-group">
				<div class="col-lg-12 col-md-12 col-sm-12 text-right">
					{!! Form::button('<i class="fa fa-check"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => 'confirmarPago(\''.$entidad.'\', this)')) !!}
					@if(!empty($reserva->operacion))
						{!! Form::button('<i class="fa fa-times"></i> Rechazar Pago', array('class' => 'btn btn-danger btn-sm', 'id' => 'btnRechazar', 'onclick' => 'rechazarPago(\''.$entidad.'\', this)')) !!}
					@endif
					{!! Form::button('<i class="fa fa-undo"></i> Cancelar', array('class' => 'btn btn-default btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
				</div>
			</div>
		</div>
	</div>
{!! Form::close() !!}
<style type="text/css">
	#divMensajeError{!! $entidad !!} {
		color:red;
	}
</style>
<script type="text/javascript">
$(document).ready(function() {
	configurarAnchoModal('650');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
});

function confirmarPago(entidad, btn){
	var form = $(IDFORMMANTENIMIENTO + '{!! $entidad !!}');
	form.find('#accion').val('CONFIRMAR');
	guardar(entidad, btn);
}

function rechazarPago(entidad, btn){
	if(!confirm('¿Deseas rechazar este pago? El cliente deberá registrar nuevamente su N.° de operación.')){
		return false;
	}
	var form = $(IDFORMMANTENIMIENTO + '{!! $entidad !!}');
	form.find('#accion').val('RECHAZAR');
	guardar(entidad, btn);
}
</script>
