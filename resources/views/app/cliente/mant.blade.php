<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($cliente, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	<div class="row">
		<div class="col-md-3 col-lg-3">
			<div class="form-group pb-1">
				{!! Form::label('documento', 'DNI:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
        		<div class="col-lg-12 col-md-12 col-sm-12 input-group pl-0">
                    <div class="input-group">
						{!! Form::text('documento', null, ['class' => 'form-control input-xs', 'id' => 'documento']) !!}
						<div class="input-group-append">
							{!! Form::button('<i class="fa fa-search"></i>', ['class' => 'btn btn-primary', 'onclick' => 'buscarDNI();', 'title' => 'Buscar DNI']) !!}
						</div>
					</div>
        		</div>
        	</div>
			
		</div>
		<div class="col-md-3 col-lg-3">
			<div class="form-group">
				{!! Form::label('apellidopaterno', 'Apellido Paterno:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::text('apellidopaterno', null, array('class' => 'form-control input-xs', 'id' => 'apellidopaterno', 'placeholder' => 'Ingrese apellido paterno')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-3 col-lg-3">
			<div class="form-group">
				{!! Form::label('apellidomaterno', 'Apellido Materno:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::text('apellidomaterno', null, array('class' => 'form-control input-xs', 'id' => 'apellidomaterno', 'placeholder' => 'Ingrese apellido materno')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-3 col-lg-3">
			{!! Form::label('nombres', 'Nombres:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
			<div class="col-lg-12 col-md-12 col-sm-12">
				{!! Form::text('nombres', null, array('class' => 'form-control input-xs', 'id' => 'nombres', 'placeholder' => 'Ingrese nombres')) !!}
			</div>
		</div>
		<div class="col-md-6 col-lg-6">
			{!! Form::label('direccion', 'Direccion:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
			<div class="col-lg-12 col-md-12 col-sm-12">
				{!! Form::text('direccion', null, array('class' => 'form-control input-xs', 'id' => 'direccion', 'placeholder' => 'Ingrese direccion')) !!}
			</div>
		</div>
		<div class="col-md-3 col-lg-3">
			<div class="form-group">
				{!! Form::label('fechanacimiento', 'Fecha Nac.:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::date('fechanacimiento', null, array('class' => 'form-control input-xs', 'id' => 'fechanacimiento', 'placeholder' => 'Ingrese fecha de nacimiento')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-3 col-lg-3" hidden>
			<div class="form-group">
				{!! Form::label('dniapoderado', 'DNI Apoderado:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::text('dniapoderado', null, array('class' => 'form-control input-xs', 'id' => 'dniapoderado', 'placeholder' => 'Ingrese DNI del apoderado')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-3 col-lg-3">
			<div class="form-group">
				{!! Form::label('celular', 'Celular:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::text('celular', null, array('class' => 'form-control input-xs', 'id' => 'celular', 'placeholder' => 'Ingrese celular')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-3 col-lg-3">
			<div class="form-group">
				{!! Form::label('correo', 'Correo:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::text('correo', null, array('class' => 'form-control input-xs', 'id' => 'correo', 'placeholder' => 'Ingrese correo')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-3 col-lg-3" hidden>
			<div class="form-group">
				{!! Form::label('plan_id', 'Plan:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::select('plan_id',$cboPlan, null, array('class' => 'form-control input-xs', 'id' => 'plan_id')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-3 col-lg-3" hidden>
			<div class="form-group">
				{!! Form::label('clases', 'Clases:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::number('clases', null, array('class' => 'form-control input-xs', 'id' => 'clases', 'placeholder' => 'Ingrese cantidad de clases')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-3 col-lg-3" hidden>
			<div class="form-group">
				{!! Form::label('creditos', 'Creditos:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::number('creditos', null, array('class' => 'form-control input-xs', 'id' => 'creditos', 'placeholder' => 'Ingrese creditos')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-3 col-lg-3" hidden>
			<div class="form-group">
				{!! Form::label('situacion', 'Situacion:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::select('situacion',$cboSituacion, null, array('class' => 'form-control input-xs', 'id' => 'situacion')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-3 col-lg-3" hidden>
			<div class="form-group">
				{!! Form::label('sucursal_id', 'Sede:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::select('sucursal_id',$cboSucursal, null, array('class' => 'form-control input-xs', 'id' => 'sucursal_id')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-3 col-lg-3">
			<div class="form-group">
				{!! Form::label('usuario', 'Ingrese Correo:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::text('usuario', null, array('class' => 'form-control input-xs', 'id' => 'usuario', 'placeholder' => 'Ingrese correo')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-3 col-lg-3">
			<div class="form-group">
				{!! Form::label('password', 'Contraseña:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::password('password', array('class' => 'form-control input-xs', 'id' => 'password', 'placeholder' => 'Ingrese contraseña')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-3 col-lg-3">
			<div class="form-group">
				{!! Form::label('password_confirmation', 'Confirmar contraseña:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::password('password_confirmation', array('class' => 'form-control input-xs', 'id' => 'password_confirmation', 'placeholder' => 'Confirme contraseña')) !!}
				</div>
			</div>
		</div>
		{{-- Solo administrador y solo al registrar: plan opcional con su pago (queda PENDIENTE hasta "Validar Pago") --}}
		@if(!empty($esAdmin) && is_null($cliente))
		<div class="col-md-12">
			<h6 class="mt-2 mb-2 pl-2 font-weight-bold"><i class="fa fa-bolt"></i> Plan (opcional)</h6>
		</div>
		<div class="col-md-4 col-lg-4">
			<div class="form-group">
				{!! Form::label('planpago_plan_id', 'Plan:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::select('planpago_plan_id', $cboPlanPago, null, array('class' => 'form-control input-xs', 'id' => 'planpago_plan_id', 'onchange' => 'cambiarPlanPago();')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-4 col-lg-4 js-planpago" style="display: none;">
			<div class="form-group">
				{!! Form::label('operacion', 'N.° de operación:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::text('operacion', null, array('class' => 'form-control input-xs', 'id' => 'operacion', 'maxlength' => '30', 'placeholder' => 'Ingrese N.° de operación')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-4 col-lg-4 js-planpago" style="display: none;">
			<div class="form-group">
				{!! Form::label('fechapago', 'Fecha de pago:', array('class' => 'col-lg-12 col-md-12 col-sm-12 control-label')) !!}
				<div class="col-lg-12 col-md-12 col-sm-12">
					{!! Form::date('fechapago', $hoy, array('class' => 'form-control input-xs', 'id' => 'fechapago', 'max' => $hoy)) !!}
				</div>
			</div>
		</div>
		<div class="col-md-12 js-planpago" style="display: none;">
			<small class="text-muted pl-2">El pago quedará <b>PENDIENTE</b>. El plan se activa cuando lo valides con <b>"Validar Pago"</b> en la lista de clientes.</small>
		</div>
		@endif
		<div class="col-md-9 col-lg-9" style="padding-top: 30px;">
			<div class="form-group">
				<div class="col-lg-12 col-md-12 col-sm-12">
					<div class="form-check">
						<input type="checkbox" class="form-check-input chkTerminoObligatorio" name="acepta_terminos" id="acepta_terminos" value="1" onchange="validarTerminos();">
						<label class="form-check-label" for="acepta_terminos">He leído y acepto los Términos y Condiciones. <b>(Obligatoria)</b></label>
					</div>
					<div class="form-check">
						<input type="checkbox" class="form-check-input chkTerminoObligatorio" name="acepta_datos" id="acepta_datos" value="1" onchange="validarTerminos();">
						<label class="form-check-label" for="acepta_datos">He leído la Política de Protección de Datos Personales. <b>(Obligatoria)</b></label>
					</div>
					<div class="form-check">
						<input type="checkbox" class="form-check-input" name="autoriza_imagen" id="autoriza_imagen" value="1">
						<label class="form-check-label" for="autoriza_imagen">Autorizo el uso de mi imagen para fines publicitarios, promocionales e institucionales de DANZZA. (Opcional)</label>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-3 col-lg-3">
			<br />
			<br />
			<br />
			<br />
			<br />
			<div class="form-group">
				<div class="col-lg-12 col-md-12 col-sm-12 text-center">
					{!! Form::button('<i class="fa fa-check "></i> '.$boton, array('class' => 'btn btn-primary btn-sm', 'id' => 'btnGuardar', 'disabled' => 'disabled', 'onclick' => 'guardarCliente(\''.$entidad.'\', this)')) !!}
				{!! Form::button('<i class="fa fa-undo "></i> Cancelar', array('class' => 'btn btn-default btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
			</div>
		</div>
	</div>
{!! Form::close() !!}
<style type="text/css">
	#divMensajeError{!! $entidad !!} {
		color:red;
	}
	.alert{
		color: red;
	}
</style>
<script type="text/javascript">
$(document).ready(function() {
	configurarAnchoModal('1200');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="dni"]').inputmask("99999999");
    //$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="ruc"]').inputmask("99999999999");
}); 

// Muestra los datos del pago solo si el administrador eligió un plan
function cambiarPlanPago(){
	var form = $(IDFORMMANTENIMIENTO + '{!! $entidad !!}');
	form.find('.js-planpago').toggle(form.find('#planpago_plan_id').val() != '');
}

function validarTerminos(){
	var form = $(IDFORMMANTENIMIENTO + '{!! $entidad !!}');
	var total = form.find('.chkTerminoObligatorio').length;
	var marcados = form.find('.chkTerminoObligatorio:checked').length;
	form.find('#btnGuardar').prop('disabled', marcados < total);
}

function guardarCliente(entidad, btn){
	var form = $(IDFORMMANTENIMIENTO + '{!! $entidad !!}');
	var password = form.find('#password').val();
	var confirmacion = form.find('#password_confirmation').val();
	if(password != confirmacion){
		toastr.warning("Las contraseñas no coinciden.", 'Error:');
		form.find('#password_confirmation').focus();
		return false;
	}
	guardar(entidad, btn);
}

function buscarDNI(){
	var reg = new RegExp('^[0-9]+$');
    if($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="documento"]').val() == ""){
        toastr.warning("Debe ingresar un DNI.", 'Error:');
    }else if(!reg.test($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="documento"]').val())){
        toastr.warning("El DNI es incorrecto.", 'Error:');
	}else{
        $.ajax({
            type: "POST",
            url: "webservices/buscarDNI",
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