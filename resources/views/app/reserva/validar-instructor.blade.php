<?php
// Variables que envía ReservaController@validarInstructor: $reserva, $formData, $entidad, $boton, $listar
$cliente    = $reserva->cliente;
$horario    = $reserva->horario;
$instructor = $horario ? $horario->instructor : null;
?>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($reserva, $formData) !!}
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
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
					<th>Fecha / Horario:</th>
					<td>
						{{ $reserva->fecha ? \Carbon\Carbon::parse($reserva->fecha)->format('d/m/Y') : '-' }}
						· {{ $horario ? $horario->horainicio.' - '.$horario->horafin : '-' }}
					</td>
				</tr>
				<tr>
					<th>Clase:</th>
					<td>{{ ($horario && $horario->clase) ? $horario->clase->nombre : '-' }}</td>
				</tr>
				<tr>
					<th>Instructor:</th>
					<td><b>{{ $instructor ? ($instructor->sobrenombre ?: $instructor->nombres) : '-' }}</b></td>
				</tr>
				<tr>
					<th>Lugar:</th>
					<td>N.° {{ $reserva->ubicacion }}</td>
				</tr>
				<tr>
					<th>Asistencia:</th>
					<td>{{ $reserva->asistencia ?: '-' }}</td>
				</tr>
			</table>
		</div>
		<div class="col-md-12 mb-2">
			<div class="alert alert-info py-2 mb-0">
				¿Confirmas que el instructor <b>{{ $instructor ? ($instructor->sobrenombre ?: $instructor->nombres) : '' }}</b>
				dictó esta clase? La reserva quedará como <b>INSTRUCTOR VALIDADO</b>.
			</div>
		</div>
		<div class="col-md-12">
			<div class="form-group">
				<div class="col-lg-12 col-md-12 col-sm-12 text-right">
					{!! Form::button('<i class="fa fa-check"></i> '.$boton, array('class' => 'btn btn-info btn-sm', 'id' => 'btnGuardar', 'onclick' => 'guardar(\''.$entidad.'\', this)')) !!}
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
	configurarAnchoModal('600');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
});
</script>
