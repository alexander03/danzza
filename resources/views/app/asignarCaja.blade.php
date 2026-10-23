<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($caja, $formData) !!}	
	
	<div class="form-group">
		{!! Form::label('lblcaja_id', 'Caja', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
		<div class="col-lg-12 col-md-12 col-sm-12">
			{!! Form::select('caja_id',$cboCajas, null, array('class' => 'form-control input-xs', 'id' => 'caja_id')) !!}
		</div>
	</div>
    <div class="form-group">
		<div class="col-lg-12 col-md-12 col-sm-12 text-right">
			{!! Form::button('<i class="fa fa-check "></i> '.$boton, array('class' => 'btn btn-primary btn-sm', 'id' => 'btnGuardar', 'onclick' => 'asignar(\''.$entidad.'\', this)')) !!}
		</div>
	</div>
{!! Form::close() !!}
