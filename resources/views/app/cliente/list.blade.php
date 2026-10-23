@if(count($lista) == 0)
<h3 class="text-warning">No se encontraron resultados.</h3>
@else
{!! $paginacion  !!}
<table id="example1" class="table table-sm text-center table-striped  table-hover">

	<thead>
		<tr>
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
		<tr>
			<td>{{ $contador }}</td>
			<td>{{ $value->documento }}</td>
			<td>{{ $value->apellidopaterno.' '.$value->apellidomaterno.' '.$value->nombres }}</td>
			<td>{{ $value->direccion }}</td>
			<td>{{ $value->fechanacimiento }}</td>
			<td>{{ $value->dniapoderado }}</td>
            <td>{{ $value->celular }}</td>
            <td>{{ $value->correo }}</td>
			<td>{{ optional($value->plan)->nombre ?? '-' }}</td>
			<!--td>{{ $value->clases }}</td-->
			<td>{{ $value->creditos }}</td>
			<td>{{ $value->situacion }}</td>
			<!--td>{{ optional($value->sucursal)->nombre ?? '-' }}</td-->
			<?php
			// Pago de plan en validación (como máximo uno por cliente) y último pago validado
			$pagoPendiente = \App\Planpago::where('cliente_id', '=', $value->id)->where('situacion', '=', 'PENDIENTE')->orderBy('id', 'desc')->first();
			$ultimoValidado = $pagoPendiente ? null : \App\Planpago::where('cliente_id', '=', $value->id)->where('situacion', '=', 'VALIDADO')->orderBy('id', 'desc')->first();
			?>
			<td>
				@if($pagoPendiente)
					<span class="badge badge-info">POR VALIDAR</span>
					<br><small>{{ optional($pagoPendiente->plan)->nombre }} · S/ {{ number_format($pagoPendiente->monto, 2) }}</small>
				@elseif($ultimoValidado)
					<span class="badge badge-success">VALIDADO</span>
					@if($ultimoValidado->fechavencimiento)
						<br><small>Vence {{ \Carbon\Carbon::parse($ultimoValidado->fechavencimiento)->format('d/m/Y') }}</small>
					@endif
				@else
					-
				@endif
			</td>
			@if($pagoPendiente)
				<td>{!! Form::button('<div class="fas fa-money-check-alt"></div> Validar Pago', array('onclick' => 'modal (\''.URL::route($ruta["validarpago"], array($pagoPendiente->id, 'SI')).'\', \''.$titulo_validarpago.'\', this);', 'class' => 'btn btn-sm btn-outline-primary')) !!}</td>
			@elseif(!empty($esAdmin) && isset($asignables[$value->id]))
				{{-- Sin plan, sin créditos o plan vencido: el administrador registra el pago del plan (luego se valida con "Validar Pago") --}}
				<td>
					{!! Form::button('<div class="fas fa-bolt"></div> Asignar Plan', array('onclick' => 'modal (\''.URL::route($ruta["asignarplan"], array($value->id, 'SI')).'\', \''.$titulo_asignarplan.'\', this);', 'class' => 'btn btn-sm btn-outline-warning')) !!}
					<br><small class="text-muted">{{ $asignables[$value->id] }}</small>
				</td>
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
			@foreach($cabecera as $key => $value)
				<th @if( (int)$value['numero'] > 1) colspan="{{ $value['numero'] }}" @endif>{!! $value['valor'] !!}</th>
			@endforeach
		</tr>
	</tfoot>
</table>
{!! $paginacion !!}
@endif