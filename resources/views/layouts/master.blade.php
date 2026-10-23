<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ config('app.name', 'Mercadito') }}</title>
  
  <style>
    body{
      font-family: Nunito, sans-serif !important;
      font-size: .9rem !important;
    }
    .mostrarScroll{
    overflow-y:scroll !important;
    }
    .ocultarScroll{
    overflow-y:hidden !important;
    }
    #container.content-wrapper.p-2.ml-0 {
      margin-left:0px !important;
    }
		
  </style>
  
  <link rel="icon" type="image/svg+xml"
            href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/svgs/solid/person-dress.svg">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="css/app.css">
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <link rel="stylesheet" href="dist/css/typeaheadjs.css">
  <link rel="stylesheet" href="dist/css/select2.min.css">
  <link rel="stylesheet" href="dist/css/toastr.min.css">
  
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>

<body class="hold-transition sidebar-mini">
<div class="wrapper " id ="app">

  <!-- Navbar -->
  @include('app.header')
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  @include('app.sidebar')

  <!-- /.navbar -->

  <!-- Main Sidebar Container -->


  <!-- Content Wrapper. Contains page content -->
  @include('app.home')
  <!-- /.content-wrapper -->

  <!-- Control Sidebar -->
  <!--aside class="control-sidebar control-sidebar-dark">
    Control sidebar content goes here
    <div class="p-3">
      <h5>Title</h5>
      <p>Sidebar content</p>
    </div>
  </aside--> 
  <!-- /.control-sidebar -->

  <!-- Main Footer -->
  <footer class="main-footer">
    <!-- To the right -->
        <!-- Default to the left -->
    <strong>Copyright &copy; 2026.</strong> All rights reserved.
  </footer>
</div>

<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>


<!-- AdminLTE App -->

<script src="dist/js/adminlte.min.js"></script>

<script src="js/app.js"></script>
<script src="plugins/datatables/jquery.dataTables.js"></script>
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="dist/js/funciones.js"></script>
<script src="dist/js/bootbox.min.js"></script>
{{-- typeahead.js-bootstrap: para autocompletar --}}
<script src="dist/js/bootstrap3-typeahead.min.js"></script>
<script src="dist/js/bootstrap3-typeahead.js"></script>
<script src="dist/js/typeahead.bundle.min.js"></script>
<script src="dist/js/bloodhound.min.js"></script>
{{-- jquery.inputmask: para mascaras en cajas de texto --}}
<script src="dist/js/select2.min.js"></script>
<script src="dist/js/select2.js"></script>

<script src="dist/js/toastr.min.js"></script>


<script src="plugins/input-mask/jquery.inputmask.js"></script>
<script src="plugins/input-mask/jquery.inputmask.extensions.js"></script>
<script src="plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
<script src="plugins/input-mask/jquery.inputmask.numeric.extensions.js"></script>
<script src="plugins/input-mask/jquery.inputmask.phone.extensions.js"></script>
<script src="plugins/input-mask/jquery.inputmask.regex.extensions.js"></script>
<?php
 use App\Caja;
 $caja_sesion_id = session('caja_sesion_id','0');
 $user_caja_asignada = Auth::user()->caja_id;
 ?>
@if(Auth::user()->usertype_id == 2))
    <?php 
        $nro_cajas = Caja::where('sucursal_id', Auth::user()->sucursal_id)->where('estado','CERRADA')->count();
    ?>
      @if($caja_sesion_id == '0' )
          @if(!$user_caja_asignada)
              @if($nro_cajas && $nro_cajas != 0)
                    @if($nro_cajas == 1)
                      <?php 
                        $caja_unica = Caja::where('sucursal_id' , Auth::user()->sucursal_id)->where('estado','CERRADA')->first();
                        $caja_nombre = $caja_unica->nombre;
                        session(['caja_sesion_id' => $caja_unica->id]); 
                      ?>
                      <script>
                        $(document).ready(function (){
                                  toastr.success('Se te asignó la caja '+'{{$caja_nombre}}','Caja asignada automaticamente');
                        });
                      </script>
                    @elseif($nro_cajas > 1)
                        <script>
                          $(document).ready(function (){
                            modal_nocerrar('{{URL::route('mantenimientocaja.asignarcaja')}}' , 'Asignar Caja');
                          });
                        </script>
                    @endif
              @else
              <script>
                $(document).ready(function (){
                            toastr.error('No existen cajas disponibles en tu sucursal','No hay cajas');
                  });
              </script>
              @endif
        @else
        <?php session(['caja_sesion_id'=>$user_caja_asignada]); ?>
        @endif
     @endif
@endif

{{-- jquery.inputmask: para mascaras en cajas de texto --}}
 
</body>
</html>
