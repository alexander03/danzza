<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Instructor;
use App\User;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use Excel;
use File;

class InstructorController extends Controller
{
    protected $folderview      = 'app.instructor';
    protected $tituloAdmin     = 'Instructor';
    protected $tituloRegistrar = 'Registrar Instructor';
    protected $tituloModificar = 'Modificar Instructor';
    protected $tituloEliminar  = 'Eliminar Instructor';
    protected $rutas           = array(
        'create' => 'instructor.create',
        'edit'   => 'instructor.edit',
        'delete' => 'instructor.eliminar',
        'search' => 'instructor.buscar',
        'index'  => 'instructor.index',
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
        $entidad          = 'Instructor';
        $nombre             = Libreria::getParam($request->input('nombre'));
        $plan             = Libreria::getParam($request->input('plan'));
        $resultado        = Instructor::where(DB::raw('concat(apellidopaterno,\' \',apellidomaterno,\' \',nombres)'), 'LIKE', '%' . strtoupper($nombre) . '%')->orWhere('documento','like','%'.$nombre.'%')
            ->orderBy('apellidopaterno', 'ASC');
        if($plan!=""){
            $resultado->where('plan_id','=',$plan);
        }
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'DNI', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Apellidos y Nombres', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Sobrenombre', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Direccion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha de Nacimiento', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Telefono', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Correo', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Especialidad', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Modalidad', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Tarifa', 'numero' => '1');
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
        $entidad          = 'Instructor';
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
        $entidad  = 'Instructor';
        $instructor = null;
        $usuario  = null;
        $formData = array('instructor.store');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento' . $entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar';
        return view($this->folderview . '.mant')->with(compact('instructor', 'usuario', 'formData', 'entidad', 'boton', 'listar'));
    }

    /** Tipo de usuario de los instructores (ve el mismo panel que el administrador) */
    const USERTYPE_INSTRUCTOR = 6;

    /** Reglas y mensajes del usuario del instructor; en edición la contraseña es opcional */
    private function reglasUsuario($usuarioId = null)
    {
        $unico = 'unique:user,login' . ($usuarioId ? ',' . $usuarioId : '');
        return array(
            'login'    => 'required|max:100|' . $unico,
            'password' => ($usuarioId ? 'nullable' : 'required') . '|min:6|confirmed',
        );
    }

    private function mensajesUsuario()
    {
        return array(
            'login.required'     => 'Debe ingresar un usuario',
            'login.unique'       => 'El usuario ya existe, ingrese otro',
            'password.required'  => 'Debe ingresar una contraseña',
            'password.min'       => 'La contraseña debe tener al menos 6 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
        );
    }

    /** Crea o actualiza el usuario (usertype 6) vinculado al instructor por user.instructor_id */
    private function guardarUsuario($instructorId, Request $request)
    {
        $usuario = User::where('instructor_id', '=', $instructorId)->first();
        if (!$usuario) {
            $usuario = new User();
            $usuario->instructor_id = $instructorId;
        }
        $usuario->login       = $request->input('login');
        $usuario->usertype_id = self::USERTYPE_INSTRUCTOR;
        if ($request->input('password') != '') {
            $usuario->password = bcrypt($request->input('password'));
        }
        $usuario->sucursal_id = 4; // Sucursal "instructores" (no se usa para nada, pero es obligatorio)
        $usuario->save();
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
            'nombres' => 'required|max:500',
            'apellidopaterno' => 'required|max:500',
            'apellidomaterno' => 'required|max:500',
            'documento' => 'required',
            'especialidad' => 'required',
            'modalidad' => 'required',
            'sobrenombre' => 'required|max:500',
        ) + $this->reglasUsuario();
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre',
            'apellidopaterno.required'         => 'Debe ingresar un apellido paterno'
        ) + $this->mensajesUsuario();
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function () use ($request) {
            $person = new Instructor();
            $person->apellidopaterno = strtoupper($request->input('apellidopaterno'));
            $person->apellidomaterno = strtoupper($request->input('apellidomaterno'));
            $person->nombres = strtoupper($request->input('nombres'));
            $person->sobrenombre = strtoupper($request->input('sobrenombre'));
            $person->documento = strtoupper($request->input('documento'));
            $person->direccion = strtoupper($request->input('direccion'));
            $person->correo = strtoupper($request->input('correo'));
            $person->fechanacimiento = $request->input('fechanacimiento');
            $person->modalidad = strtoupper($request->input('modalidad'));
            $person->telefono = strtoupper($request->input('telefono'));
            $person->especialidad = strtoupper($request->input('especialidad'));
            $person->tarifa = $request->input('tarifa');
            $person->save();
            // Cada instructor tiene su usuario para ingresar al panel
            $this->guardarUsuario($person->id, $request);
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
        $existe = Libreria::verificarExistencia($id, 'instructor');
        if ($existe !== true) {
            return $existe;
        }
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $instructor = Instructor::find($id);
        $usuario  = User::where('instructor_id', '=', $id)->first();
        $entidad  = 'Instructor';
        $formData = array('instructor.update', $id);
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento' . $entidad, 'autocomplete' => 'off');
        $boton    = 'Modificar';
        return view($this->folderview . '.mant')->with(compact('instructor', 'usuario', 'formData', 'entidad', 'boton', 'listar'));
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
        $existe = Libreria::verificarExistencia($id, 'instructor');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array(
            'nombres' => 'required|max:500',
            'apellidopaterno' => 'required|max:500',
            'apellidomaterno' => 'required|max:500',
            'documento' => 'required',
            'modalidad' => 'required',
            'sobrenombre' => 'required|max:500',
        );
        // Si aún no tiene usuario, la contraseña es obligatoria para crearlo
        $usuario = User::where('instructor_id', '=', $id)->first();
        $reglas   = $reglas + $this->reglasUsuario($usuario ? $usuario->id : null);
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre',
            'apellidopaterno.required'         => 'Debe ingresar un apellido paterno'
        ) + $this->mensajesUsuario();
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function () use ($request, $id) {
            $person = Instructor::find($id);
            $person->apellidopaterno = strtoupper($request->input('apellidopaterno'));
            $person->apellidomaterno = strtoupper($request->input('apellidomaterno'));
            $person->nombres = strtoupper($request->input('nombres'));
            $person->sobrenombre = strtoupper($request->input('sobrenombre'));
            $person->documento = strtoupper($request->input('documento'));
            $person->direccion = strtoupper($request->input('direccion'));
            $person->correo = strtoupper($request->input('correo'));
            $person->fechanacimiento = $request->input('fechanacimiento');
            $person->modalidad = strtoupper($request->input('modalidad'));
            $person->telefono = strtoupper($request->input('telefono'));
            $person->especialidad = strtoupper($request->input('especialidad'));
            $person->tarifa = $request->input('tarifa');
            $person->save();
            // Contraseña en blanco: se mantiene la actual
            $this->guardarUsuario($id, $request);
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
        $existe = Libreria::verificarExistencia($id, 'instructor');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function () use ($id) {
            $person = Instructor::find($id);
            $person->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'instructor');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Instructor::find($id);
        $entidad  = 'Instructor';
        $formData = array('route' => array('instructor.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento' . $entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function buscarDNI(Request $request)
    {
        $respuesta = array();
        $dni = $request->input('dni');
        $client = new Client();
        $res = $client->get('http://facturae-garzasoft.com/facturacion/buscaInstructor/BuscaInstructor2.php?' . 'dni=' . $dni . '&fe=N&token=qusEj_w7aHEpX');
        if ($res->getStatusCode() == 200) { // 200 OK
            $response_data = $res->getBody()->getContents();
            $respuesta = json_decode($response_data);
        }
        return json_encode($respuesta);
    }

    public function buscarRUC(Request $request)
    {
        $respuesta = array();
        $ruc = $request->input('ruc');
        $client = new Client();
        $res = $client->get('https://comprobante-e.com/facturacion/buscaInstructor/BuscaInstructorRuc.php?fe=N&token=qusEj_w7aHEpX&' . 'ruc=' . $ruc);
        if ($res->getStatusCode() == 200) { // 200 OK
            $response_data = $res->getBody()->getContents();
            $respuesta = json_decode($response_data);
        }
        return json_encode($respuesta);
    }

}
