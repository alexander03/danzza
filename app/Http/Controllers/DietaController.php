<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Dieta;
use App\Person;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DietaController extends Controller
{
    protected $folderview      = 'app.dieta';
    protected $tituloAdmin     = 'Dieta';
    protected $tituloRegistrar = 'Registrar dieta';
    protected $tituloModificar = 'Modificar dieta';
    protected $tituloEliminar  = 'Eliminar dieta';
    protected $rutas           = array('create' => 'dieta.create', 
            'edit'     => 'dieta.edit', 
            'delete'   => 'dieta.eliminar',
            'search'   => 'dieta.buscar',
            'index'    => 'dieta.index',
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
        $entidad          = 'Dieta';
        $name             = Libreria::getParam($request->input('name'));
        $fechainicio      = Libreria::getParam($request->input('fechainicio'),'');
        $fechafin      = Libreria::getParam($request->input('fechafin'),'');
        $resultado        = Dieta::join('person','person.id','=','dieta.persona_id')
                                ->where(DB::raw('concat(person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'),'like','%'.$name.'%');
        if($fechainicio!=""){
            $resultado = $resultado->where('dieta.fechainicio','>=',$fechainicio);
        }
        if($fechafin!=""){
            $resultado = $resultado->where('dieta.fechafin','<=',$fechafin);
        }
        $lista            = $resultado->select('dieta.*')->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha Inicio', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha Fin', 'numero' => '1');
        $cabecera[]       = array('valor' => 'DNI', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Persona', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Modalidad', 'numero' => '1');
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
        $entidad          = 'Dieta';
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
        $listar       = Libreria::getParam($request->input('listar'), 'NO');
        $entidad      = 'Dieta';
        $dieta  = null;
        $formData     = array('dieta.store');
        $formData     = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton        = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('dieta', 'formData', 'entidad', 'boton', 'listar'));
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
                'fechainicio' => 'required|max:60',
                'fechafin' => 'required|max:60'
                )
            );
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request){
            $dieta       = new Dieta();
            $dieta->persona_id = $request->input('persona_id');
            $dieta->fechainicio = $request->input('fechainicio');
            $dieta->fechafin = $request->input('fechafin');
            $dieta->save();
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
        $existe = Libreria::verificarExistencia($id, 'dieta');
        if ($existe !== true) {
            return $existe;
        }
        $listar       = Libreria::getParam($request->input('listar'), 'NO');
        $dieta  = Dieta::find($id);
        $entidad      = 'Dieta';
        $formData     = array('dieta.update', $id);
        $formData     = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton        = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('dieta', 'formData', 'entidad', 'boton', 'listar'));
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
        $existe = Libreria::verificarExistencia($id, 'dieta');
        if ($existe !== true) {
            return $existe;
        }
        $validacion = Validator::make($request->all(),
            array(
                'fechainicio' => 'required|max:60'
                )
            );
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        } 
        $error = DB::transaction(function() use($request, $id){
            $dieta       = Dieta::find($id);
            $dieta->persona_id = $request->input('persona_id');
            $dieta->fechainicio = $request->input('fechainicio');
            $dieta->fechafin = $request->input('fechafin');
            $dieta->save();

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
        $existe = Libreria::verificarExistencia($id, 'dieta');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $dieta = Dieta::find($id);
            $dieta->delete();
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
        $existe = Libreria::verificarExistencia($id, 'dieta');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Dieta::find($id);
        $entidad  = 'Dieta';
        $formData = array('route' => array('dieta.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function personautocompletar($searching)
    {
        $resultado        = Person::join('rolpersona', 'rolpersona.person_id', '=', 'person.id')->where('rolpersona.rol_id', '=', 1)
            ->where(function ($sql) use ($searching) {
                $sql->where(DB::raw('CONCAT(apellidopaterno," ",apellidomaterno," ",nombres)'), 'LIKE', '%' . strtoupper($searching) . '%')->orWhere('bussinesname', 'LIKE', '%' . strtoupper($searching) . '%');
            })
            ->where(function ($sql) {
                $sql->whereNull('person.sexo')->orWhere('person.sexo','like','N');
            })
            ->whereNull('person.deleted_at')->whereNull('rolpersona.deleted_at')->orderBy('apellidopaterno', 'ASC');
        $list      = $resultado->select('person.*')->get();
        $data = array();
        foreach ($list as $key => $value) {
            $name = '';
            if ($value->bussinesname != null) {
                $name = $value->bussinesname;
            } else {
                $name = $value->apellidopaterno . " " . $value->apellidomaterno . " " . $value->nombres;
            }
            $data[] = array(
                'label' => trim($name),
                'id'    => $value->id,
                'value' => trim($name),
                'ruc' => $value->ruc,
            );
        }
        return json_encode($data);
    }

}
