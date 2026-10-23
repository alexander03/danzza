<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($persona, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	<div class="row">
		<div class="col-md-12 col-lg-12">
			<div class="form-group ">
				{!! Form::label('file', 'Importar Excel', array('class' => 'col-lg-5 col-md-5 col-sm-5 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12 ">
					{!! Form::file('file', array('class' => 'form-control input-xs', 'id' => 'file')) !!}
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
	configurarAnchoModal('500');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');

	
});


</script>