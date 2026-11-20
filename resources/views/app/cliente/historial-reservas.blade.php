<?php
// Tabla de reservas del historial del cliente. Variables: $lista, $estado, $fecha (de historial.blade.php)
?>
@if(count($lista) == 0)
	<p class="text-muted mb-0">Sin reservas en este periodo.</p>
@else
	<table class="table table-sm table-striped text-center mb-0">
		<thead><tr><th>Reservado el</th><th>Clase</th><th>Fecha de la clase</th><th>Créditos</th><th>Estado</th></tr></thead>
		<tbody>
		@foreach($lista as $r)
			<?php list($texto, $color) = $estado($r); ?>
			<tr>
				<td>{{ $fecha($r->created_at, true) }}</td>
				<td>{{ $r->clase_nombre ?? '-' }}</td>
				<td>{{ $fecha($r->fecha) }} {{ $r->horainicio ? substr($r->horainicio, 0, 5) : '' }}</td>
				<td>{{ (int) $r->creditos }}</td>
				<td><span class="badge badge-{{ $color }}">{{ $texto }}</span></td>
			</tr>
		@endforeach
		</tbody>
	</table>
@endif
