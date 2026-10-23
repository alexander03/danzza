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
				<li class="breadcrumb-item active">{{$title}}</li>
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
								<div class="col-lg-2 col-md-3 form-group">
									{!! Form::label('fechainicio', 'Desde') !!}
									{!! Form::date('fechainicio', $desde, array('class' => 'form-control', 'id' => 'fechainicio', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
								</div>
								<div class="col-lg-2 col-md-3 form-group">
									{!! Form::label('fechafin', 'Hasta') !!}
									{!! Form::date('fechafin', $hasta, array('class' => 'form-control', 'id' => 'fechafin', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
								</div>
								{{-- El instructor solo ve sus propios horarios --}}
								@if(!$esInstructor)
								<div class="col-lg-3 col-md-3 form-group">
									{!! Form::label('instructor', 'Instructor') !!}
									{!! Form::select('instructor', $cboInstructor, '', array('class' => 'form-control', 'id' => 'instructor', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
								</div>
								@endif
								<div class="col-lg-2 col-md-3 form-group">
									{!! Form::label('sucursal', 'Sede') !!}
									{!! Form::select('sucursal', $cboSucursal, '', array('class' => 'form-control', 'id' => 'sucursal', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
								</div>
								<div class="col-lg-3 col-md-3 form-group">
									{!! Form::label('estado', 'Estado') !!}
									{!! Form::select('estado', $cboEstado, '', array('class' => 'form-control', 'id' => 'estado', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
								</div>
							</div>
							{!! Form::close() !!}
						</div>
					  </div>
					  <div class="row mt-2" >
						<div class="col-md-12">
						  <div class="card">
							<div class="card-header">
							  <h3 class="card-title">
								{{ $esInstructor ? 'Mis horarios con reservas' : 'Horarios con reservas' }}
							  </h3>
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
