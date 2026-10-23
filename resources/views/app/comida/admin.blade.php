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
				<li class="breadcrumb-item"><a href="#">Usuarios</a></li>
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
						  <div class="row">
							{!! Form::open(['route' => $ruta["show"], 'method' => 'POST' ,'onsubmit' => 'return false;', 'class' => 'w-100 d-md-flex d-lg-flex d-sm-inline-block', 'role' => 'form', 'autocomplete' => 'off', 'id' => 'formBusqueda'.$entidad]) !!}
							{!! Form::hidden('page', 1, array('id' => 'page')) !!}
							{!! Form::hidden('accion', 'listar', array('id' => 'accion')) !!}
							
							<div class="col-lg-3 col-md-3  form-group">
								{!! Form::label('name', 'DNI:') !!}
								{!! Form::text('name', '', array('class' => 'form-control input-xs', 'id' => 'name','maxlength'=>'8')) !!}
							</div>
							<div class="col-lg-3 col-md-3  form-group">
								{!! Form::label('otros', 'Otros:') !!}
								{!! Form::text('otros', '', array('class' => 'form-control input-xs', 'id' => 'otros')) !!}
							</div>
							{!! Form::close() !!}
						  </div>
						</div>
					  </div>
					  <div class="row mt-2" >
						<div class="col-md-12">
						  <div class="card">
							<div class="card-header">
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
		//buscar('{{ $entidad }}');
		init(IDFORMBUSQUEDA+'{{ $entidad }}', 'B', '{{ $entidad }}');
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="name"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				var dni = $(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="name"]').val();
				if(dni.trim=="" || dni.length<8 || dni.length>8){
					alert("Debe ingresar un DNI valido");
				}else{
					buscar('{{ $entidad }}');
					$("#name").val("");
					$("#name").focus();
				}
			}
		});
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="otros"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
				$("#otros").val("");
				$("#otros").focus();
			}
		});
	});

	function imprimirComida(id){
	    $.ajax({
	        type: "POST",
	        url: "comida/imprimirComida",
	        data: "id="+id+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),
	        success: function(a) {
	            console.log(a);
		    }
	    });
	}
</script>