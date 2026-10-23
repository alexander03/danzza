<div class="content-wrapper p-2 ml-0 " id="container"  >
	<!-- Content Header (Page header) -->
	
	  <div class="content-header mb-none">
		<div class="container-fluid">
		  <div class="row mb-2">
			<div class="col-sm-6">
			  <h1 class="m-0 text-dark">{{$title}}</h1>
			</div><!-- /.col -->
			<div class="col-sm-6">
			  <ol class="breadcrumb float-sm-right">
				<li class="breadcrumb-item"><a href="#">Administracion</a></li>
				<li class="breadcrumb-item active">{{$entidad}}</li>
			  </ol>
			</div><!-- /.col -->
		  </div><!-- /.row -->
		</div><!-- /.container-fluid -->
	  </div>
	  <!-- /.content-header -->
  
	  <!-- Main content -->
	  <div class="content ">
		<div class="container-fluid">
		  <div class="">
			  <div class="row justify-content-center">
				  <div class="col-lg-12 col-md-12">
					  <div class="card mt-4">
						<div class="card-body">
							{!! Form::open(['route' => $ruta["search"], 'method' => 'POST' ,'onsubmit' => 'return false;', 'class' => '', 'role' => 'form', 'autocomplete' => 'off', 'id' => 'formBusqueda'.$entidad]) !!}
							{!! Form::hidden('page', 1, array('id' => 'page')) !!}
							{!! Form::hidden('accion', 'listar', array('id' => 'accion')) !!}
							<div class="row">
							<div class="col-lg-2 col-md-2  form-group">
								{!! Form::label('codigo', 'Codigo') !!}
								{!! Form::text('codigo', '', array('class' => 'form-control ', 'id' => 'codigo')) !!}
							</div>
							<div class="col-lg-3 col-md-3  form-group">
								{!! Form::label('cliente', 'Cliente') !!}
								{!! Form::text('cliente', '', array('class' => 'form-control ', 'id' => 'cliente')) !!}
							</div>
							<div class="col-lg-2 col-md-2  form-group">
								{!! Form::label('sucursal', 'Sede') !!}
								{!! Form::select('sucursal', $cboSucursal, '', array('class' => 'form-control ', 'id' => 'sucursal', 'onchange' => 'filtrarHorarios()')) !!}
							</div>
							<div class="col-lg-3 col-md-3  form-group">
								{!! Form::label('instructor', 'Instructor') !!}
								{!! Form::select('instructor', $cboInstructor, '', array('class' => 'form-control ', 'id' => 'instructor', 'onchange' => 'filtrarHorarios()')) !!}
							</div>
							<div class="col-lg-2 col-md-2  form-group">
								{!! Form::label('horario', 'Horario') !!}
								{!! Form::select('horario', $cboHorario, '', array('class' => 'form-control ', 'id' => 'horario', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="col-lg-2 col-md-2  form-group">
								{!! Form::label('clase', 'Clase') !!}
								{!! Form::select('clase', $cboClase, '', array('class' => 'form-control ', 'id' => 'clase', 'onchange' => 'filtrarHorarios()')) !!}
							</div>
							<div class="col-lg-2 col-md-2  form-group" style="min-width: 150px;">
								{!! Form::label('filas', 'Filas a mostrar') !!}
								{!! Form::selectRange('filas', 1, 30, 10, array('class' => 'form-control input-xs', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							</div>
							{!! Form::close() !!}
						</div>
					  </div>
					  <div class="row mt-2" >
						<div class="col-md-12">
						  <div class="card">
							<div class="card-header">
							  <h3 class="card-title">{{$title}}</h3>
							  <div class="card-tools">
								{!! Form::button(' <i class="fa fa-plus fa-fw"></i> Agregar', array('class' => 'btn  btn-outline-primary', 'id' => 'btnNuevo', 'onclick' => 'modal (\''.URL::route($ruta["create"], array('listar'=>'SI')).'\', \''.$titulo_registrar.'\', this);')) !!}
							  </div>
							</div>
							<!-- /.card-header -->
							<div class="card-body table-responsive px-3">
								<div id="listado{{ $entidad }}">
								</div>
							</div>
							<!-- /.card-body -->
							   
			  
						  </div>
						  <!-- /.card -->
						</div>
					  </div>
					  
				  </div>
			  </div>
		  </div>
		  <!-- /.row -->
		</div><!-- /.container-fluid -->
	  </div>
	  <!-- /.content -->
	
  </div>

  
<script>
	// Recarga el filtro de Horario según Sede / Instructor / Clase y luego busca
	function filtrarHorarios() {
		var form = $(IDFORMBUSQUEDA + '{{ $entidad }}');
		var $horario = form.find('#horario');
		var seleccionado = $horario.val();
		$.ajax({
			type: 'GET',
			url: '{{ URL::route('reserva.horariosfiltro') }}',
			data: {
				sucursal: form.find('#sucursal').val(),
				instructor: form.find('#instructor').val(),
				clase: form.find('#clase').val()
			},
			dataType: 'json',
			success: function (horarios) {
				$horario.empty().append('<option value="">--Todos--</option>');
				$.each(horarios, function (i, h) {
					$horario.append($('<option>', { value: h.id, text: h.texto }));
				});
				// Se mantiene el horario elegido si sigue en la lista
				if (seleccionado && $horario.find('option[value="' + seleccionado + '"]').length) {
					$horario.val(seleccionado);
				}
				buscar('{{ $entidad }}');
			},
			error: function () {
				buscar('{{ $entidad }}');
			}
		});
	}

	$(document).ready(function () {
		buscar('{{ $entidad }}');
		init(IDFORMBUSQUEDA+'{{ $entidad }}', 'B', '{{ $entidad }}');
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="codigo"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="cliente"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});
	});
</script>