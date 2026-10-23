<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Plan;
use App\Clase;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PlanController extends Controller
{
    protected $folderview      = 'app.plan';
    protected $tituloAdmin     = 'Plan';
    protected $tituloRegistrar = 'Registrar Plan';
    protected $tituloModificar = 'Modificar Plan';
    protected $tituloEliminar  = 'Eliminar Plan';
    protected $rutas           = array('create' => 'plan.create', 
            'edit'     => 'plan.edit', 
            'delete'   => 'plan.eliminar',
            'search'   => 'plan.buscar',
            'index'    => 'plan.index',
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
        $entidad          = 'Plan';
        $name             = Libreria::getParam($request->input('name'));
        $resultado        = Plan::where('nombre','like','%'.$name.'%')
                            ->orderBy('orden','asc');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nombre', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Descripcion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Recomendado', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Clase', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro. Clases', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Precio', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Precio Promoción', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Vigencia', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Créditos Adicionales', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Beneficios', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Reserva Ant.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Orden', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Situación', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '3');
        
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
        $entidad          = 'Plan';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboClase = array('' => '--Todos--') + Clase::pluck('nombre', 'id')->all();
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboClase'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar       = Libreria::getParam($request->input('listar'), 'NO');
        $entidad      = 'Plan';
        $plan  = null;
        $formData     = array('plan.store');
        $formData     = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton        = 'Registrar'; 
        $cboSituacion = array('ACTIVO' => 'ACTIVO', 'INACTIVO' => 'INACTIVO','OCULTO'=>'OCULTO');
        $cboClase = array('' => '--Seleccione--') + Clase::pluck('nombre', 'id')->all();
        return view($this->folderview.'.mant')->with(compact('plan', 'formData', 'entidad', 'boton', 'listar','cboSituacion','cboClase'));
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
        $validacion = Validator::make($request->all(),
            array(
                'nombre' => 'required',
                'descripcion' => 'required|max:6000',
                'precio' => 'required'
                )
            );
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request){
            $Plan       = new Plan();
            $Plan->nombre = $request->input('nombre');
            $Plan->descripcion = $request->input('descripcion');
            $Plan->recomendado = $request->input('recomendado');
            $Plan->precio = $request->input('precio');
            $Plan->preciopromocion = $request->input('preciopromocion');
            $Plan->creditos = $request->input('creditos');
            $Plan->vigencia = $request->input('vigencia');
            $Plan->creditosadicionales = $request->input('creditosadicionales');
            $Plan->reservaant = $request->input('reservaant');
            $Plan->situacion = $request->input('situacion');
            $Plan->beneficios = $request->input('beneficios');
            $Plan->clase_id = $request->input('clase_id');
            $Plan->orden = $request->input('orden');
            $Plan->save();
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
        $existe = Libreria::verificarExistencia($id, 'plan');
        if ($existe !== true) {
            return $existe;
        }
        $listar       = Libreria::getParam($request->input('listar'), 'NO');
        $plan  = Plan::find($id);
        $entidad      = 'Plan';
        $formData     = array('plan.update', $id);
        $formData     = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton        = 'Modificar';
        $cboSituacion = array('ACTIVO' => 'ACTIVO', 'INACTIVO' => 'INACTIVO','OCULTO'=>'OCULTO');
        $cboClase = array('' => '--Seleccione--') + Clase::pluck('nombre', 'id')->all();
        return view($this->folderview.'.mant')->with(compact('plan', 'formData', 'entidad', 'boton', 'listar', 'cboSituacion','cboClase'));
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
        $existe = Libreria::verificarExistencia($id, 'plan');
        if ($existe !== true) {
            return $existe;
        }
        $validacion = Validator::make($request->all(),
            array(
                'nombre' => 'required',
                'descripcion' => 'required|max:6000',
                'precio' => 'required'
                )
            );
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        } 
        $error = DB::transaction(function() use($request, $id){
            $Plan       = Plan::find($id);
            $Plan->nombre = $request->input('nombre');
            $Plan->descripcion = $request->input('descripcion');
            $Plan->recomendado = $request->input('recomendado');
            $Plan->precio = $request->input('precio');
            $Plan->preciopromocion = $request->input('preciopromocion');
            $Plan->vigencia = $request->input('vigencia');
            $Plan->creditos = $request->input('creditos');
            $Plan->creditosadicionales = $request->input('creditosadicionales');
            $Plan->reservaant = $request->input('reservaant');
            $Plan->situacion = $request->input('situacion');
            $Plan->beneficios = $request->input('beneficios');
            $Plan->clase_id = $request->input('clase_id');
            $Plan->orden = $request->input('orden');
            $Plan->save();

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
        $existe = Libreria::verificarExistencia($id, 'plan');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $Plan = Plan::find($id);
            $Plan->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    /**
     * Función para confirmar la eliminación de un registrlo
     * @param  integer $id          id del registro a intentar eliminar
     * @param  string $listarLuego consultar si luego de eliminar se listará
     * @return html              se retorna html, con la ventana de confirmar eliminar
     */
    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'plan');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Plan::find($id);
        $entidad  = 'Plan';
        $formData = array('route' => array('plan.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }

}
