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
		$abrevDias = array(1 => 'Lun', 2 => 'Mar', 3 => 'Mié', 4 => 'Jue', 5 => 'Vie', 6 => 'Sáb', 7 => 'Dom');
		?>
		@foreach ($lista as $key => $value)
		<tr>
			<td>{{ $contador }}</td>
			<td>{{ $value->sucursal->nombre }}</td>
			<td>{{ $value->clase->nombre }}</td>
			<!--td>{{ $value->nombre }}</td-->
			<td>{{ $value->horainicio }}</td>
            <td>{{ $value->horafin }}</td>
			<td>
				<?php
				$diasHorario = $value->dias != '' ? array_map('intval', explode(',', $value->dias)) : array();
				?>
				@if(count($diasHorario) == 7)
					Todos los días
				@elseif(count($diasHorario))
					{{ implode(' · ', array_map(function ($d) use ($abrevDias) { return isset($abrevDias[$d]) ? $abrevDias[$d] : $d; }, $diasHorario)) }}
				@else
					<span class="text-danger">Sin días</span>
				@endif
			</td>
			<td>
				@if($value->fechainicio && $value->fechafin)
					<?php $vencido = \Carbon\Carbon::parse($value->fechafin)->lt(\Carbon\Carbon::today()); ?>
					<span class="{{ $vencido ? 'text-danger' : '' }}" title="{{ $vencido ? 'Vigencia terminada' : '' }}">
						{{ \Carbon\Carbon::parse($value->fechainicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($value->fechafin)->format('d/m/Y') }}
					</span>
				@else
					<span class="text-danger">Sin vigencia</span>
				@endif
			</td>
			<td>{{ $value->instructor->sobrenombre ?: $value->instructor->nombres }}</td>
			<!--td>{{ $value->ocupacion.'/'.$value->aforo }}</td-->
			<td>{{ $value->situacion }}</td>
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