<?php
if(is_null($dieta)){
	$persona = '';
}else{
	$persona = $dieta->persona->apellidopaterno.' '.$dieta->persona->apellidomaterno.' '.$dieta->persona->nombres;
}
?>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($dieta, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	<div class="form-group">
		{!! Form::label('persona', 'Persona:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-12 col-md-12 col-sm-12">
			{!! Form::text('persona', $persona, array('class' => 'form-control input-xs', 'id' => 'persona', 'placeholder' => 'Ingrese persona')) !!}
			{!! Form::hidden('persona_id', null, array('id' => 'persona_id')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('fechainicio', 'Fecha Inicio:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-12 col-md-12 col-sm-12">
			{!! Form::date('fechainicio', null, array('class' => 'form-control input-xs', 'id' => 'fechainicio')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('fechafin', 'Fecha Fin:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-12 col-md-12 col-sm-12">
			{!! Form::date('fechafin', null, array('class' => 'form-control input-xs', 'id' => 'fechafin')) !!}
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

var personas2 = new Bloodhound({
	datumTokenizer: function (d) {
		return Bloodhound.tokenizers.whitespace(d.value);
	},
	queryTokenizer: Bloodhound.tokenizers.whitespace,
	remote: {
		url: 'dieta/personautocompletar/%QUERY',
		filter: function (personas2) {
			return $.map(personas2, function (movie) {
				return {
					value: movie.value,
					id: movie.id,
                    ruc: movie.ruc,
				};
			});
		}
	}
});
personas2.initialize();
$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="persona"]').typeahead(null,{
	displayKey: 'value',
	source: personas2.ttAdapter()
}).on('typeahead:selected', function (object, datum) {
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="persona"]').val(datum.value);
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="persona_id"]').val(datum.id);
});
</script>