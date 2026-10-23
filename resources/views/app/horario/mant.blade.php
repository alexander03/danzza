<?php
// Días de la semana en que se dicta el horario (1=Lunes ... 7=Domingo), guardados como "1,5,6"
$nombresDias = array(1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo');
$diasMarcados = ($horario && $horario->dias != '') ? array_map('intval', explode(',', $horario->dias)) : array();
// Rango de fechas en que el horario está vigente (solo se puede reservar dentro de él)
$vigenciaDesde = ($horario && $horario->fechainicio) ? \Carbon\Carbon::parse($horario->fechainicio)->format('Y-m-d') : null;
$vigenciaHasta = ($horario && $horario->fechafin) ? \Carbon\Carbon::parse($horario->fechafin)->format('Y-m-d') : null;
// Fechas de reservas activas desde hoy (solo al modificar), para avisar si el nuevo rango las deja fuera
$fechasReservadas = isset($fechasReservadas) ? $fechasReservadas : array();
?>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($horario, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	<div class="row">
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('sucursal_id', 'Sede:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::select('sucursal_id',$cboSucursal, null, array('class' => 'form-control input-xs', 'id' => 'sucursal_id')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('clase_id', 'Clase:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::select('clase_id',$cboClase, null, array('class' => 'form-control input-xs', 'id' => 'clase_id')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-6" hidden>
			<div class="form-group">
				{!! Form::label('nombre', 'Nombre:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::text('nombre', null, array('class' => 'form-control input-xs', 'id' => 'nombre', 'placeholder' => 'Ingrese nombre')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('horainicio', 'Hora Inicio:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::time('horainicio', null, array('class' => 'form-control input-xs', 'id' => 'horainicio')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('horafin', 'Hora Fin:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::time('horafin', null, array('class' => 'form-control input-xs', 'id' => 'horafin')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-12 col-lg-12">
			{!! Form::label('instructor_id', 'Instructor:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
			<div class="col-lg-12 col-md-12 col-sm-12">
				{!! Form::select('instructor_id',$cboInstructor, null, array('class' => 'form-control input-xs', 'id' => 'instructor_id')) !!}
			</div>
		</div>
		<div class="col-md-6 col-lg-6" hidden>
			<div class="form-group">
				{!! Form::label('aforo', 'Aforo:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::number('aforo', null, array('class' => 'form-control input-xs', 'id' => 'aforo', 'placeholder' => 'Ingrese aforo')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('situacion', 'Estado:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::select('situacion',['DISPONIBLE'=>'DISPONIBLE','PROXIMAMENTE'=>'PROXIMAMENTE','NO DISPONIBLE'=>'NO DISPONIBLE'], null, array('class' => 'form-control input-xs', 'id' => 'situacion')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('fechainicio', 'Vigente desde:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::date('fechainicio', $vigenciaDesde, array('class' => 'form-control input-xs', 'id' => 'fechainicio')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('fechafin', 'Vigente hasta:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::date('fechafin', $vigenciaHasta, array('class' => 'form-control input-xs', 'id' => 'fechafin')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-12 col-lg-12">
			<div class="form-group">
				<label class="col-lg-12 col-md-12 col-sm-12 control-label">Días disponibles:</label>
				<div class="col-lg-12 col-md-12 col-sm-12">
					@foreach($nombresDias as $numero => $nombreDia)
						<div class="form-check form-check-inline">
							<input type="checkbox" class="form-check-input chkDia" name="dias[]" id="dia{{ $numero }}" value="{{ $numero }}" {{ in_array($numero, $diasMarcados) ? 'checked' : '' }}>
							<label class="form-check-label" for="dia{{ $numero }}">{{ $nombreDia }}</label>
						</div>
					@endforeach
					<div>
						<a href="javascript:void(0)" onclick="marcarDias(true);">Marcar todos</a> ·
						<a href="javascript:void(0)" onclick="marcarDias(false);">Quitar todos</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="form-group">
		<div class="col-lg-12 col-md-12 col-sm-12 text-right">
			{!! Form::button('<i class="fa fa-check "></i> '.$boton, array('class' => 'btn btn-primary btn-sm', 'id' => 'btnGuardar', 'onclick' => 'guardarHorario(\''.$entidad.'\', this)')) !!}
		{!! Form::button('<i class="fa fa-undo "></i> Cancelar', array('class' => 'btn btn-default btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
	</div>
	</div>
{!! Form::close() !!}
<script type="text/javascript">
$(document).ready(function() {
	configurarAnchoModal('600');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="dni"]').inputmask("99999999");
    //$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="ruc"]').inputmask("99999999999");
});

function marcarDias(marcar){
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} .chkDia').prop('checked', marcar);
}

// Fechas (Y-m-d) de reservas activas desde hoy en este horario
var fechasReservadas = {!! json_encode(array_values($fechasReservadas)) !!};

// Si el nuevo rango deja reservas fuera, se avisa; las reservas se mantienen
function guardarHorario(entidad, btn){
	var form = $(IDFORMMANTENIMIENTO + '{!! $entidad !!}');
	var desde = form.find('#fechainicio').val();
	var hasta = form.find('#fechafin').val();
	if (desde && hasta && fechasReservadas.length) {
		var fuera = fechasReservadas.filter(function (f) { return f < desde || f > hasta; }).length;
		if (fuera > 0 && !confirm('Hay ' + fuera + (fuera === 1 ? ' reserva' : ' reservas') + ' de clientes fuera del nuevo rango (' + desde + ' al ' + hasta + '). Se mantendrán, pero no se podrán hacer nuevas reservas fuera del rango. ¿Deseas guardar?')) {
			return false;
		}
	}
	guardar(entidad, btn);
}

</script>