<?php
// Variables que envía ReservaController@confirmarGrupo:
//   $reservas  reservas seleccionadas que aún no tienen ingreso validado
//   $formData, $entidad, $boton, $listar
?>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::open($formData) !!}
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	@foreach($reservas as $r)
		<input type="hidden" name="ids[]" value="{{ $r->id }}">
	@endforeach
	<div class="row">
		<div class="col-md-12">
			@if(count($reservas) == 0)
				<div class="alert alert-warning py-2">
					Las reservas seleccionadas ya tienen el ingreso validado o fueron eliminadas.
				</div>
			@else
				<div class="alert alert-info py-2">
					Se validará el ingreso de <b>{{ count($reservas) }}</b> {{ count($reservas) == 1 ? 'reserva' : 'reservas' }}
					(asistencia = <b>INGRESO VALIDADO</b>).
				</div>
				<div style="max-height: 320px; overflow-y: auto;">
					<table class="table table-sm table-striped text-center mb-2">
						<thead>
							<tr>
								<th>Código</th>
								<th>Cliente</th>
								<th>Fecha</th>
								<th>Horario</th>
								<th>Clase</th>
								<th>Lugar</th>
							</tr>
						</thead>
						<tbody>
							@foreach($reservas as $r)
								<tr>
									<td>{{ $r->codigo }}</td>
									<td>{{ $r->cliente ? $r->cliente->apellidopaterno.' '.$r->cliente->apellidomaterno.' '.$r->cliente->nombres : '-' }}</td>
									<td>{{ $r->fecha ? \Carbon\Carbon::parse($r->fecha)->format('d/m/Y') : '-' }}</td>
									<td>{{ $r->horario ? $r->horario->horainicio.' - '.$r->horario->horafin : '-' }}</td>
									<td>{{ ($r->horario && $r->horario->clase) ? $r->horario->clase->nombre : '-' }}</td>
									<td>{{ $r->ubicacion }}</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			@endif
		</div>
		<div class="col-md-12">
			<div class="form-group">
				<div class="col-lg-12 col-md-12 col-sm-12 text-right">
					@if(count($reservas) > 0)
						{!! Form::button('<i class="fa fa-check"></i> '.$boton, array('class' => 'btn btn-warning btn-sm', 'id' => 'btnGuardar', 'onclick' => 'guardar(\''.$entidad.'\', this)')) !!}
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
	configurarAnchoModal('800');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
});
</script>
