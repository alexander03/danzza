<?php
// Variables que envía ValidarHorarioController@confirmar:
//   $horario, $fecha, $validacion (o null), $reservas, $error (motivo por el que no puede confirmar o null),
//   $esInstructor, $formData, $entidad, $boton, $listar
?>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::open($formData) !!}
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	{!! Form::hidden('horario_id', $horario ? $horario->id : '', array('id' => 'horario_id')) !!}
	{!! Form::hidden('fecha', $fecha, array('id' => 'fecha')) !!}
	<div class="row">
		<div class="col-md-12">
			@if($horario)
				<table class="table table-sm mb-2">
					<tr><th style="width: 140px;">Fecha</th><td>{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</td></tr>
					<tr><th>Horario</th><td>{{ substr($horario->horainicio, 0, 5) }} - {{ substr($horario->horafin, 0, 5) }}</td></tr>
					<tr><th>Clase</th><td>{{ $horario->clase ? $horario->clase->nombre : '-' }}</td></tr>
					<tr><th>Sede</th><td>{{ $horario->sucursal ? $horario->sucursal->nombre : '-' }}</td></tr>
					<tr><th>Instructor</th><td>{{ $horario->instructor ? ($horario->instructor->sobrenombre ?: $horario->instructor->nombres) : '-' }}</td></tr>
					<tr><th>Nro. Clientes</th><td>{{ count($reservas) }}</td></tr>
					@if($validacion && $validacion->fechainstructor)
						<tr><th>Confirmó instructor</th><td>{{ \Carbon\Carbon::parse($validacion->fechainstructor)->format('d/m/Y H:i') }}</td></tr>
					@endif
				</table>
				@if(count($reservas))
					<div style="max-height: 220px; overflow-y: auto;" hidden>
						<table class="table table-sm table-striped text-center mb-2">
							<thead><tr><th>Lugar</th><th>Cliente</th><th>Asistencia</th></tr></thead>
							<tbody>
								@foreach($reservas as $r)
									<tr>
										<td>{{ $r->ubicacion }}</td>
										<td>{{ $r->cliente }}</td>
										<td>{{ $r->asistencia ?: '-' }}</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				@endif
			@endif

			@if($error)
				<div class="alert alert-warning py-2">{{ $error }}</div>
			@else
				<div class="alert alert-info py-2">
					@if($esInstructor)
						Confirmarás que dictaste esta clase. Después el administrador validará tu asistencia.
					@else
						El instructor ya confirmó su asistencia. ¿Confirmas la asistencia del instructor en esta clase?
					@endif
				</div>
			@endif
		</div>
		<div class="col-md-12">
			<div class="form-group">
				<div class="col-lg-12 col-md-12 col-sm-12 text-right">
					@if(!$error)
						{!! Form::button('<i class="fa fa-check"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => 'guardar(\''.$entidad.'\', this)')) !!}
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
	configurarAnchoModal('650');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
});
</script>
