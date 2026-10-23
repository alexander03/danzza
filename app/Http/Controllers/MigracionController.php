<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Migracion;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use App\Imports\MigracionImport;
use Excel;
use File;

class MigracionController extends Controller
{
    protected $folderview      = 'app.migracion';
    protected $tituloAdmin     = 'Migracion';
    protected $tituloRegistrar = 'Registrar persona';
    protected $tituloModificar = 'Modificar persona';
    protected $tituloEliminar  = 'Eliminar persona';
    protected $rutas           = array(
        'create' => 'migracion.create',
        'edit'   => 'migracion.edit',
        'delete' => 'migracion.eliminar',
        'search' => 'migracion.buscar',
        'index'  => 'migracion.index',
        'import' => 'migracion.import',
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
        $entidad          = 'Migracion';
        $nombre             = Libreria::getParam($request->input('nombre'));
        $resultado        = Migracion::where('trabajador', 'LIKE', '%' . strtoupper($nombre) . '%')
            ->orderBy('fecha','asc')
            ->orderBy('trabajador', 'ASC');
        if($request->input('fechainicial')!=""){
            $resultado = $resultado->where('fecha','>=',$request->input('fechainicial'));
        }
        if($request->input('fechafinal')!=""){
            $resultado = $resultado->where('fecha','<=',$request->input('fechafinal'));
        }
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Trabajador', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Dia', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Empresa', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Tipo Doc.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro Doc.', 'numero' => '1');

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
            return view($this->folderview . '.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta'));
        }
        return view($this->folderview . '.list')->with(compact('lista', 'entidad'));
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $entidad          = 'Migracion';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        return view($this->folderview . '.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Persona';
        $persona = null;
        $formData = array('persona.store');
        $cboRol = array();
        $cboRp = array();
        $rol = Rol::orderBy('nombre', 'asc')->get();
        foreach ($rol as $k => $v) {
            $cboRol = $cboRol + array($v->id => $v->nombre);
        }
        $cboModalidad = array('0' => 'Ninguno');
        $modalidad = Modalidad::orderBy('descripcion', 'asc')->get();
        foreach ($modalidad as $k => $v) {
            $cboModalidad = $cboModalidad + array($v->id => $v->descripcion);
        }
        $cboDieta = array('N'=>'No','S'=>'Si');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento' . $entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar';
        return view($this->folderview . '.mant')->with(compact('persona', 'formData', 'entidad', 'boton', 'listar', 'cboRol', 'cboRp','cboModalidad','cboDieta'));
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
        $reglas     = array(
            'nombres' => 'required|max:50',
            'roles' => 'required'
        );
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre',
            'roles.required'         => 'Debe seleccionar al menos un Rol'
        );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function () use ($request) {
            $person = new Person();
            $person->apellidopaterno = strtoupper($request->input('apellidopaterno'));
            $person->apellidomaterno = strtoupper($request->input('apellidomaterno'));
            $person->nombres = strtoupper($request->input('nombres'));
            $person->dni = strtoupper($request->input('dni'));
            $person->ruc = strtoupper($request->input('ruc'));
            $person->direccion = strtoupper($request->input('direccion'));
            $person->email = strtoupper($request->input('email'));
            $person->modalidad_id = strtoupper($request->input('modalidad_id'));
            $person->telefono = strtoupper($request->input('telefono'));
            $person->autorizado = strtoupper($request->input('autorizado'));
            $person->solicitado = strtoupper($request->input('solicitado'));
            $person->anotaciones = strtoupper($request->input('anotaciones'));
            $person->dscto = strtoupper($request->input('dscto'));
            $person->sexo = strtoupper($request->input('sexo'));
            $person->save();
            $roles = explode(",", $request->input('roles'));
            for ($c = 0; $c < count($roles); $c++) {
                $rolpersona = new Rolpersona();
                $rolpersona->person_id = $person->id;
                $rolpersona->rol_id = $roles[$c];
                $rolpersona->save();
            }
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
        $existe = Libreria::verificarExistencia($id, 'person');
        if ($existe !== true) {
            return $existe;
        }
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $persona = Person::find($id);
        $cboRol = array();
        $rol = Rol::orderBy('nombre', 'asc')->get();
        foreach ($rol as $k => $v) {
            $cboRol = $cboRol + array($v->id => $v->nombre);
        }
        $rolpersona = Rolpersona::where('person_id', '=', $id)->get();
        $cboRp = array();
        foreach ($rolpersona as $key => $value) {
            $cboRp = $cboRp + array($value->rol_id => $value->rol_id);
        }
        $cboModalidad = array('0' => 'Ninguno');
        $modalidad = Modalidad::orderBy('descripcion', 'asc')->get();
        foreach ($modalidad as $k => $v) {
            $cboModalidad = $cboModalidad + array($v->id => $v->descripcion);
        }
        $cboDieta = array('N'=>'No','S'=>'Si');
        $entidad  = 'Persona';
        $formData = array('persona.update', $id);
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento' . $entidad, 'autocomplete' => 'off');
        $boton    = 'Modificar';
        return view($this->folderview . '.mant')->with(compact('persona', 'formData', 'entidad', 'boton', 'listar', 'cboRol', 'cboRp','cboModalidad','cboDieta'));
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
        $existe = Libreria::verificarExistencia($id, 'person');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array(
            'nombres' => 'required|max:50',
            'roles' => 'required'
        );
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre',
            'roles.required'         => 'Debe seleccionar al menos un Rol'
        );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function () use ($request, $id) {
            $person = Person::find($id);
            $person->apellidopaterno = strtoupper($request->input('apellidopaterno'));
            $person->apellidomaterno = strtoupper($request->input('apellidomaterno'));
            $person->nombres = strtoupper($request->input('nombres'));
            $person->dni = strtoupper($request->input('dni'));
            $person->ruc = strtoupper($request->input('ruc'));
            $person->direccion = strtoupper($request->input('direccion'));
            $person->email = strtoupper($request->input('email'));
            $person->telefono = strtoupper($request->input('telefono'));
            $person->modalidad_id = strtoupper($request->input('modalidad_id'));
            $person->autorizado = strtoupper($request->input('autorizado'));
            $person->solicitado = strtoupper($request->input('solicitado'));
            $person->anotaciones = strtoupper($request->input('anotaciones'));
            $person->dscto = strtoupper($request->input('dscto'));
            $person->sexo = strtoupper($request->input('sexo'));
            $person->save();
            $dat = Rolpersona::where('person_id', '=', $person->id)->get();
            foreach ($dat as $key => $value) {
                $value->delete();
            }
            $roles = explode(",", $request->input('roles'));
            for ($c = 0; $c < count($roles); $c++) {
                $rolpersona = new Rolpersona();
                $rolpersona->person_id = $person->id;
                $rolpersona->rol_id = $roles[$c];
                $rolpersona->save();
            }
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
        $existe = Libreria::verificarExistencia($id, 'person');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function () use ($id) {
            $person = Person::find($id);
            $person->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'person');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Person::find($id);
        $entidad  = 'Persona';
        $formData = array('route' => array('persona.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento' . $entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }


    /**
     * Funcion para abrir modal de importar.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $entidad             = "Migracion";
        $migracion             = null;
        $formData            = array('migracion.saveimport');
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento' . $entidad, 'autocomplete' => 'off', 'enctype' => 'multipart/form-data');
        $boton               = 'Importar';
        return view($this->folderview . '.mant2')->with(compact('migracion', 'formData', 'entidad', 'boton', 'listar'));
    }

    /**
     * Funcion para importar.
     *
     * @param  \Illuminate\Http\Request  $request
     * 
     */
    public function saveimport(Request $request)
    {
        $listar     = Libreria::getParam($request->input('listar'), 'NO');
        $validacion = Validator::make(
            $request->all(),
            array(
                'file'           => 'required',
            ),
            array(
                'file.required'  => 'El campo Excel es necesario.',
            )
        );
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function () use ($request) {
            if ($request->hasFile("file")) {
                $file = $request->file("file");
                if ($file->getClientMimeType() == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" || $file->getClientMimeType() == "application/vnd.ms-excel" || $file->getClientMimeType() == "application/octet-stream") {
                    $namefile = time() . $file->getClientOriginalName();
                    $file->move(public_path() . '/imports/', $namefile);
                    $data = Excel::import(new MigracionImport, public_path() . '/imports/' . $namefile);
                } else {
                    echo json_encode(array("file" => array("El archivo debe ser formato xls o xlsx")));
                    exit();
                }
            }
        });
        return is_null($error) ? "OK" : $error;
    }

    public function asistencia(Request $request)
    {
        $resultado = Migracion::whereNotNull('asistencia');
        if($request->input('fechainicial')!=""){
            $resultado = $resultado->where('fecha','>=',$request->input('fechainicial'));
        }
        if($request->input('fechafinal')!=""){
            $resultado = $resultado->where('fecha','<=',$request->input('fechafinal'));
        }

        $resultado = $resultado->select(DB::raw("sum(case when asistencia='SI' then 1 else 0 end) as asistio"),DB::raw("sum(case when asistencia='SI' then 0 else 1 end) as noasistio"))->first();
        $data = "[".$resultado->asistio.",".$resultado->noasistio."]";
        return view($this->folderview . '.asistencia')->with(compact('data'));
    }
}
