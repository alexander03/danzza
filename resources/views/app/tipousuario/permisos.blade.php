<?php 
use App\Menuoptioncategory;
use App\Usertype;
use App\Menuoption;
use Illuminate\Support\Facades\Input;

$categoriasPadre = Menuoptioncategory::whereNull('menuoptioncategory_id')->get();
$asignados       = array();
$opciones        = Usertype::find($tipousuario->id)->menuoptions;
foreach ($opciones as $key => $value) {
	$asignados[] = $value->id;
}

function generarArbol($idcategoria, $nivel, $asignados){
	$sangria = '';
	for ($i=0; $i < pow(2, $nivel); $i++) { 
		$sangria .= '&nbsp;';
	}
	$categorias = Menuoptioncategory::where('menuoptioncategory_id', '=', $idcategoria)->orderBy('order', 'ASC')->get();
	$opcionmenus = Menuoption::where('menuoptioncategory_id', '=', $idcategoria)->orderBy('order', 'ASC')->get();
?>
	 <?php foreach( $opcionmenus as $key => $opcionmenu){ ?>
		@if(in_array($opcionmenu->id, $asignados))
			{!! $sangria !!}
			@if(strtoupper($opcionmenu->name) === 'SEPARADOR')
				{!! Form::label('condicion'.$opcionmenu->id, '<< SEPARADOR >>') !!}
			@else
				{!! Form::label('condicion'.$opcionmenu->id, $opcionmenu->name) !!}
			@endif
			{!! Form::checkbox('condicion[]', '', Input::old('condicion'.$opcionmenu->id, true), array('id' => 'condicion'.$opcionmenu->id,'class' => 'pull-right', 'onchange' => 'cambiarEstado(this, \''.'estado'.$opcionmenu->id.'\');')) !!}
			{!! Form::hidden('estado[]', Input::old('estado'.$opcionmenu->id, 'H'), array('id' => 'estado'.$opcionmenu->id)) !!}
			{!! Form::hidden('idopcionmenu[]', $opcionmenu->id, array('id' => 'idopcionmenu'.$opcionmenu->id)) !!}
			{!! '<br>' !!}
		@else
			{!! $sangria !!}
			@if(strtoupper($opcionmenu->name) === 'SEPARADOR')
				{!! Form::label('condicion'.$opcionmenu->id, '<< SEPARADOR >>') !!}
			@else
				{!! Form::label('condicion'.$opcionmenu->id, $opcionmenu->name) !!}
			@endif
			{!! Form::checkbox('condicion[]', '', Input::old('condicion'.$opcionmenu->id, false), array('id' => 'condicion'.$opcionmenu->id,'class' => 'pull-right', 'onchange' => 'cambiarEstado(this, \''.'estado'.$opcionmenu->id.'\');')) !!}
			{!! Form::hidden('estado[]', Input::old('estado'.$opcionmenu->id, 'I'), array('id' => 'estado'.$opcionmenu->id)) !!}
			{!! Form::hidden('idopcionmenu[]', $opcionmenu->id, array('id' => 'idopcionmenu'.$opcionmenu->id)) !!}
			{!! '<br>' !!}
		@endif
	 <?php }?>
	 <?php foreach($categorias as $key => $categoria) { ?>
		{!! $sangria !!}
		{!! "<b><u><span class='text-info'>".$categoria->name."</span></u></b>" !!}
		{!! '<br>' !!}
		<?php generarArbol($categoria->id, $nivel+1, $asignados); ?>
	 <?php } ?>
<?php } ?>
{!! Form::open(array('route' => array('tipousuario.guardarpermisos', $tipousuario->id), 'id' => 'formMantenimiento'.$entidad)) !!}
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	<div class="form-group border">
		<?php foreach($categoriasPadre as $key => $categoria){ ?>
			{!! '<b><u><span class=\'text-info\'>'.$categoria->name.'<span></u></b><br>' !!}
			<?php generarArbol($categoria->id, 2, $asignados); ?>
			<?php } ?>
	</div>
	<div class="form-group text-right">
		{!! Form::button('Guardar', array('class' => 'btn btn-primary btn-sm', 'id' => 'btnGuardar', 'onclick' => 'guardar(\''.$entidad.'\', this)')) !!}
		{!! Form::button('Cancelar', array('class' => 'btn btn-default btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal((contadorModal - 1));')) !!}
	</div>
{!! Form::close() !!}

<script type="text/javascript">
$(document).ready(function() {
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}');
}); 
function cambiarEstado (elemento, id) {
	if (elemento.checked) {
		$('#'+id).val('H');
	} else{
		$('#'+id).val('I');
	};
}
</script>