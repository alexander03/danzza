<?php
use App\Menuoptioncategory;
use App\Menuoption;
use App\Permission;
use App\User;
use App\Person;
use App\Cliente;
use App\Instructor;
use App\Alerta;
use App\Calibracion;
use App\Indicador;
use GuzzleHttp\Client;
$user                  = Auth::user();
session(['usertype_id' => $user->usertype_id]);
$tipousuario_id        = session('usertype_id');
$menu                  = generarMenu($tipousuario_id);
if($user->usertype_id==5){
    $person            = Cliente::find($user->cliente_id);
}elseif($user->usertype_id==6){
    $person            = Instructor::find($user->instructor_id);
}else{
    $person            = Person::find($user->person_id);
}

?>

<aside class="main-sidebar position-fixed sidebar-dark-primary elevation-4" style="overflow-y: auto; max-height: 100%;">
    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link text-center">
      <!--img src="dist/logo_images/logo-cto.jpeg" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
           style="opacity: .8"-->
      <span class="brand-text font-weight-light">{{ config('app.name', 'Mercadito') }}</span>
    </a>
    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="dist/img/user-danzza.png" class="img-circle elevation-2" alt="User Image">
        </div> 
        <div class="info">
          <a href="#" class="d-block">
             <div class="text-bold">{{explode(" ",$person->nombres)[0]}}</div>
             <small>{{ strToUpper($user->login)." (".$user->usertype->name.")"}}</small>
            </a>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          {!! $menu !!}
          
          <li class="nav-item">
            <a href="#" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="nav-link">
              <i class="nav-icon fas fa-sign-out-alt" style="color:red"></i>
              <p>
                Cerrar Sesión
                {{-- <span class="right badge badge-danger">New</span> --}}
              </p>
            </a>
          </li>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
    <!-- /.sidebar -->
  </aside>

  <?php
function generarMenu($idtipousuario)
{
    $menu = array();
    #Paso 1°: Buscar las categorias principales
    $categoriaopcionmenu = new Menuoptioncategory();
    $opcionmenu          = new Menuoption();
    $permiso             = new Permission();
    $catPrincipales      = $categoriaopcionmenu->whereNull('menuoptioncategory_id')->orderBy('order', 'ASC')->get();
    $cadenaMenu          = '';
    foreach ($catPrincipales as $key => $catPrincipal) {
        #Buscamos a las categorias hijo
        $hijos = "";
        $usar = false;
        $aux = array();
        $opciones = $opcionmenu->where('menuoptioncategory_id', '=', $catPrincipal->id)->orderBy('order', 'ASC')->get();
        if ($opciones->count()) {               
            foreach ($opciones as $key => $opcion) {
                $permisos = $permiso->where('menuoption_id', '=', $opcion->id)->where('usertype_id', '=', $idtipousuario)->first();
                if ($permisos) {
                    $usar  = true;
                    $aux2  = $opcionmenu->find($permisos->menuoption_id);
                    $aux[] = array(
                        'nombre' => $aux2->name,
                        'link'   => $aux2->link,
                        'icono'  => $aux2->icon,
                        'id'     => $aux2->id,
                        'color'  => $aux2->color
                        );
                }
            }           
        }
        if ($hijos != '' || $usar === true ) {
            $cadenaMenu .= '<li class="nav-item has-treeview">';
            $cadenaMenu .= '<a href="#" id="cat'.$catPrincipal->id.'" class="nav-link categs"><i class="nav-icon '.$catPrincipal->icon.'"></i> <p>'.$catPrincipal->name.'<i class="right fas fa-angle-left"></i></p></a>';
            $cadenaMenu .= '<ul class="nav nav-treeview">';
            for ($i=0; $i < count($aux); $i++) { 
                if (strtoupper($aux[$i]['nombre']) === 'SEPARADOR') {
                    //$cadenaMenu .= '<li class="divider"></li>';
                }else{
                    $cadenaMenu .= '<li class="nav-item ml-2"><a href="#" id="opt'.$aux[$i]['id'].'" onclick="cargarRutaLink(\''.URL::to($aux[$i]['link']).'\', \'container\',\''.$catPrincipal->id.'\',\''.$aux[$i]['id'].'\');" class="opts nav-link"><i class="fas '.$aux[$i]['icono'].' nav-icon" style="color:'.$aux[$i]['color'].'"></i><p>'.$aux[$i]['nombre'].'</p></a></li>';
                }
            }
            if (count($aux) > 0 && $hijos != '' ) {
                $cadenaMenu .= '';
            }
            if ($hijos != '') {
                $cadenaMenu .= $hijos;
            }
            $cadenaMenu .= '</ul>';
            $cadenaMenu .= '</li>';
        }
    }
    $cadenaMenu .= '';
    return $cadenaMenu;
}

function buscarHijos($categoriaopcionmenu_id, $tipousuario_id)
{
    $menu = array();
    $categoriaopcionmenu = new Menuoptioncategory();
    $opcionmenu          = new Menuoption();
    $permiso             = new Permission();

    $catHijos = $categoriaopcionmenu->where('menuoptioncategory_id', '=', $categoriaopcionmenu_id)->orderBy('order', 'ASC')->get();
    $cadenaMenu = '';
    foreach ($catHijos as $key => $catHijo) {
        $usar = false;
        $aux = array();
        $hijos = buscarHijos($catHijo->id, $tipousuario_id);
        $opciones = $opcionmenu->where('menuoptioncategory_id', '=', $catHijo->id)->orderBy('order', 'ASC')->get();
        if ($opciones->count()) {

            foreach ($opciones as $key => $opcion) {
                $permisos = $permiso->where('menuoption_id', '=', $opcion->id)->where('usertype_id', '=', $tipousuario_id)->first();
                if ($permisos) {
                    $usar = true;
                    $aux2 = $opcionmenu->find($permisos->menuoption_id);
                    $aux[] = array(
                        'nombre' => $aux2->name,
                        'link'   => $aux2->link,
                        'icono'  => $aux2->icon
                        );
                }
            }

        }
        if ($hijos != '' || $usar === true ) {

            $cadenaMenu .= '<li class="nav-item has-treeview">';
            $cadenaMenu .= '<a href="#" class="nav-link"><i class="nav-icon  '.$catHijo->icon.'"></i> <p>'.$catHijo->name.'<i class="right fas fa-angle-left"></i></p></a>';
            $cadenaMenu .= '<ul class="nav nav-treeview">';
            for ($i=0; $i < count($aux); $i++) { 
                if (strtoupper($aux[$i]['nombre']) === 'SEPARADOR') {
                    //$cadenaMenu .= '<li class="divider"></li>';
                } else {
                    $cadenaMenu .= '<li class="nav-item "><a onclick="cargarRuta(\''.URL::to($aux[$i]['link']).'\', \'container\');" class="nav-link "><i class="'.$aux[$i]['icono'].'"></i><p>'.$aux[$i]['nombre'].'</p></a></li>';
                }
            }
            if (count($aux) > 0 && $hijos != '' ) {
                //$cadenaMenu .= '<li class="divider"></li>';
            }
            if ($hijos != '') {
                $cadenaMenu .= $hijos;
            }
            $cadenaMenu .= '</ul>';
            $cadenaMenu .= '</li>';
        }
    }
    return $cadenaMenu;
}

?>