<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($modelo, $formData) !!}
{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
{{-- $mensajeBloqueo (opcional): motivo por el que no se puede eliminar; se muestra sin botón Eliminar --}}
@if(!empty($mensajeBloqueo))
	<div class="callout callout-warning"><p class="text-warning mb-0">{{ $mensajeBloqueo }}</p></div>
@else
	{!!'<div class="callout callout-danger"><p class="text-danger">¿Esta seguro de eliminar el registro?</p></div>' !!}
@endif
<div class="form-group">
	<div class="col-lg-12 col-md-12 col-sm-12 text-right">
		@if(empty($mensajeBloqueo))
		{!! Form::button('<i class="fa fa-check "></i> '.$boton, array('class' => 'btn btn-danger btn-sm', 'id' => 'btnGuardar', 'onclick' => 'guardar(\''.$entidad.'\', this)')) !!}
		@endif
		{!! Form::button('<i class="fa fa-undo "></i> Cancelar', array('class' => 'btn btn-default btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal((contadorModal - 1));')) !!}
	</div>
</div>
{!! Form::close() !!}
<script type="text/javascript">
	$(document).ready(function() {
		init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
		configurarAnchoModal('350');
	}); 

</script>