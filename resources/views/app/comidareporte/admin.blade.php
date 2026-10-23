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
				  <div class="col-lg-8 col-md-8 col-offset-2">
					  <div class="card mt-4">
						<div class="card-body">
							{!! Form::open(['route' => $ruta["index"], 'method' => 'POST' ,'onsubmit' => 'return false;', 'class' => '', 'role' => 'form', 'autocomplete' => 'off', 'id' => 'formBusqueda'.$entidad]) !!}
							{!! Form::hidden('page', 1, array('id' => 'page')) !!}
							{!! Form::hidden('accion', 'listar', array('id' => 'accion')) !!}
							
						  <div class="row">
							<div class="col-lg-12 col-md-12  form-group">
								{!! Form::label('fechainicio', 'Fecha inicio') !!}
								{!! Form::date('fechainicio', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fechainicio')) !!}
							</div>
						  </div>
						  <div class="row">
							<div class="col-lg-12 col-md-12  form-group">
								{!! Form::label('fechafin', 'Fecha fin') !!}
								{!! Form::date('fechafin', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fechafin')) !!}
							</div>
						  </div>
						  <div class="row">
							<div class="col-lg-12 col-md-12  form-group">
								{!! Form::label('modalidad', 'Modalidad') !!}
								{!! Form::select('modalidad',$cboModalidad,null, array('class' => 'form-control input-xs', 'id' => 'modalidad')) !!}
							</div>
						  </div>
						  <div class="row">
							<div class="col-lg-12 col-md-12  form-group text-right">
								{!! Form::button('<i class="fa fa-file-excel"></i> EXCEL', array('class' => 'btn btn-success btn-sm  ', 'id' => 'btnDetalle', 'onclick' => 'imprimir();' ,'style'=>'width:200px;')) !!}   
							</div>
						  </div>
						{!! Form::close() !!}
						</div>
					  </div>
					  
							<!-- /.card-header -->
							<div class="card-body table-responsive px-3">
								<div id="listado{{ $entidad }}">
								</div>
							</div>
							<!-- /.card-body -->
							   
			  
						  
					  
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
		init(IDFORMBUSQUEDA+'{{ $entidad }}', 'B', '{{ $entidad }}');
	});

    function imprimir(){
        window.open("comidareporte/excelDetalle?fechainicio="+$("#fechainicio").val()+"&fechafin="+$("#fechafin").val()+"&modalidad="+$("#modalidad").val(),"_blank");
    }
</script>