<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Sucursal;
use App\Empresa;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class sucursalController extends Controller
{
    protected $folderview      = 'app.sucursal';
    protected $tituloAdmin     = 'Sede';
    protected $entidad 		   = 'Sede';
    protected $tabla 		   = 'sucursal';
    protected $tituloRegistrar = 'Registrar Sede';
    protected $tituloModificar = 'Modificar Sede';
    protected $tituloEliminar  = 'Eliminar Sede';
    protected $rutas           = array('create' => 'sucursal.create', 
            'edit'   => 'sucursal.edit', 
            'delete' => 'sucursal.eliminar',
            'search' => 'sucursal.buscar',
            'index'  => 'sucursal.index',
        );


     /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }


    /**
     * Mostrar el resultado de búsquedas
     * 
     * @return Response 
     */
    public function buscar(Request $request)
    {
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = $this->entidad;
        $nombre           = Libreria::getParam($request->input('nombre'));
        $resultado        = Sucursal::listar($nombre,null);
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nombre', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Direccion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Teléfono', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '2');
        
        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
        $ruta             = $this->rutas;
        if (count($lista) > 0) {
            $clsLibreria     = new Libreria();
            $paramPaginacion = $clsLibreria->generarPaginacion($lista, $pagina, $filas, $entidad);
            $paginacion      = $paramPaginacion['cadenapaginacion'];
            $inicio          = $paramPaginacion['inicio'];
            $fin             = $paramPaginacion['fin'];
            $paginaactual    = $paramPaginacion['nuevapagina'];
            $lista           = $resultado->paginate($filas);
            $request->replace(array('page' => $paginaactual));
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta'));
        }
        return view($this->folderview.'.list')->with(compact('lista', 'entidad'));
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $entidad          = $this->entidad;
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = $this->entidad;
        $sucursal = null;
        $formData = array('sucursal.store');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('sucursal', 'formData', 'entidad', 'boton', 'listar'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $listar     = Libreria::getParam($request->input('listar'), 'NO');
       // $empresa = Empresa::first();
        $empresa 	= DB::table('empresa')->first();
        if(!$empresa){
        	return json_encode(array("empresa"=>["Primero debe crear una empresa"]));
        }
        $reglas     = array(
        	'nombre' => 'required',
        	'direccion' => 'required'
    		);
        $mensajes 	= array(
            'nombre.required'         => 'Debe ingresar un nombre',
            'direccion.required'         => 'Debe ingresar una dirección'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request,$empresa){
            $sucursal = new Sucursal();
            $sucursal->nombre = Libreria::getParam(strtr(strtoupper($request->input('nombre')),"àèìòùáéíóúçñäëïöü","ÀÈÌÒÙÁÉÍÓÚÇÑÄËÏÖÜ"),"");
            $sucursal->direccion = Libreria::getParam(strtr(strtoupper($request->input('direccion')),"àèìòùáéíóúçñäëïöü","ÀÈÌÒÙÁÉÍÓÚÇÑÄËÏÖÜ"),"");
            $sucursal->telefono = Libreria::getParam($request->input('telefono'),"");
            $sucursal->empresa_id = $empresa->id;
            $sucursal->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request)
    {
        $existe = Libreria::verificarExistencia($id, $this->tabla);
        if ($existe !== true) {
            return $existe;
        }
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $sucursal = Sucursal::find($id);
        $entidad  = $this->entidad;
        $formData = array('sucursal.update', $id);
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('sucursal', 'formData', 'entidad', 'boton', 'listar'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $existe = Libreria::verificarExistencia($id, $this->tabla);
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array(
        	'nombre' => 'required',
        	'direccion' => 'required'
    		);
        $mensajes 	= array(
            'nombre.required'         => 'Debe ingresar un nombre',
            'direccion.required'         => 'Debe ingresar una dirección'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        } 
        $error = DB::transaction(function() use($request, $id){
            $sucursal = Sucursal::find($id);
            $sucursal->nombre = Libreria::getParam(strtr(strtoupper($request->input('nombre')),"àèìòùáéíóúçñäëïöü","ÀÈÌÒÙÁÉÍÓÚÇÑÄËÏÖÜ"),"");
            $sucursal->direccion = Libreria::getParam(strtr(strtoupper($request->input('direccion')),"àèìòùáéíóúçñäëïöü","ÀÈÌÒÙÁÉÍÓÚÇÑÄËÏÖÜ"),"");
            $sucursal->telefono = Libreria::getParam($request->input('telefono'),"");
            $sucursal->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $existe = Libreria::verificarExistencia($id, $this->tabla);
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $sucursal = Sucursal::find($id);
            $sucursal->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, $this->tabla);
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Sucursal::find($id);
        $entidad  = $this->entidad;
        $formData = array('route' => array('sucursal.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
    
}
