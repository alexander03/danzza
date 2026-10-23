<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($modelo, $formData) !!}
{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
{!!'<div class="callout callout-danger"><p class="text-danger">¿Esta seguro de eliminar el registro?</p></div>' !!}
<div class="form-group">
	<div class="col-lg-12 col-md-12 col-sm-12 text-right">
		{!! Form::button('<i class="fa fa-check "></i> '.$boton, array('class' => 'btn btn-danger btn-sm', 'id' => 'btnGuardar', 'onclick' => 'guardarEliminar(\''.$entidad.'\', this)')) !!}
		{!! Form::button('<i class="fa fa-undo "></i> Cancelar', array('class' => 'btn btn-default btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal((contadorModal - 1));')) !!}
	</div>
</div>
{!! Form::close() !!}
<script type="text/javascript">
	$(document).ready(function() {
		init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
		configurarAnchoModal('350');
	}); 

    function guardarEliminar (entidad, idboton) {
        var band=true;
        var msg="";
        if(band){
            contador=1;
            var idformulario = IDFORMMANTENIMIENTO + entidad;
            var data         = submitForm(idformulario);
            var respuesta    = '';
            var btn = $(idboton);
            btn.button('loading');
            data.done(function(msg) {
                respuesta = msg;
            }).fail(function(xhr, textStatus, errorThrown) {
                respuesta = 'ERROR';
                contador=0;
            }).always(function() {
                btn.button('reset');
                contador=0;
                if(respuesta === 'ERROR'){
                }else{
                //alert(respuesta);
                    var dat = JSON.parse(respuesta);
                    if(dat[0]!==undefined){
                        resp=dat[0].respuesta;    
                    }else{
                        resp='VALIDACION';
                    }
                    console.log(resp)
                    if (resp === 'OK') {
                        cerrarModal();
                        buscarCompaginado('', 'Accion realizada correctamente', entidad, 'OK');
                        //window.open('/juanpablo/ticket/pdfComprobante3?ticket_id='+dat[0].ticket_id,'_blank')
                    } else if(resp === 'ERROR') {
                        //alert(dat[0].msg);
                        toastr.error(dat[0].msg, 'ERROR:');
                    } else {
                        mostrarErrores(respuesta, idformulario, entidad);
                    }
                }
            });
        }else{
            toastr.error(msg, 'Corregir los sgtes errores:');
        }
    }

</script>