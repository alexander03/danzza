<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($instructor, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	<div class="row">
		<div class="col-md-6 col-lg-6">
			<div class="form-group pb-1">
				{!! Form::label('documento', 'DNI:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
        		<div class="col-lg-12 col-md-12 col-sm-12 input-group pl-0">
                        <div class="col-lg-10 col-sm-10 col-md-10 pr-0">
							{!! Form::text('documento', null, array('class' => 'form-control input-xs', 'id' => 'documento')) !!}
                        </div>
                        <div class="col-lg-1 col-sm-1 col-md-1 pl-1">
                            <span class="input-group-append">
                                {!! Form::button('<i class="fa fa-search "></i>', array('class' => 'btn btn-primary', 'onclick' => 'buscarDNI();', 'title' => 'Buscar DNI')) !!}
                            </span>
                        </div>
        		</div>
        	</div>
			
		</div>
	</div>
	<div class="row">
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('apellidopaterno', 'Apellido Paterno:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::text('apellidopaterno', null, array('class' => 'form-control input-xs', 'id' => 'apellidopaterno', 'placeholder' => 'Ingrese apellido paterno')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('apellidomaterno', 'Apellido Materno:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::text('apellidomaterno', null, array('class' => 'form-control input-xs', 'id' => 'apellidomaterno', 'placeholder' => 'Ingrese apellido materno')) !!}
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-6 col-lg-6">
			{!! Form::label('nombres', 'Nombres:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
			<div class="col-lg-12 col-md-12 col-sm-12">
				{!! Form::text('nombres', null, array('class' => 'form-control input-xs', 'id' => 'nombres', 'placeholder' => 'Ingrese nombres')) !!}
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			{!! Form::label('sobrenombre', 'Sobrenombre:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
			<div class="col-lg-12 col-md-12 col-sm-12">
				{!! Form::text('sobrenombre', null, array('class' => 'form-control input-xs', 'id' => 'sobrenombre', 'placeholder' => 'Ingrese sobrenombre')) !!}
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			{!! Form::label('direccion', 'Direccion:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
			<div class="col-lg-12 col-md-12 col-sm-12">
				{!! Form::text('direccion', null, array('class' => 'form-control input-xs', 'id' => 'direccion', 'placeholder' => 'Ingrese direccion')) !!}
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('fechanacimiento', 'Fecha Nac.:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::date('fechanacimiento', null, array('class' => 'form-control input-xs', 'id' => 'fechanacimiento', 'placeholder' => 'Ingrese fecha de nacimiento')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('telefono', 'Celular:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::text('telefono', null, array('class' => 'form-control input-xs', 'id' => 'telefono', 'placeholder' => 'Ingrese telefono')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('correo', 'Correo:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::text('correo', null, array('class' => 'form-control input-xs', 'id' => 'correo', 'placeholder' => 'Ingrese correo')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('modalidad', 'Modalidad:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::text('modalidad', null, array('class' => 'form-control input-xs', 'id' => 'modalidad', 'placeholder' => 'Ingrese modalidad')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('especialidad', 'Especialidad:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::text('especialidad', null, array('class' => 'form-control input-xs', 'id' => 'especialidad')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('tarifa', 'Tarifa:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::number('tarifa', null, array('class' => 'form-control input-xs', 'id' => 'tarifa')) !!}
				</div>
			</div>
		</div>
	</div>
	{{-- Usuario del instructor para ingresar al panel (tipo de usuario 6) --}}
	<div class="row">
		<div class="col-md-12">
			<h6 class="mt-2 mb-2 pl-2 font-weight-bold"><i class="fa fa-user-lock"></i> Acceso al sistema</h6>
		</div>
		<div class="col-md-12 col-lg-12">
			<div class="form-group">
				{!! Form::label('login', 'Usuario:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::text('login', $usuario ? $usuario->login : null, array('class' => 'form-control input-xs', 'id' => 'login', 'placeholder' => 'Ingrese usuario', 'autocomplete' => 'off')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('password', 'Contraseña:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::password('password', array('class' => 'form-control input-xs', 'id' => 'password', 'autocomplete' => 'new-password', 'placeholder' => $usuario ? 'Dejar en blanco para no cambiar' : 'Ingrese contraseña')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			<div class="form-group">
				{!! Form::label('password_confirmation', 'Confirmar contraseña:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::password('password_confirmation', array('class' => 'form-control input-xs', 'id' => 'password_confirmation', 'autocomplete' => 'new-password', 'placeholder' => 'Repita la contraseña')) !!}
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
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="dni"]').inputmask("99999999");
    //$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="ruc"]').inputmask("99999999999");
}); 

function buscarDNI(){
	var reg = new RegExp('^[0-9]+$');
    if($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="documento"]').val() == ""){
        toastr.warning("Debe ingresar un DNI.", 'Error:');
    }else if(!reg.test($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="documento"]').val())){
        toastr.warning("El DNI es incorrecto.", 'Error:');
	}else{
        $.ajax({
            type: "POST",
            url: "persona/buscarDNI",
            data: "dni="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="documento"]').val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
            success: function(a) {
                datos=JSON.parse(a);
				if(datos.code != 0){
        			toastr.warning(datos.mensaje, 'Error:');
				}else{
					$("#apellidopaterno").val(datos.apepat);
					$("#apellidomaterno").val(datos.apemat);
					$("#nombres").val(datos.nombres);
				}
            }
        });
    }
}

function buscarRUC(){
	var reg = new RegExp('^[0-9]+$');
    if($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="ruc"]').val() == ""){
        toastr.warning("Debe ingresar un RUC.", 'Error:');
    }else if(!reg.test($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="ruc"]').val())){
        toastr.warning("El RUC ingresado es incorrecto.", 'Error:');
	}else{
        $.ajax({
            type: "POST",
            url: "persona/buscarRUC",
            data: "ruc="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="ruc"]').val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
            success: function(a) {
                datos=JSON.parse(a);
				if(datos.length == 0){
        			toastr.warning("El RUC ingresado es incorrecto.", 'Error:');
				}else{
					$("#nombres").val(datos.RazonSocial);
					$("#direccion").val(datos.Direccion);
				}
            }
        });
    }
}
</script>