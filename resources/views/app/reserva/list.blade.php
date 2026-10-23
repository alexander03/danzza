@if(count($lista) == 0)
<h3 class="text-warning">No se encontraron resultados.</h3>
@else
{!! $paginacion  !!}
{{-- Validar ingreso en grupo: se marcan las reservas con el checkbox y se validan juntas --}}
<div class="mb-2 text-left">
	<button type="button" class="btn btn-sm btn-warning" id="btnValidarGrupo" disabled
		data-url="{{ URL::route('reserva.confirmargrupo') }}">
		<i class="fas fa-check-double"></i> Validar ingreso seleccionados (<span id="cantSeleccionados">0</span>)
	</button>
</div>
<table id="example1" class="table table-sm text-center table-striped  table-hover">

	<thead>
		<tr>
			<th><input type="checkbox" id="chkTodos" title="Seleccionar todos los de esta página"></th>
			@foreach($cabecera as $key => $value)
				<th @if( (int)$value['numero'] > 1) colspan="{{ $value['numero'] }}" @endif>{!! $value['valor'] !!}</th>
			@endforeach
		</tr>
	</thead>
	<tbody>
		<?php
		$contador = $inicio + 1;
		?>
		@foreach ($lista as $key => $value)
		<?php
		$grupo = $value->reservagrupo_id ? $value->grupo : null;
		$diaDelRango = $grupo ? \Carbon\Carbon::parse($grupo->fechainicio)->diffInDays(\Carbon\Carbon::parse($value->fecha)) + 1 : null;
		?>
		<tr>
			<td>
				@if($value->asistencia != 'INGRESO VALIDADO')
					<input type="checkbox" class="chkReserva" value="{{ $value->id }}">
				@endif
			</td>
			<td>{{ $contador }}</td>
			<td>{{ $value->codigo }}</td>
			<td>{{ $value->cliente->apellidopaterno.' '.$value->cliente->apellidomaterno.' '.$value->cliente->nombres }}</td>
			<td>{{ $value->sucursal->nombre }}</td>
			<td>
				{{ $value->fecha ? \Carbon\Carbon::parse($value->fecha)->format('d/m/Y') : '-' }}
				@if($grupo)
					<br><span class="badge badge-primary" title="Reserva por rango {{ $grupo->codigo }}">
						Rango {{ \Carbon\Carbon::parse($grupo->fechainicio)->format('d/m') }} al {{ \Carbon\Carbon::parse($grupo->fechafin)->format('d/m') }}
					</span>
					<br><small>Día {{ $diaDelRango }} de {{ $grupo->dias }}</small>
				@endif
			</td>
			<td>{{ $value->horario->horainicio.' '.$value->horario->horafin }}</td>
			<td>{{ $value->horario->clase->nombre }}</td>
			<td>{{ $value->horario->instructor->sobrenombre ?: $value->horario->instructor->nombres }}</td>
			<td>{{ $value->ubicacion }}</td>
			<td>{{ $value->situacion }}</td>
			<td>{{ $value->asistencia }}</td>
			@if($value->asistencia != 'INGRESO VALIDADO')
				<td>{!! Form::button('<div class="fas fa-check"></div> Validar Ingreso', array('onclick' => 'modal (\''.URL::route($ruta["confirmar"], array($value->id, 'SI')).'\', \''.$titulo_confirmar.'\', this);', 'class' => 'btn btn-sm btn-outline-warning')) !!}</td>
			@else
				<td> - </td>
			@endif
			<td>{!! Form::button('<div class="fas fa-edit"></div> Editar', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-sm btn-outline-success')) !!}</td>
            <td>{!! Form::button('<div class="fas fa-trash-alt"></div> Eliminar', array('onclick' => 'modal (\''.URL::route($ruta["delete"], array($value->id, 'SI')).'\', \''.$titulo_eliminar.'\', this);', 'class' => 'btn btn-sm btn-outline-danger')) !!}</td>
		</tr>
		<?php
		$contador = $contador + 1;
		?>
		@endforeach
	</tbody>
	<tfoot>
		<tr>
			<th></th>
			@foreach($cabecera as $key => $value)
				<th @if( (int)$value['numero'] > 1) colspan="{{ $value['numero'] }}" @endif>{!! $value['valor'] !!}</th>
			@endforeach
		</tr>
	</tfoot>
</table>
{!! $paginacion !!}
<script type="text/javascript">
// Validar ingreso en grupo: checkbox por reserva + "seleccionar todos" de la página
(function () {
	function seleccionados() {
		return $('#listadoReserva .chkReserva:checked').map(function () { return this.value; }).get();
	}
	function actualizarBoton() {
		var ids = seleccionados();
		$('#cantSeleccionados').text(ids.length);
		$('#btnValidarGrupo').prop('disabled', ids.length === 0);
		var total = $('#listadoReserva .chkReserva').length;
		$('#chkTodos').prop('checked', total > 0 && ids.length === total);
	}
	$('#chkTodos').on('change', function () {
		$('#listadoReserva .chkReserva').prop('checked', this.checked);
		actualizarBoton();
	});
	$('#listadoReserva .chkReserva').on('change', actualizarBoton);
	$('#btnValidarGrupo').on('click', function () {
		var ids = seleccionados();
		if (!ids.length) return;
		modal($(this).data('url') + '?listar=SI&ids=' + ids.join(','), 'Validar ingreso en grupo', this);
	});
})();
</script>
@endif