<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Hash;
use Validator;
use App\Http\Requests;
use App\User;
use App\Usertype;
use App\Sucursal;
use App\Person;
use App\Configuracion;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{
    protected $folderview      = 'app.usuario';
    protected $tituloAdmin     = 'Usuario';
    protected $tituloRegistrar = 'Registrar usuario';
    protected $tituloModificar = 'Modificar usuario';
    protected $tituloEliminar  = 'Eliminar usuario';
    protected $rutas           = array('create' => 'usuario.create', 
            'edit'   => 'usuario.edit', 
            'delete' => 'usuario.eliminar',
            'search' => 'usuario.buscar',
            'index'  => 'usuario.index',
        );

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        /*$this->middleware('auth');
        define("ASIGNAR_CAJA", Configuracion::where("nombre", "=", "ASIGNAR_CAJA")->first()->valor);*/
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
        $entidad          = 'Usuario';
        $login            = Libreria::getParam($request->input('login'));
        $nombre           = Libreria::getParam($request->input('nombre'));
        $sucursal_id      = Libreria::getParam($request->input('sucursal_id'));
        $resultado        = User::listar($login,$nombre,$sucursal_id);
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Personal / Cliente / Instructor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Login', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Tipo de usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Sucursal', 'numero' => '1');
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
        $user             = Auth::User();
        $entidad          = 'Usuario';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboSucursal      = array('' => 'TODOS') + Sucursal::pluck('nombre', 'id')->all();
        if($user->usertype_id != 1 && $user->usertype_id != 4){
            $cboSucursal = Sucursal::where('sucursal_id','=',$user->sucursal_id)->pluck('nombre', 'id')->all();
        }
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta','cboSucursal'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar         = Libreria::getParam($request->input('listar'), 'NO');
        $entidad        = 'Usuario';
        $usuario        = null;
        $formData       = array('usuario.store');
        $formData       = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton          = 'Registrar'; 
        $cboTipousuario = array('' => 'SELECCIONE') + Usertype::where('id','<>','1')->pluck('name', 'id')->all();
        $cboSucursal    = array('' => 'SELECCIONE') + Sucursal::pluck('nombre', 'id')->all();
        return view($this->folderview.'.mant')->with(compact('usuario', 'formData', 'entidad', 'boton', 'listar', 'cboTipousuario', 'cboSucursal'));
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
        $reglas = array(
            'login'       => 'required|max:200|unique:user,login,NULL,id,deleted_at,NULL',
            'password'    => 'required|max:200',
            'usertype_id' => 'required|integer|exists:usertype,id,deleted_at,NULL',
            'person_id'   => 'required|integer|exists:person,id,deleted_at,NULL',
            'sucursal_id'   => 'required|integer|exists:sucursal,id,deleted_at,NULL',
            );

        $mensajes = array(
            'login.required' => 'Debe ingresar el nombre de usuario.',
            'password.required' => 'Debe ingresar una contraseña.',
            'sucursal_id.required' => 'Debe seleccionar una sucursal.',
            'usertype_id.required' => 'Debe seleccionar un tipo de usuario.',
            'person_id.required' => 'Debe seleccionar una persona.',
            );
        if(ASIGNAR_CAJA == "S" && $request->input('usertype_id') == '2'){
            $reglas["caja_id"] = "required|integer|exists:caja,id,deleted_at,NULL";
            $mensajes["caja_id.required"] = "Debe asignar una caja al usuario.";
        }
        $validacion = Validator::make($request->all(),$reglas,$mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request){
            $usuario               = new User();
            $usuario->login        = $request->input('login');
            $usuario->password     = Hash::make($request->input('password'));
            $usuario->usertype_id  = $request->input('usertype_id');
            $usuario->person_id    = $request->input('person_id');
            $usuario->sucursal_id  = $request->input('sucursal_id');
            $usuario->name  = "";
            $usuario->email  = "";
            $usuario->issuperuser  = "0";
            $usuario->isstaff  = "1";
            $usuario->isactive  = "1";
            $usuario->save();
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
        $existe = Libreria::verificarExistencia($id, 'user');
        if ($existe !== true) {
            return $existe;
        }
        $listar         = Libreria::getParam($request->input('listar'), 'NO');
        $usuario        = User::find($id);
        $entidad        = 'Usuario';
        $formData       = array('usuario.update', $id);
        $formData       = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton          = 'Modificar';
        $cboTipousuario = array('' => 'SELECCIONE') + Usertype::where('id','<>','1')->pluck('name', 'id')->all();
        $cboSucursal    = array('' => 'SELECCIONE') + Sucursal::pluck('nombre', 'id')->all();
        return view($this->folderview.'.mant')->with(compact('usuario', 'formData', 'entidad', 'boton', 'listar', 'cboTipousuario', 'cboSucursal'));
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
        $existe = Libreria::verificarExistencia($id, 'user');
        if ($existe !== true) {
            return $existe;
        }
        // Solo los usuarios del personal llevan persona; los de cliente o instructor ya están vinculados
        $actual     = User::find($id);
        $esPersonal = !($actual->cliente_id > 0) && !($actual->instructor_id > 0);
        $reglas = array(
            'usertype_id' => 'required|integer|exists:usertype,id,deleted_at,NULL',
            'sucursal_id'   => 'required|integer|exists:sucursal,id,deleted_at,NULL',
            );

        $mensajes = array(
            'sucursal_id.required' => 'Debe seleccionar una sucursal.',
            'usertype_id.required' => 'Debe seleccionar un tipo de usuario.',
            'person_id.required' => 'Debe seleccionar una persona.',
            );
        if ($esPersonal) {
            $reglas['person_id'] = 'required|integer|exists:person,id,deleted_at,NULL';
        }
        if(ASIGNAR_CAJA == "S" && $request->input('usertype_id') == '2'){
            $reglas["caja_id"] = "required|integer|exists:caja,id,deleted_at,NULL";
            $mensajes["caja_id.required"] = "Debe asignar una caja al usuario.";
        }
        $validacion = Validator::make($request->all(),$reglas,$mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request, $id, $esPersonal){
            $usuario                 = User::find($id);
            $usuario->usertype_id  = $request->input('usertype_id');
            if ($esPersonal) {
                $usuario->person_id = $request->input('person_id');
            }
            $usuario->sucursal_id  = $request->input('sucursal_id');
            if(strlen($request->input('password'))>0 ){
                $usuario->password     = Hash::make($request->input('password'));
            }
            $usuario->save();
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
        $existe = Libreria::verificarExistencia($id, 'user');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $usuario = User::find($id);
            $usuario->delete();
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
        $existe = Libreria::verificarExistencia($id, 'user');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = User::find($id);
        $entidad  = 'Usuario';
        $formData = array('route' => array('usuario.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function personautocompletar($searching){
        $resultado        = Person::join('rolpersona','rolpersona.person_id','=','person.id')->where('rolpersona.rol_id','=',1)
                            ->where(function($sql) use($searching){
                                $sql->where(DB::raw('CONCAT(apellidopaterno," ",apellidomaterno," ",nombres)'), 'LIKE', '%'.strtoupper($searching).'%')->orWhere('bussinesname', 'LIKE', '%'.strtoupper($searching).'%');
                            })
                            ->whereNull('person.deleted_at')->whereNull('rolpersona.deleted_at')->orderBy('apellidopaterno', 'ASC');
        $list      = $resultado->select('person.*')->get();
        $data = array();
        foreach ($list as $key => $value) {
            $name = '';
            if ($value->bussinesname != null) {
                $name = $value->bussinesname;
            }else{
                $name = $value->apellidopaterno." ".$value->apellidomaterno." ".$value->nombres;
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

    function cambiarcaja(Request $request){
        $productos = Caja::listar(null,$request->input('sucursal_id'));
        $productos = $productos->get();
        $cadena = '';
        foreach ($productos as $key => $value) {
            $cadena = $cadena. "<option value=".$value->id.">".$value->nombre."</option>";
        }
        return json_encode(array("cajas"=>$cadena));
    }
}
