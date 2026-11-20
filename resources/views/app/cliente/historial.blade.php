<?php
// Variables que envía ClienteController@historial:
//   $cliente, $pagos (todos los pagos de plan), $periodos (por pago validado, el más reciente primero:
//   pago, plan, desde, hasta, exacto, inicial, usados, ajustes, quedan, reservas), $sinPlan (reservas antes del
//   primer plan), $movimientos (registro de créditos)
use Carbon\Carbon;

$fecha = function ($valor, $conHora = false) {
    return $valor ? Carbon::parse($valor)->format($conHora ? 'd/m/Y H:i' : 'd/m/Y') : '-';
};
// Estado de una reserva para el cliente
$estado = function ($r) use ($fecha) {
    if ($r->deleted_at) {
        return array('Cancelada el ' . $fecha($r->deleted_at) . ' (créditos devueltos)', 'secondary');
    }
    if ($r->asistencia == 'INGRESO VALIDADO') {
        return array('Asistió', 'success');
    }
    if (Carbon::parse(Carbon::parse($r->fecha)->format('Y-m-d') . ' ' . $r->horainicio)->lt(Carbon::now())) {
        return array('Clase pasada, sin ingreso validado', 'warning');
    }
    return array('Reservada', 'info');
};
$tipos = array('PLAN' => 'Plan', 'RESERVA' => 'Reserva', 'CANCELACION' => 'Devolución', 'CONGELAMIENTO' => 'Congelamiento', 'AJUSTE' => 'Ajuste manual');
?>
<div class="historial-cliente">
	<table class="table table-sm mb-3">
		<tr><th style="width: 160px;">Cliente</th><td>{{ $cliente->apellidopaterno.' '.$cliente->apellidomaterno.' '.$cliente->nombres }} · DNI {{ $cliente->documento }}</td></tr>
		<tr><th>Plan actual</th><td>{{ optional($cliente->plan)->nombre ?? '-' }}</td></tr>
		<tr><th>Créditos disponibles</th><td><b>{{ (int) $cliente->creditos }}</b></td></tr>
	</table>

	<h6 class="font-weight-bold">Planes comprados</h6>
	@if(count($pagos) == 0)
		<p class="text-muted">No tiene pagos de plan.</p>
	@else
		<table class="table table-sm table-striped text-center">
			<thead><tr><th>Registrado</th><th>Plan</th><th>Fecha de pago</th><th>Monto</th><th>Inicio</th><th>Vence</th><th>Situación</th></tr></thead>
			<tbody>
			@foreach($pagos->sortByDesc('id') as $p)
				<tr>
					<td>{{ $fecha($p->created_at) }}</td>
					<td>{{ optional(\App\Plan::withTrashed()->find($p->plan_id))->nombre ?? '-' }}</td>
					<td>{{ $fecha($p->fechapago) }}</td>
					<td>S/ {{ number_format($p->monto, 2) }}</td>
					<td>{{ $fecha($p->fechainicio) }}</td>
					<td>{{ $fecha($p->fechavencimiento) }}</td>
					<td>{{ $p->situacion }}</td>
				</tr>
			@endforeach
			</tbody>
		</table>
	@endif

	@foreach($periodos as $i => $per)
		<div class="card mb-3">
			<div class="card-header py-2">
				<b>{{ optional($per['plan'])->nombre ?? 'Plan' }}</b>
				· desde {{ $fecha($per['desde'], $per['exacto']) }}
				@if($per['hasta']) hasta {{ $fecha($per['hasta'], true) }} @else <span class="badge badge-primary">Actual</span> @endif
			</div>
			<div class="card-body py-2">
				<p class="mb-2">
					Créditos al iniciar: <b>{{ $per['inicial'] }}</b>@if(!$per['exacto'])<small class="text-muted"> (estimado: créditos del plan)</small>@endif
					· Usados: <b>{{ $per['usados'] }}</b>
					@if($per['ajustes'] != 0) · Ajustes manuales: <b>{{ $per['ajustes'] > 0 ? '+' : '' }}{{ $per['ajustes'] }}</b> @endif
					· Quedan (calculado): <b>{{ $per['quedan'] }}</b>
				</p>
				@if($i == 0)
					@if($per['quedan'] == (int) $cliente->creditos)
						<div class="alert alert-success py-1 mb-2">El saldo actual ({{ (int) $cliente->creditos }}) coincide con lo calculado.</div>
					@else
						<div class="alert alert-warning py-1 mb-2">
							El saldo actual es {{ (int) $cliente->creditos }} y lo calculado es {{ $per['quedan'] }}.
							La diferencia viene de cambios hechos antes de que existiera el registro de movimientos
							o de reservas de un periodo anterior. Revisa los movimientos de abajo.
						</div>
					@endif
				@endif
				@include('app.cliente.historial-reservas', array('lista' => $per['reservas']))
			</div>
		</div>
	@endforeach

	@if(count($sinPlan))
		<div class="card mb-3">
			<div class="card-header py-2"><b>Reservas antes del primer plan validado</b></div>
			<div class="card-body py-2">
				@include('app.cliente.historial-reservas', array('lista' => $sinPlan))
			</div>
		</div>
	@endif

	<h6 class="font-weight-bold">Movimientos de créditos registrados</h6>
	@if(count($movimientos) == 0)
		<p class="text-muted">Todavía no hay movimientos registrados para este cliente (el registro guarda cada cambio de créditos desde que se instaló).</p>
	@else
		<table class="table table-sm table-striped text-center">
			<thead><tr><th>Fecha</th><th>Tipo</th><th>Detalle</th><th>Cantidad</th><th>Saldo</th><th>Usuario</th></tr></thead>
			<tbody>
			@foreach($movimientos->sortByDesc('id') as $m)
				<tr>
					<td>{{ $fecha($m->created_at, true) }}</td>
					<td>{{ $tipos[$m->tipo] ?? $m->tipo }}</td>
					<td class="text-left">{{ $m->detalle }}</td>
					<td class="{{ $m->cantidad < 0 ? 'text-danger' : 'text-success' }}">{{ $m->cantidad > 0 ? '+' : '' }}{{ $m->cantidad }}</td>
					<td>{{ $m->saldoanterior }} → <b>{{ $m->saldonuevo }}</b></td>
					<td>{{ optional($m->usuario)->login ?? '-' }}</td>
				</tr>
			@endforeach
			</tbody>
		</table>
	@endif

	<div class="text-right">
		{!! Form::button('<i class="fa fa-undo"></i> Cerrar', array('class' => 'btn btn-default btn-sm', 'onclick' => 'cerrarModal();')) !!}
	</div>
</div>
<script type="text/javascript">
$(document).ready(function() {
	configurarAnchoModal('950');
});
</script>
