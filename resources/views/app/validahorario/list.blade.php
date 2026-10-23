@if(count($lista) == 0)
<h3 class="text-warning">No hay horarios con reservas en estas fechas.</h3>
@else
<table class="table table-sm text-center table-striped table-hover">
	<thead>
		<tr>
			<th>#</th>
			<th>Fecha</th>
			<th>Horario</th>
			<th>Clase</th>
			<th>Sede</th>
			<th>Instructor</th>
			<th>Reservas</th>
			<th>Estado</th>
			<th>Confirmó instructor</th>
			<th>Confirmó admin</th>
			<th>Operación</th>
		</tr>
	</thead>
	<tbody>
		@foreach ($lista as $i => $value)
		<?php
		$clave   = $value->horario_id . '|' . $value->fecha;
		$inicio  = \Carbon\Carbon::parse($value->fecha . ' ' . $value->horainicio);
		$empezo  = $inicio->lte($ahora);
		$urlConfirmar = URL::route($ruta['confirmar'], array($value->horario_id, $value->fecha, 'listar' => 'SI'));
		?>
		<tr>
			<td>{{ $i + 1 }}</td>
			<td>{{ \Carbon\Carbon::parse($value->fecha)->format('d/m/Y') }}</td>
			<td>{{ substr($value->horainicio, 0, 5) }} - {{ substr($value->horafin, 0, 5) }}</td>
			<td>{{ $value->clase }}</td>
			<td>{{ $value->sede }}</td>
			<td>{{ $value->instructor ?: '-' }}</td>
			<td>
				<b>{{ $value->total }}</b>
				<br><a href="#" class="small js-ver-reservas" data-clave="{{ $clave }}">Ver reservas</a>
			</td>
			<td>
				@if($value->situacion == 'ADMIN CONFIRMADO')
					<span class="badge badge-success">ADMIN CONFIRMADO</span>
				@elseif($value->situacion == 'INSTRUCTOR CONFIRMADO')
					<span class="badge badge-info">INSTRUCTOR CONFIRMADO</span>
				@else
					<span class="badge badge-warning">PENDIENTE</span>
				@endif
			</td>
			<td>{{ $value->fechainstructor ? \Carbon\Carbon::parse($value->fechainstructor)->format('d/m/Y H:i') : '-' }}</td>
			<td>{{ $value->fechaadmin ? \Carbon\Carbon::parse($value->fechaadmin)->format('d/m/Y H:i') : '-' }}</td>
			<td>
				@if($esInstructor)
					{{-- Instructor: confirma su asistencia desde que empieza la clase --}}
					@if($value->situacion)
						-
					@elseif($empezo)
						{!! Form::button('<i class="fas fa-check"></i> Confirmar asistencia', array('onclick' => 'modal (\''.$urlConfirmar.'\', \''.$titulo_confirmar.'\', this);', 'class' => 'btn btn-sm btn-outline-success')) !!}
					@else
						<small class="text-muted">Desde las {{ $inicio->format('H:i') }}</small>
					@endif
				@elseif($esAdmin)
					{{-- Administrador: confirma después del instructor --}}
					@if($value->situacion == 'INSTRUCTOR CONFIRMADO')
						{!! Form::button('<i class="fas fa-user-check"></i> Confirmar asistencia', array('onclick' => 'modal (\''.$urlConfirmar.'\', \''.$titulo_confirmar.'\', this);', 'class' => 'btn btn-sm btn-outline-success')) !!}
					@elseif($value->situacion == 'ADMIN CONFIRMADO')
						-
					@else
						<small class="text-muted">Esperando al instructor</small>
					@endif
				@else
					-
				@endif
			</td>
		</tr>
		{{-- Clientes que reservaron este horario ese día --}}
		<tr class="fila-reservas" data-clave="{{ $clave }}" style="display: none;">
			<td colspan="11" class="text-left">
				<table class="table table-sm table-bordered mb-0">
					<thead>
						<tr><th>Lugar</th><th>Cliente</th><th>Asistencia</th></tr>
					</thead>
					<tbody>
						@foreach(isset($clientes[$clave]) ? $clientes[$clave] : array() as $c)
							<tr>
								<td>{{ $c->ubicacion }}</td>
								<td>{{ $c->cliente }}</td>
								<td>{{ $c->asistencia ?: '-' }}</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</td>
		</tr>
		@endforeach
	</tbody>
</table>
<script type="text/javascript">
	$('#listado{{ $entidad }} .js-ver-reservas').on('click', function (e) {
		e.preventDefault();
		var clave = $(this).data('clave');
		var $fila = $('#listado{{ $entidad }} .fila-reservas').filter(function () { return $(this).data('clave') == clave; });
		$fila.toggle();
		$(this).text($fila.is(':visible') ? 'Ocultar reservas' : 'Ver reservas');
	});
</script>
@endif
