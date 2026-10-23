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
							<div class="col-lg-3 col-md-3  form-group">
								{!! Form::label('instructor', 'Instructor') !!}
								{!! Form::select('instructor', $cboInstructor, '', array('class' => 'form-control ', 'id' => 'instructor', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="col-lg-2 col-md-2  form-group">
								{!! Form::label('sucursal', 'Sede') !!}
								{!! Form::select('sucursal', $cboSucursal, '', array('class' => 'form-control ', 'id' => 'sucursal', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="col-lg-2 col-md-2  form-group">
								{!! Form::label('clase', 'Clase') !!}
								{!! Form::select('clase', $cboClase, '', array('class' => 'form-control ', 'id' => 'clase', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="col-lg-2 col-md-2  form-group">
								{!! Form::label('hora', 'Hora') !!}
								{!! Form::select('hora', $cboHora, '', array('class' => 'form-control ', 'id' => 'hora', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="col-lg-2 col-md-2  form-group">
								{!! Form::label('vigencia', 'Vigencia') !!}
								{!! Form::select('vigencia', array('' => '--Todos--', 'VIGENTE' => 'Vigente', 'VENCIDO' => 'Vencido', 'POR EMPEZAR' => 'Por empezar'), '', array('class' => 'form-control ', 'id' => 'vigencia', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="col-lg-2 col-md-2  form-group">
								{!! Form::label('situacion', 'Estado') !!}
								{!! Form::select('situacion', $cboSituacion, 'DISPONIBLE', array('class' => 'form-control ', 'id' => 'situacion', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							{{-- Días: muestra los horarios que se dictan en al menos uno de los días marcados --}}
							<div class="col-lg-5 col-md-6  form-group" hidden>
								<label>Días</label>
								<div class="pt-1">
									@foreach(array(1 => 'Lun', 2 => 'Mar', 3 => 'Mié', 4 => 'Jue', 5 => 'Vie', 6 => 'Sáb', 7 => 'Dom') as $num => $dia)
										<div class="form-check form-check-inline">
											<input type="checkbox" class="form-check-input" name="dias[]" id="filtroDia{{ $num }}" value="{{ $num }}" onchange="buscar('{{ $entidad }}')">
											<label class="form-check-label" for="filtroDia{{ $num }}">{{ $dia }}</label>
										</div>
									@endforeach
								</div>
							</div>
							<div class="col-lg-2 col-md-2  form-group" style="min-width: 150px;">
								{!! Form::label('nombre', 'Filas a mostrar') !!}
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
	$(document).ready(function () {
		buscar('{{ $entidad }}');
		init(IDFORMBUSQUEDA+'{{ $entidad }}', 'B', '{{ $entidad }}');
	});
</script>