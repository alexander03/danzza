<?php
// Reserva desde el panel. El código se genera al registrar; el estado, la asistencia y la
// validación del instructor no se editan aquí (solo con sus botones del listado).
// Horarios y lugares se cargan por AJAX según sede/clase/instructor y fecha.
$fechaReserva = ($reserva && $reserva->fecha) ? \Carbon\Carbon::parse($reserva->fecha)->format('Y-m-d') : null;
?>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($reserva, $formData) !!}
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	<div class="row">
		@if($reserva)
			<div class="col-md-6 col-lg-6">
				<div class="form-group">
					<label class="col-lg-12 col-md-12 col-sm-12 control-label">Código:</label>
					<div class="col-lg-12 col-md-12 col-sm-12">
						<input type="text" class="form-control input-xs" value="{{ $reserva->codigo }}" readonly>
					</div>
				</div>
			</div>
			@if($reserva->reservagrupo_id && $reserva->grupo)
				<div class="col-md-6 col-lg-6">
					<div class="form-group">
						<label class="col-lg-12 col-md-12 col-sm-12 control-label">Rango:</label>
						<div class="col-lg-12 col-md-12 col-sm-12">
							<span class="badge badge-primary">
								{{ \Carbon\Carbon::parse($reserva->grupo->fechainicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($reserva->grupo->fechafin)->format('d/m/Y') }}
							</span>
							<br><small>Se modifica solo este día del rango.</small>
						</div>
					</div>
				</div>
			@endif
		@endif
		<div class="col-md-3 col-lg-3">
			<div class="form-group">
				{!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::date('fecha', $fechaReserva, array('class' => 'form-control input-xs', 'id' => 'fecha', 'onchange' => 'cargarHorarios()')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-9 col-lg-9">
			<div class="form-group">
				{!! Form::label('nombrecliente', 'Cliente (DNI o Nombres):', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::hidden('cliente_id', null, array('id' => 'cliente_id')) !!}
					{!! Form::text('nombrecliente', null, array('class' => 'form-control input-xs', 'id' => 'nombrecliente', 'placeholder' => 'Escriba y seleccione un cliente')) !!}
					<small class="text-muted">Cada reserva consume 1 crédito del cliente.</small>
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('sucursal_id', 'Sede:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::select('sucursal_id',$cboSucursal, null, array('class' => 'form-control input-xs', 'id' => 'sucursal_id','onchange' => 'cargarHorarios()')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('clase_id', 'Clase:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::select('clase_id',$cboClase, null, array('class' => 'form-control input-xs', 'id' => 'clase_id','onchange' => 'cargarHorarios()')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('instructor_id', 'Instructor:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::select('instructor_id',$cboInstructor, null, array('class' => 'form-control input-xs', 'id' => 'instructor_id','onchange' => 'cargarHorarios()')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('horario_id', 'Horario:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::select('horario_id',$cboHorario, null, array('class' => 'form-control input-xs', 'id' => 'horario_id', 'onchange' => 'cargarLugares()')) !!}
					<small class="text-muted" id="ayudaHorario">Elija la fecha para ver los horarios que se dictan ese día.</small>
				</div>
			</div>
		</div>
		<div class="col-md-12 col-lg-12">
			<div class="form-group">
				<label class="col-lg-12 col-md-12 col-sm-12 control-label">
					Lugar: <span id="lugarElegido" class="text-muted">sin elegir</span>
				</label>
				<div class="col-lg-12 col-md-12 col-sm-12">
					{{-- Mapa de lugares (igual que la vista del cliente al reservar) --}}
					{!! Form::hidden('ubicacion', null, array('id' => 'ubicacion')) !!}
					<div class="plano-admin">
						<div class="capacidad-admin">Capacidad máxima: <strong id="capacidadMapa">-</strong></div>
						<div class="instructor-admin">
							<i class="fas fa-chalkboard-teacher"></i> INSTRUCTOR<span id="instructorMapa"></span>
						</div>
						<div class="lugares-admin" id="mapaLugares">
							<div class="mensaje-mapa">Seleccione la fecha y el horario para ver los lugares.</div>
						</div>
						<div class="leyenda-admin">
							<span class="l-libre">Libre</span>
							<span class="l-ocupado">Ocupado</span>
							<span class="l-elegido">Elegido</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="form-group">
		<div class="col-lg-12 col-md-12 col-sm-12 text-right">
			{!! Form::button('<i class="fa fa-check "></i> '.$boton, array('class' => 'btn btn-primary btn-sm', 'id' => 'btnGuardar', 'onclick' => 'guardar(\''.$entidad.'\', this)')) !!}
		{!! Form::button('<i class="fa fa-undo "></i> Cancelar', array('class' => 'btn btn-default btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
	</div>
	</div>
{!! Form::close() !!}
<style type="text/css">
	/* Mapa de lugares: mismos colores que la vista del cliente (libre / ocupado / elegido) */
	.plano-admin {
		padding: 14px;
		border: 1px solid #dee2e6;
		border-radius: 12px;
		background: #f8f9fa;
	}
	.capacidad-admin {
		margin-bottom: 10px;
		color: #6c757d;
		font-size: 13px;
	}
	.instructor-admin {
		margin-bottom: 14px;
		padding: 8px;
		border: 1px dashed #a9c900;
		border-radius: 10px;
		background: rgba(223, 255, 0, .15);
		color: #5f7700;
		font-weight: 800;
		letter-spacing: 2px;
		text-align: center;
	}
	.lugares-admin {
		display: grid;
		grid-template-columns: repeat(6, 1fr);
		gap: 8px;
		max-width: 380px;
		margin: 0 auto;
	}
	.lugares-admin .mensaje-mapa {
		grid-column: 1 / -1;
		color: #6c757d;
		text-align: center;
	}
	.lugar-admin {
		aspect-ratio: 1 / 1;
		border: none;
		border-radius: 8px;
		background: #22c55e;
		color: #fff;
		font-weight: 800;
		cursor: pointer;
		transition: .15s;
	}
	.lugar-admin:hover:not(:disabled) { transform: scale(1.06); }
	.lugar-admin.ocupado {
		background: #ef4444;
		cursor: not-allowed;
		opacity: .85;
	}
	.lugar-admin.elegido {
		background: #facc15;
		color: #070707;
	}
	.leyenda-admin {
		display: flex;
		flex-wrap: wrap;
		justify-content: center;
		gap: 16px;
		margin-top: 12px;
		font-size: 13px;
	}
	.leyenda-admin span::before {
		content: "";
		display: inline-block;
		width: 12px;
		height: 12px;
		margin-right: 5px;
		border-radius: 3px;
		vertical-align: -1px;
	}
	.leyenda-admin .l-libre::before { background: #22c55e; }
	.leyenda-admin .l-ocupado::before { background: #ef4444; }
	.leyenda-admin .l-elegido::before { background: #facc15; }
</style>
<script type="text/javascript">
// Al modificar: reserva actual (no cuenta como ocupada) y valores a seleccionar cuando cargan los combos
var reservaActual = {!! json_encode($reserva ? array(
	'id'         => $reserva->id,
	'horario_id' => $reserva->horario_id,
	'ubicacion'  => (int) $reserva->ubicacion,
) : null) !!};

$(document).ready(function() {
	configurarAnchoModal('850');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');

	var clientes = new Bloodhound({
			datumTokenizer: function (d) {
				return Bloodhound.tokenizers.whitespace(d.value);
			},
			queryTokenizer: Bloodhound.tokenizers.whitespace,
			remote: {
				url: 'reserva/clienteautocompletar/%QUERY',
				filter: function (clientes) {
					return $.map(clientes, function (movie) {
						return {
							value: movie.value,
							id: movie.id
						};
					});
				}
			}
		});
		clientes.initialize();
		$('#nombrecliente').typeahead(null,{
			displayKey: 'value',
			source: clientes.ttAdapter()
		}).on('typeahead:selected', function (object, datum) {
			$('#cliente_id').val(datum.id);
		});
		// Si se escribe otro nombre sin elegirlo de la lista, se limpia el cliente
		$('#nombrecliente').on('input', function () {
			$('#cliente_id').val('');
		});

	@if(!is_null($reserva))
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="sucursal_id"]').val({!! (int) $reserva->sucursal_id !!});
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="clase_id"]').val({!! (int) $reserva->horario->clase_id !!});
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="instructor_id"]').val({!! (int) $reserva->horario->instructor_id !!});
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="nombrecliente"]').val({!! json_encode($reserva->cliente->apellidopaterno.' '.$reserva->cliente->apellidomaterno.' '.$reserva->cliente->nombres) !!});
	@endif
	cargarHorarios();
});

function campo(id) {
	return $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="' + id + '"]');
}

// Horarios DISPONIBLES de la sede/clase/instructor que se dictan en la fecha elegida
function cargarHorarios(){
	var fecha = campo('fecha').val();
	var $select = campo('horario_id');
	var seleccionado = $select.val() || (reservaActual ? reservaActual.horario_id : '');
	$select.empty().append('<option value="">Seleccione un horario</option>');
	if (!fecha) {
		$('#ayudaHorario').text('Elija la fecha para ver los horarios que se dictan ese día.');
		cargarLugares();
		return;
	}
	$.ajax({
		type: "GET",
		url: "reserva/cargarhorarios",
		data: {
			sucursal_id: campo('sucursal_id').val(),
			clase_id: campo('clase_id').val(),
			instructor_id: campo('instructor_id').val(),
			fecha: fecha,
			reserva_id: reservaActual ? reservaActual.id : ''
		},
		dataType: 'json',
		success: function (horarios) {
			$('#ayudaHorario').text(horarios.length ? '' : 'No hay horarios que se dicten ese día con estos filtros.');
			$.each(horarios, function (index, h) {
				var lleno = h.capacidad > 0 && h.libres <= 0;
				$select.append($('<option>', {
					value: h.id,
					text: h.horainicio + ' - ' + h.horafin + ' · ' + h.libres + '/' + h.capacidad + ' libres' + (lleno ? ' (LLENO)' : ''),
					disabled: lleno
				}));
			});
			if (seleccionado && $select.find('option[value="' + seleccionado + '"]:not(:disabled)').length) {
				$select.val(seleccionado);
			}
			cargarLugares();
		},
		error: function (xhr, status, error) {
			console.error('Error al cargar horarios:', error);
		}
	});
}

// Elige un lugar en el mapa (o lo limpia con null)
function elegirLugar(n){
	campo('ubicacion').val(n || '');
	$('#mapaLugares .lugar-admin').removeClass('elegido');
	if (n) {
		$('#mapaLugares .lugar-admin[data-lugar="' + n + '"]').addClass('elegido');
		$('#lugarElegido').removeClass('text-muted').text('N.° ' + n);
	} else {
		$('#lugarElegido').addClass('text-muted').text('sin elegir');
	}
}

// Mapa de lugares del horario en la fecha elegida (1..capacidad de la clase), como en la vista del cliente
function cargarLugares(){
	var $mapa = $('#mapaLugares');
	var horario = campo('horario_id').val();
	var fecha = campo('fecha').val();
	var seleccionado = campo('ubicacion').val() || (reservaActual ? reservaActual.ubicacion : '');
	elegirLugar(null);
	$('#capacidadMapa').text('-');
	$('#instructorMapa').text('');
	if (!horario || !fecha) {
		$mapa.html('<div class="mensaje-mapa">Seleccione la fecha y el horario para ver los lugares.</div>');
		return;
	}
	$mapa.html('<div class="mensaje-mapa">Cargando lugares...</div>');
	$.ajax({
		type: "GET",
		url: "reserva/lugareslibres",
		data: { horario_id: horario, fecha: fecha, reserva_id: reservaActual ? reservaActual.id : '' },
		dataType: 'json',
		success: function (r) {
			var ocupados = (r.ocupados || []).map(Number);
			$('#capacidadMapa').text(r.capacidad);
			$('#instructorMapa').text(r.instructor ? ': ' + r.instructor : '');
			$mapa.empty();
			if (!r.capacidad) {
				$mapa.html('<div class="mensaje-mapa">La clase no tiene capacidad registrada.</div>');
				return;
			}
			for (var n = 1; n <= r.capacidad; n++) {
				var ocupado = ocupados.indexOf(n) !== -1;
				$('<button type="button" class="lugar-admin"></button>')
					.text(n)
					.attr('data-lugar', n)
					.toggleClass('ocupado', ocupado)
					.prop('disabled', ocupado)
					.appendTo($mapa);
			}
			if (seleccionado && ocupados.indexOf(Number(seleccionado)) === -1 && Number(seleccionado) <= r.capacidad) {
				elegirLugar(Number(seleccionado));
			}
		},
		error: function (xhr, status, error) {
			$mapa.html('<div class="mensaje-mapa">No se pudieron cargar los lugares.</div>');
			console.error('Error al cargar lugares:', error);
		}
	});
}

$(document).on('click', '#mapaLugares .lugar-admin:not(.ocupado)', function () {
	elegirLugar(Number($(this).data('lugar')));
});
</script>
