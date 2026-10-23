<?php 
$icono = 'fa fa-bank';
if ($categoriaopcionmenu !== NULL) {
	$icono = $categoriaopcionmenu->icon;
}
?>

<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($categoriaopcionmenu, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	<div class="form-group">
		{!! Form::label('menuoptioncategory_id', 'Categoria:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
		<div class="col-lg-12 col-md-12 col-sm-12">
			{!! Form::select('menuoptioncategory_id', $cboCategoria, null, array('class' => 'form-control input-xs', 'id' => 'menuoptioncategory_id')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('name', 'Nombre:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
		<div class="col-lg-12 col-md-12 col-sm-12">
			{!! Form::text('name', null, array('class' => 'form-control input-xs', 'id' => 'name', 'placeholder' => 'Ingrese nombre')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('order', 'Orden:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
		<div class="col-lg-12 col-md-12 col-sm-12">
			{!! Form::text('order', null, array('class' => 'form-control input-xs', 'id' => 'order', 'placeholder' => 'Ingrese orden')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('icon', 'Icono:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
		<div class="col-lg-12 col-md-12 col-sm-12">
			{!! Form::text('icon', $icono, array('class' => 'form-control input-xs', 'id' => 'icon', 'placeholder' => 'Ingrese icono')) !!}
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
	configurarAnchoModal('350');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
}); 
</script>