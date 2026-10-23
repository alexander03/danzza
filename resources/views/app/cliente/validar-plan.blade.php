<?php
// Variables que envía ClienteController@validarPagoPlan:
//   $pago       Planpago PENDIENTE a validar
//   $cliente    cliente del pago
//   $historial  pagos anteriores del cliente (más reciente primero)
$fechaPago = $pago->fechapago ? \Carbon\Carbon::parse($pago->fechapago)->format('Y-m-d') : null;
$planNuevo = $pago->plan;
$planActual = $cliente ? $cliente->plan : null;
$badge = array('PENDIENTE' => 'badge-info', 'VALIDADO' => 'badge-success', 'RECHAZADO' => 'badge-danger');
?>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($pago, $formData) !!}
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	{!! Form::hidden('accion', 'CONFIRMAR', array('id' => 'accion')) !!}
	<div class="row">
		<div class="col-md-12">
			<table class="table table-sm table-borderless mb-2">
				<tr>
					<th style="width: 35%;">Cliente:</th>
					<td>{{ $cliente ? $cliente->apellidopaterno.' '.$cliente->apellidomaterno.' '.$cliente->nombres : '-' }}</td>
				</tr>
				<tr>
					<th>Plan actual:</th>
					<td>{{ $planActual ? $planActual->nombre : 'Sin plan' }}</td>
				</tr>
				<tr>
					<th>Plan solicitado:</th>
					<td>
						<b>{{ $planNuevo ? $planNuevo->nombre : '-' }}</b>
						@if($planActual && $planNuevo && $planActual->id == $planNuevo->id)
							<span class="badge badge-warning">RENOVACIÓN</span>
							<small>(créditos actuales: {{ (int) $cliente->creditos }})</small>
						@endif
						@if($planNuevo && !empty($planNuevo->vigencia))
							· Vigencia {{ $planNuevo->vigencia }} días
						@endif
					</td>
				</tr>
				<tr>
					<th>Monto:</th>
					<td>S/ {{ number_format($pago->monto, 2) }}</td>
				</tr>
				<tr>
					<th>Registrado:</th>
					<td>{{ $pago->created_at ? \Carbon\Carbon::parse($pago->created_at)->format('d/m/Y H:i') : '-' }}</td>
				</tr>
			</table>
		</div>
		<div class="col-md-12 mb-2">
			<div class="alert alert-info py-2 mb-0">
				Verifica que el N.° de operación, la fecha y el monto coincidan con el pago recibido en Yape.
				Al <b>confirmar</b>, se asigna el plan <b>{{ $planNuevo ? $planNuevo->nombre : '' }}</b> al cliente
				@if($planNuevo && !empty($planNuevo->vigencia))
					con vigencia hasta el <b>{{ \Carbon\Carbon::today()->addDays((int) $planNuevo->vigencia)->format('d/m/Y') }}</b>
				@endif
				. Al <b>rechazar</b>, el cliente conserva su plan actual.
			</div>
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
		<div class="col-md-6">
			<div class="form-group">
				{!! Form::label('montovalidado', 'Monto:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::number('montovalidado', null, array('class' => 'form-control input-xs', 'id' => 'montovalidado', 'placeholder' => 'Ingrese monto', 'step' => '0.01', 'min' => '0')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">
				{!! Form::label('observacion', 'Observación (se muestra al cliente si se rechaza):', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::text('observacion', null, array('class' => 'form-control input-xs', 'id' => 'observacion', 'maxlength' => '255', 'placeholder' => 'Ej. El N.° de operación no coincide')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-12">
			<div class="form-group">
				<div class="col-lg-12 col-md-12 col-sm-12 text-right">
					{!! Form::button('<i class="fa fa-check"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => 'confirmarPagoPlan(\''.$entidad.'\', this)')) !!}
					{!! Form::button('<i class="fa fa-times"></i> Rechazar Pago', array('class' => 'btn btn-danger btn-sm', 'id' => 'btnRechazar', 'onclick' => 'rechazarPagoPlan(\''.$entidad.'\', this)')) !!}
					{!! Form::button('<i class="fa fa-undo"></i> Cancelar', array('class' => 'btn btn-default btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
				</div>
			</div>
		</div>

		<div class="col-md-12">
			<h6 class="mt-2"><b>Historial de pagos del cliente</b></h6>
			@if(count($historial) == 0)
				<small class="text-muted">Es el primer pago de este cliente.</small>
			@else
				<table class="table table-sm table-striped text-center mb-0">
					<thead>
						<tr>
							<th>Registrado</th>
							<th>Plan</th>
							<th>Monto</th>
							<th>Operación</th>
							<th>Fecha pago</th>
							<th>Vigencia</th>
							<th>Estado</th>
						</tr>
					</thead>
					<tbody>
						@foreach($historial as $h)
						<tr>
							<td>{{ $h->created_at ? \Carbon\Carbon::parse($h->created_at)->format('d/m/Y') : '-' }}</td>
							<td>{{ $h->plan ? $h->plan->nombre : '-' }}</td>
							<td>S/ {{ number_format($h->monto, 2) }}</td>
							<td>{{ $h->operacion }}</td>
							<td>{{ $h->fechapago ? \Carbon\Carbon::parse($h->fechapago)->format('d/m/Y') : '-' }}</td>
							<td>
								@if($h->fechainicio)
									{{ \Carbon\Carbon::parse($h->fechainicio)->format('d/m/Y') }}
									@if($h->fechavencimiento) al {{ \Carbon\Carbon::parse($h->fechavencimiento)->format('d/m/Y') }} @endif
								@else
									-
								@endif
							</td>
							<td>
								<span class="badge {{ isset($badge[$h->situacion]) ? $badge[$h->situacion] : 'badge-secondary' }}">{{ $h->situacion }}</span>
								@if($h->situacion == 'RECHAZADO' && !empty($h->observacion))
									<br><small>{{ $h->observacion }}</small>
								@endif
							</td>
						</tr>
						@endforeach
					</tbody>
				</table>
			@endif
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
	configurarAnchoModal('800');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
});

function confirmarPagoPlan(entidad, btn){
	var form = $(IDFORMMANTENIMIENTO + '{!! $entidad !!}');
	form.find('#accion').val('CONFIRMAR');
	guardar(entidad, btn);
}

function rechazarPagoPlan(entidad, btn){
	if(!confirm('¿Deseas rechazar este pago? El cliente conservará su plan actual y podrá registrar el pago nuevamente.')){
		return false;
	}
	var form = $(IDFORMMANTENIMIENTO + '{!! $entidad !!}');
	form.find('#accion').val('RECHAZAR');
	guardar(entidad, btn);
}
</script>
