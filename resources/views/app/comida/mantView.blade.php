<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($comida, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	<div class="row ">
		<div class='col-lg-2 col-md-2 col-sm-2'>
			<div class="form-group">
				{!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::text('fecha', $fecha, array('class' => 'form-control input-xs', 'id' => 'persona', 'placeholder' => 'fecha','readonly'=>'true')) !!}
				</div>
			</div>
		</div>
		<div class='col-lg-2 col-md-2 col-sm-2'>
			<div class="form-group">
				{!! Form::label('dni', 'DNI / OTROS:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::text('dni', $dni, array('class' => 'form-control input-xs', 'id' => 'dni', 'placeholder' => 'tipo','readonly'=>'true')) !!}
				</div>
			</div>
		</div>
		<div class='col-lg-6 col-md-6 col-sm-6'>
			<div class="form-group">
				{!! Form::label('persona', 'Persona:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::text('persona', $persona, array('class' => 'form-control input-xs', 'id' => 'persona', 'placeholder' => 'persona','readonly'=>'true')) !!}
				</div>
			</div>
		</div>
		<div class='col-lg-2 col-md-2 col-sm-2'>
			<div class="form-group">
				{!! Form::label('tipo', 'Tipo:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::text('tipo', $tipo, array('class' => 'form-control input-xs', 'id' => 'tipo', 'placeholder' => 'tipo','readonly'=>'true')) !!}
				</div>
			</div>
		</div>
	</div>
	<div class="row ">
		<div class='col-lg-11 col-md-11 col-sm-11'>
			<div class="form-group">
				{!! Form::label('mensaje', 'Mensaje:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::text('mensaje', $mensaje, array('class' => 'form-control input-xs text-'.$color, 'id' => 'mensaje', 'placeholder' => 'mensaje','disabled'=>'true','style'=>'font-weight: bold;background-color:white !important')) !!}
				</div>
			</div>
		</div>
		@if($esdieta=="SI")
			<?php 
			$id=$comida->id;
			?>
		<div class='col-lg-1 col-md-1 col-sm-1'>
			<span class="input-group-append">
                {!! Form::button('<i class="fa fa-print "></i>', array('class' => 'btn btn-primary', 'onclick' => 'imprimirDieta('.$id.');', 'title' => 'Imprimir Dieta')) !!}
            </span>
		</div>
		@endif
	</div>
{!! Form::close() !!}
<script type="text/javascript">
$(document).ready(function() {
	configurarAnchoModal('400');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
}); 
function imprimirDieta(vid){
	//window.open('comida/verpdf/'+vid,'_blank');
	imprimirComida(vid);
}
</script>