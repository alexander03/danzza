<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($plan, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	<div class="row">
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('nombre', 'Nombre:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::text('nombre', null, array('class' => 'form-control input-xs', 'id' => 'nombre', 'placeholder' => 'Ingrese nombre')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('descripcion', 'Descripcion:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::textarea('descripcion', null, array('class' => 'form-control input-xs', 'id' => 'descripcion', 'placeholder' => 'Ingrese descripcion','rows'=>'3')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('recomendado', 'Recomendado:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::textarea('recomendado', null, array('class' => 'form-control input-xs', 'id' => 'recomendado', 'placeholder' => 'Ingrese recomendado','rows'=>'3')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('clase_id', 'Clase:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::select('clase_id', $cboClase, null, array('class' => 'form-control input-xs', 'id' => 'clase_id')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('creditos', 'Nro. Clases:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::number('creditos', null, array('class' => 'form-control input-xs', 'id' => 'creditos', 'placeholder' => 'Ingrese clases')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('precio', 'Precio:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::number('precio', null, array('class' => 'form-control input-xs', 'id' => 'precio', 'placeholder' => 'Ingrese precio','step'=>'0.01')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('preciopromocion', 'Precio Promoción:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::number('preciopromocion', null, array('class' => 'form-control input-xs', 'id' => 'preciopromocion', 'placeholder' => 'Ingrese precio promoción','step'=>'0.01')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('vigencia', 'Vigencia:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::number('vigencia', null, array('class' => 'form-control input-xs', 'id' => 'vigencia', 'placeholder' => 'Ingrese vigencia')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('creditosadicionales', 'Creditos Adicionales:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::number('creditosadicionales', null, array('class' => 'form-control input-xs', 'id' => 'creditosadicionales', 'placeholder' => 'Ingrese creditos adcionales')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('beneficios', 'Beneficios:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::textarea('beneficios', null, array('class' => 'form-control input-xs', 'id' => 'beneficios', 'placeholder' => 'Ingrese beneficios','rows'=>'3')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('reservaant', 'Reserva Ant.:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::number('reservaant', null, array('class' => 'form-control input-xs', 'id' => 'reservaant', 'placeholder' => 'Ingrese reserva ant.','step'=>'1')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('orden', 'Orden:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::number('orden', null, array('class' => 'form-control input-xs', 'id' => 'orden', 'placeholder' => 'Ingrese orden','step'=>'1')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('situacion', 'Situación:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::select('situacion', $cboSituacion, null, array('class' => 'form-control input-xs', 'id' => 'situacion')) !!}
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
<script type="text/javascript">
$(document).ready(function() {
	configurarAnchoModal('600');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
}); 
</script>