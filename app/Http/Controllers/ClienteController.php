<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Hash;
use Validator;
use App\Http\Requests;
use App\Cliente;
use App\Plan;
use App\Sucursal;
use App\User;
use App\Horario;
use App\Reserva;
use App\Reservagrupo;
use App\Planpago;
use App\Configuracion;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use Excel;
use File;

class ClienteController extends Controller
{
    protected $folderview      = 'app.cliente';
    protected $tituloAdmin     = 'Cliente';
    protected $tituloRegistrar = 'Registrar Cliente';
    protected $tituloModificar = 'Modificar Cliente';
    protected $tituloEliminar  = 'Eliminar Cliente';
    protected $tituloValidarPago = 'Validar Pago de Plan';
    protected $rutas           = array(
        'create' => 'cliente.create',
        'edit'   => 'cliente.edit',
        'delete' => 'cliente.eliminar',
        'search' => 'cliente.buscar',
        'index'  => 'cliente.index',
        'validarpago' => 'cliente.validarpago',
        'asignarplan' => 'cliente.asignarplan',
    );
    protected $tituloAsignarPlan = 'Asignar Plan';


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
        $entidad          = 'Cliente';
        $nombre             = Libreria::getParam($request->input('nombre'));
        $plan             = Libreria::getParam($request->input('plan'));
        $sucursal         = Libreria::getParam($request->input('sucursal'));
        $situacion         = Libreria::getParam($request->input('situacion'));
        $resultado        = Cliente::where(function ($sql) use($nombre){
                                $sql->where(DB::raw('concat(apellidopaterno,\' \',apellidomaterno,\' \',nombres)'), 'LIKE', '%' . strtoupper($nombre) . '%')
                                    ->orWhere('documento','like','%'.$nombre.'%');
                                })
                                ->orderBy('apellidopaterno', 'ASC');
        if($plan!=""){
            $resultado = $resultado->where('plan_id','=',$plan);
        }
        if($sucursal!=""){
            $resultado = $resultado->where('sucursal_id','=',$sucursal);
        }
        // Pago plan (misma regla que la columna): POR VALIDAR = tiene un pago PENDIENTE;
        // VALIDADO = no tiene pago PENDIENTE y sí tiene al menos un pago VALIDADO
        $pagoPlan = Libreria::getParam($request->input('pagoplan'));
        $existePago = function ($situacionPago) {
            return function ($q) use ($situacionPago) {
                $q->select(DB::raw(1))->from('planpago')
                    ->whereRaw('planpago.cliente_id = cliente.id')
                    ->where('planpago.situacion', '=', $situacionPago);
            };
        };
        if ($pagoPlan == 'POR VALIDAR') {
            $resultado = $resultado->whereExists($existePago('PENDIENTE'));
        } elseif ($pagoPlan == 'VALIDADO') {
            $resultado = $resultado->whereNotExists($existePago('PENDIENTE'))->whereExists($existePago('VALIDADO'));
        }
        if($situacion!=""){
            $resultado = $resultado->where('situacion','like',$situacion);
        }
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'DNI', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Apellidos y Nombres', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Direccion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha Nac.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'DNI Apoderado', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Telefono', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Correo', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Plan', 'numero' => '1');
        //$cabecera[]       = array('valor' => 'Clases', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Clases', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Situacion', 'numero' => '1');
        //$cabecera[]       = array('valor' => 'Sede', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Pago plan', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '3');

        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
        $titulo_validarpago = $this->tituloValidarPago;
        $titulo_asignarplan = $this->tituloAsignarPlan;
        $esAdmin          = $this->esAdminLogueado();
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
            // Clientes de esta página a los que el administrador puede asignar plan
            // (sin plan, sin créditos o con el plan vencido)
            $asignables = array();
            if ($esAdmin) {
                foreach ($lista as $c) {
                    if ($this->puedeAsignarPlan($c)) {
                        $asignables[$c->id] = $this->motivoAsignarPlan($c);
                    }
                }
            }
            return view($this->folderview . '.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta', 'titulo_validarpago', 'titulo_asignarplan', 'esAdmin', 'asignables'));
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
        $entidad          = 'Cliente';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboPlan = array('' => '--Todos--') + Plan::orderBy('nombre','asc')->pluck('nombre','id')->toArray();
        $cboSucursal = array('' => '--Todos--') + Sucursal::orderBy('nombre','asc')->pluck('nombre','id')->toArray();
        $cboSituacion = array('' => '--Todos--','ACTIVO'=>'ACTIVO','BLOQUEADO'=>'BLOQUEADO');
        return view($this->folderview . '.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta','cboPlan','cboSucursal','cboSituacion'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Cliente';
        $cliente = null;
        $formData = array('cliente.store');
        $cboPlan = array('' => '--Seleccione--') + Plan::orderBy('nombre','asc')->pluck('nombre','id')->toArray();
        $cboSituacion = array('ACTIVO'=>'ACTIVO','BLOQUEADO'=>'BLOQUEADO');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento' . $entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar';
        $cboSucursal = array('' => '--Seleccione--') + Sucursal::orderBy('nombre','asc')->pluck('nombre','id')->toArray();
        // Solo el administrador puede registrar el plan y su pago al crear el cliente
        $esAdmin = $this->esAdminLogueado();
        $cboPlanPago = array('' => '--Sin plan--') + $this->opcionesPlanPago();
        $hoy = \Carbon\Carbon::today()->format('Y-m-d');
        return view($this->folderview . '.mant')->with(compact('cliente', 'formData', 'entidad', 'boton', 'listar', 'cboPlan','cboSituacion','cboSucursal', 'esAdmin', 'cboPlanPago', 'hoy'));
    }

    /** ¿Hay un administrador (usertype 1 o 4) con sesión iniciada? */
    private function esAdminLogueado()
    {
        return auth()->check() && in_array((int) auth()->user()->usertype_id, array(1, 4));
    }

    /** Planes para el combo del administrador: "NOMBRE - S/ precio" (precio de promoción si existe) */
    private function opcionesPlanPago()
    {
        $opciones = array();
        foreach (Plan::orderBy('nombre', 'asc')->get() as $p) {
            $opciones[$p->id] = $p->nombre . ' - S/ ' . number_format($this->montoPlan($p), 2);
        }
        return $opciones;
    }

    /** Reglas y mensajes del pago de plan que registra el administrador */
    private function reglasPagoAdmin()
    {
        return array(
            'planpago_plan_id' => 'required|exists:plan,id',
            'operacion'        => 'required|max:30',
            'fechapago'        => 'required|date|before_or_equal:today',
        );
    }

    private function mensajesPagoAdmin()
    {
        return array(
            'planpago_plan_id.required'  => 'Debe seleccionar un plan',
            'planpago_plan_id.exists'    => 'El plan elegido no existe',
            'operacion.required'         => 'Debe ingresar el N.° de operación',
            'operacion.max'              => 'El N.° de operación es demasiado largo',
            'fechapago.required'         => 'Debe ingresar la fecha del pago',
            'fechapago.date'             => 'La fecha del pago no es válida',
            'fechapago.before_or_equal'  => 'La fecha del pago no puede ser futura',
        );
    }

    /** Registra el pago del plan como PENDIENTE; el plan se activa con "Validar Pago" */
    private function registrarPagoPendiente($clienteId, $plan, Request $request)
    {
        $pago = new Planpago();
        $pago->cliente_id = $clienteId;
        $pago->plan_id    = $plan->id;
        $pago->monto      = $this->montoPlan($plan);
        $pago->operacion  = trim($request->input('operacion'));
        $pago->fechapago  = $request->input('fechapago');
        $pago->situacion  = 'PENDIENTE';
        $pago->save();
    }

    /** ¿El cliente no tiene plan asignado? */
    private function sinPlan($cliente)
    {
        return !($cliente->plan_id > 0) || !$cliente->plan;
    }

    /**
     * ¿El administrador puede asignarle un plan? Sin plan, sin créditos o con el plan vencido
     * (misma regla que el botón "Renovar" del cliente). Puede elegir cualquier plan.
     */
    private function puedeAsignarPlan($cliente)
    {
        return $this->sinPlan($cliente) || !is_null($this->motivoRenovacion($cliente));
    }

    /** Texto del motivo para el modal: SIN PLAN / SIN CRÉDITOS / PLAN VENCIDO */
    private function motivoAsignarPlan($cliente)
    {
        if ($this->sinPlan($cliente)) {
            return 'SIN PLAN';
        }
        return $this->motivoRenovacion($cliente) == 'VENCIDO' ? 'PLAN VENCIDO' : 'SIN CLASES';
    }

    /**
     * (Administrador) Modal para asignar un plan a un cliente sin plan: registra el pago PENDIENTE
     */
    public function asignarPlan($id, $listarLuego)
    {
        if (!$this->esAdminLogueado()) {
            return 'No tienes permiso para asignar planes.';
        }
        $cliente = Cliente::find($id);
        if (!$cliente) {
            return 'El cliente no existe.';
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $error = null;
        if (!$this->puedeAsignarPlan($cliente)) {
            $error = 'El cliente tiene el plan ' . $cliente->plan->nombre . ' vigente y con clases disponibles.';
        } elseif ($this->pagoPlanPendiente($cliente->id)) {
            $error = 'El cliente ya tiene un pago de plan por validar.';
        }
        $motivo      = $error ? null : $this->motivoAsignarPlan($cliente);
        $entidad     = 'Cliente';
        $cboPlanPago = array('' => '--Seleccione--') + $this->opcionesPlanPago();
        $hoy         = \Carbon\Carbon::today()->format('Y-m-d');
        $formData    = array('route' => array('cliente.asignarplan.store', $cliente->id), 'method' => 'POST', 'class' => 'form-horizontal', 'id' => 'formMantenimiento' . $entidad, 'autocomplete' => 'off');
        $boton       = 'Registrar pago';
        return view($this->folderview . '.asignar-plan')->with(compact('cliente', 'error', 'motivo', 'cboPlanPago', 'hoy', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function asignarPlanStore(Request $request, $id)
    {
        if (!$this->esAdminLogueado()) {
            return json_encode(array('cliente' => array('No tienes permiso para asignar planes.')));
        }
        $cliente = Cliente::find($id);
        if (!$cliente) {
            return json_encode(array('cliente' => array('El cliente no existe.')));
        }
        if (!$this->puedeAsignarPlan($cliente)) {
            return json_encode(array('cliente' => array('El cliente tiene su plan vigente y con clases disponibles.')));
        }
        if ($this->pagoPlanPendiente($cliente->id)) {
            return json_encode(array('cliente' => array('El cliente ya tiene un pago de plan por validar.')));
        }
        $validacion = Validator::make($request->all(), $this->reglasPagoAdmin(), $this->mensajesPagoAdmin());
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $plan  = Plan::find($request->input('planpago_plan_id'));
        $error = DB::transaction(function () use ($cliente, $plan, $request) {
            $this->registrarPagoPendiente($cliente->id, $plan, $request);
        });
        return is_null($error) ? "OK" : $error;
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
            //'plan_id' => 'required',
            'usuario' => 'required',
            'password' => 'required',
        );
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre',
            'apellidopaterno.required'         => 'Debe ingresar un apellido paterno'
        );
        // Administrador: plan opcional; si elige uno, debe ingresar el pago (queda PENDIENTE de validación)
        $planPago = null;
        if ($this->esAdminLogueado() && $request->input('planpago_plan_id') != '') {
            $planPago = Plan::find($request->input('planpago_plan_id'));
            $reglas   = $reglas + $this->reglasPagoAdmin();
            $mensajes = $mensajes + $this->mensajesPagoAdmin();
        }
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        $validacion->after(function ($validator) use ($request) {
            $this->validarUnicos($validator, $request, null, true);
        });
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function () use ($request, $planPago) {
            $person = new Cliente();
            $person->apellidopaterno = strtoupper($request->input('apellidopaterno'));
            $person->apellidomaterno = strtoupper($request->input('apellidomaterno'));
            $person->nombres = strtoupper($request->input('nombres'));
            $person->documento = strtoupper($request->input('documento'));
            $person->direccion = strtoupper($request->input('direccion'));
            $person->creditos = strtoupper($request->input('creditos')) + 0;
            $person->fechanacimiento = strtoupper($request->input('fechanacimiento'));
            $person->dniapoderado = strtoupper($request->input('dniapoderado'));
            $person->correo = strtoupper($request->input('correo'));
            $person->plan_id = strtoupper($request->input('plan_id')) + 0;
            $person->celular = strtoupper($request->input('celular'));
            $person->clases = strtoupper($request->input('clases')) + 0;
            $person->situacion = strtoupper($request->input('situacion'));
            $person->sucursal_id = $request->input('sucursal_id');
            $person->save();

            $usuario = new User();
            $usuario->cliente_id = $person->id;
            $usuario->login        = $request->input('usuario');
            $usuario->password     = Hash::make($request->input('password'));
            $usuario->usertype_id  = 5;
            $usuario->sucursal_id  = 4;
            $usuario->name  = "";
            $usuario->email  = "";
            $usuario->issuperuser  = "0";
            $usuario->isstaff  = "1";
            $usuario->isactive  = "1";
            $usuario->save();

            // Pago del plan registrado por el administrador: se activa al validarlo con "Validar Pago"
            if ($planPago) {
                $this->registrarPagoPendiente($person->id, $planPago, $request);
            }
        });
        return is_null($error) ? "OK" : $error;
    }

    /**
     * Valida que documento, correo y usuario no estén registrados por otro cliente.
     * Documento y correo se guardan en mayúsculas, por eso se comparan así.
     */
    private function validarUnicos($validator, Request $request, $clienteId = null, $validarUsuario = false)
    {
        $documento = strtoupper(trim($request->input('documento')));
        if ($documento != '' && Cliente::where('documento', $documento)->where('id', '<>', (int) $clienteId)->exists()) {
            $validator->errors()->add('documento', 'El documento ya está registrado');
        }
        $correo = strtoupper(trim($request->input('correo')));
        if ($correo != '' && Cliente::where('correo', $correo)->where('id', '<>', (int) $clienteId)->exists()) {
            $validator->errors()->add('correo', 'El correo ya está registrado');
        }
        $login = trim($request->input('usuario'));
        if ($validarUsuario && $login != '' && User::where('login', $login)->exists()) {
            $validator->errors()->add('usuario', 'El usuario ya está registrado');
        }
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
        $existe = Libreria::verificarExistencia($id, 'cliente');
        if ($existe !== true) {
            return $existe;
        }
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $cliente = Cliente::find($id);
        $cboPlan = array('' => '--Seleccione--') + Plan::orderBy('nombre','asc')->pluck('nombre','id')->toArray();
        $entidad  = 'Cliente';
        $formData = array('cliente.update', $id);
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento' . $entidad, 'autocomplete' => 'off');
        $boton    = 'Modificar';
        $cboSituacion = array('ACTIVO'=>'ACTIVO','BLOQUEADO'=>'BLOQUEADO');
        $cboSucursal = array('' => '--Seleccione--') + Sucursal::orderBy('nombre','asc')->pluck('nombre','id')->toArray();
        return view($this->folderview . '.mant')->with(compact('cliente', 'formData', 'entidad', 'boton', 'listar', 'cboPlan','cboSituacion','cboSucursal'));
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
        $existe = Libreria::verificarExistencia($id, 'cliente');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array(
            'nombres' => 'required|max:500',
            'apellidopaterno' => 'required|max:500',
            'apellidomaterno' => 'required|max:500',
            'documento' => 'required',
            //'plan_id' => 'required',
        );
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre',
            'apellidopaterno.required'         => 'Debe ingresar un apellido paterno'
        );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        $validacion->after(function ($validator) use ($request, $id) {
            $this->validarUnicos($validator, $request, $id, false);
        });
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function () use ($request, $id) {
            $person = Cliente::find($id);
            $person->apellidopaterno = strtoupper($request->input('apellidopaterno'));
            $person->apellidomaterno = strtoupper($request->input('apellidomaterno'));
            $person->nombres = strtoupper($request->input('nombres'));
            $person->documento = strtoupper($request->input('documento'));
            $person->direccion = strtoupper($request->input('direccion'));
            $person->creditos = strtoupper($request->input('creditos')) + 0;
            $person->fechanacimiento = strtoupper($request->input('fechanacimiento'));
            $person->dniapoderado = strtoupper($request->input('dniapoderado'));
            $person->correo = strtoupper($request->input('correo'));
            $person->plan_id = strtoupper($request->input('plan_id')) + 0;
            $person->celular = strtoupper($request->input('celular'));
            $person->clases = strtoupper($request->input('clases')) + 0;
            $person->situacion = strtoupper($request->input('situacion'));
            $person->sucursal_id = $request->input('sucursal_id');
            $person->save();
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
        $existe = Libreria::verificarExistencia($id, 'cliente');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function () use ($id) {
            $person = Cliente::find($id);
            $person->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'cliente');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Cliente::find($id);
        $entidad  = 'Cliente';
        $formData = array('route' => array('cliente.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento' . $entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function buscarDNI(Request $request)
    {
        $respuesta = array();
        $dni = $request->input('dni');
        $client = new Client();
        $res = $client->get('http://facturae-garzasoft.com/facturacion/buscaCliente/BuscaCliente2.php?' . 'dni=' . $dni . '&fe=N&token=qusEj_w7aHEpX');
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
        $res = $client->get('https://comprobante-e.com/facturacion/buscaCliente/BuscaClienteRuc.php?fe=N&token=qusEj_w7aHEpX&' . 'ruc=' . $ruc);
        if ($res->getStatusCode() == 200) { // 200 OK
            $response_data = $res->getBody()->getContents();
            $respuesta = json_decode($response_data);
        }
        return json_encode($respuesta);
    }

    public function horarios(Request $request)
    {
        // Modo "Varios días": disponibilidad del día más lleno del rango
        if ($request->input('fechainicio') != '' && $request->input('fechafin') != '') {
            return response()->json($this->horariosRango($request->input('sede_id'), $request->input('fechainicio'), $request->input('fechafin')));
        }

        $fecha = $request->input('fecha');
        $data = Horario::select('horario.*', 'sucursal.nombre as sucursal',
        // La capacidad se toma de la clase (clase.capacidad), no del horario
        DB::raw('clase.capacidad as capacidad'),DB::raw('concat(horario.horainicio,\' - \',horario.horafin) as hora'),
            // Instructor por su sobrenombre (si no tiene, sus nombres)
            DB::raw("COALESCE(NULLIF(instructor.sobrenombre, ''), instructor.nombres) as instructor"),
            'clase.nombre as clase_nombre')
            ->selectRaw('(clase.capacidad - (select count(*) from reserva where reserva.deleted_at is null and reserva.horario_id = horario.id and reserva.fecha = ?)) as ocupacion', [$fecha])
            ->selectRaw('(select count(*) from reserva where reserva.deleted_at is null and reserva.horario_id = horario.id and reserva.fecha = ?) as ocupados', [$fecha])
            ->join('sucursal', 'horario.sucursal_id', '=', 'sucursal.id')
            ->leftJoin('clase', 'horario.clase_id', '=', 'clase.id')
            ->leftJoin('instructor', 'horario.instructor_id', '=', 'instructor.id')
            ->where('horario.situacion', '=', 'DISPONIBLE')
            ->where('horario.sucursal_id', '=', $request->input('sede_id'))
            ->orderBy('horario.horainicio', 'asc')
            ->get();
        // Solo los horarios que se dictan ese día de la semana y dentro de su vigencia
        // y que aún no terminaron (si la fecha es hoy, se ocultan los que ya pasaron su hora fin)
        $data = $data->filter(function ($h) use ($fecha) {
            return $this->horarioCorreEn($h, $fecha) && !$this->horarioTermino($h, $fecha);
        })->values();
        foreach ($data as $h) {
            $h->vigenciaTexto = $this->vigenciaTexto($h);
        }
        return response()->json($data);
    }

    /** Capacidad (lugares) del horario: se toma de su clase (clase.capacidad); 0 si no tiene */
    private function capacidadHorario($horario)
    {
        $clase = $horario->clase;
        return $clase ? (int) $clase->capacidad : 0;
    }

    /** Días de la semana del horario (1=Lunes ... 7=Domingo); vacío = todos */
    private function diasHorario($horario)
    {
        $dias = $horario->dias != '' ? array_map('intval', explode(',', $horario->dias)) : array();
        return count($dias) ? $dias : array(1, 2, 3, 4, 5, 6, 7);
    }

    /** ¿El horario se dicta en esa fecha? (día de la semana y dentro de su rango de vigencia) */
    private function horarioCorreEn($horario, $fecha)
    {
        $dia = \Carbon\Carbon::parse($fecha)->format('Y-m-d');
        if ($horario->fechainicio && $dia < \Carbon\Carbon::parse($horario->fechainicio)->format('Y-m-d')) {
            return false;
        }
        if ($horario->fechafin && $dia > \Carbon\Carbon::parse($horario->fechafin)->format('Y-m-d')) {
            return false;
        }
        return in_array((int) \Carbon\Carbon::parse($fecha)->format('N'), $this->diasHorario($horario));
    }

    /** ¿La clase de ese día ya terminó? (fecha + hora fin del horario ya pasó) */
    private function horarioTermino($horario, $fecha)
    {
        $fin = \Carbon\Carbon::parse(\Carbon\Carbon::parse($fecha)->format('Y-m-d') . ' ' . $horario->horafin);
        return $fin->lte(\Carbon\Carbon::now());
    }

    /** Texto de la vigencia del horario, ej. "Vigente del 01/10 al 15/10" */
    private function vigenciaTexto($horario)
    {
        if (!$horario->fechainicio || !$horario->fechafin) {
            return '';
        }
        return 'Vigente del ' . \Carbon\Carbon::parse($horario->fechainicio)->format('d/m')
            . ' al ' . \Carbon\Carbon::parse($horario->fechafin)->format('d/m');
    }

    /** Fechas (Y-m-d) del rango en que se dicta el horario (sin los días cuya clase ya terminó) */
    private function fechasDelHorario($horario, $desde, $hasta)
    {
        $fechas = array();
        $fin = \Carbon\Carbon::parse($hasta);
        for ($d = \Carbon\Carbon::parse($desde); $d->lte($fin); $d->addDay()) {
            $fecha = $d->format('Y-m-d');
            if ($this->horarioCorreEn($horario, $fecha) && !$this->horarioTermino($horario, $fecha)) {
                $fechas[] = $fecha;
            }
        }
        return $fechas;
    }

    public function lugares(Request $request)
    {
        $data = Horario::select('sucursal.id', 'sucursal.nombre as sucursal',
            // La capacidad se toma de la clase (clase.capacidad), no del horario
            DB::raw('clase.capacidad as capacidad'),
            // Instructor por su sobrenombre (si no tiene, sus nombres)
            DB::raw("COALESCE(NULLIF(instructor.sobrenombre, ''), instructor.nombres) as instructor"))
            ->join('instructor', 'horario.instructor_id', '=', 'instructor.id')
            ->leftJoin('clase', 'horario.clase_id', '=', 'clase.id')
            ->join('sucursal', 'horario.sucursal_id', '=', 'sucursal.id')
            ->where('horario.id', '=', $request->input('horario_id'))
            ->first();

        if (!$data) {
            return response()->json(['capacidad' => 0, 'instructor' => null, 'ocupados' => []], 404);
        }

        // Todas las ubicaciones ya reservadas para ese horario y fecha
        // (en un rango, un lugar está ocupado si lo está en cualquier día del rango)
        $esRango = $request->input('fechainicio') != '' && $request->input('fechafin') != '';
        $consulta = Reserva::where('horario_id', '=', $request->input('horario_id'))
                ->select('ubicacion', 'fecha')
                ->whereNotNull('ubicacion')
                ->whereNull('deleted_at')
                ->orderBy('fecha', 'asc');
        if ($esRango) {
            $consulta->whereBetween('fecha', [$request->input('fechainicio'), $request->input('fechafin')]);
        } else {
            $consulta->where('fecha', '=', $request->input('fecha'));
        }
        $filas = collect($consulta->get());
        $ocupados = $filas
            ->pluck('ubicacion')
            ->map(function ($ubicacion) {
                return (int) $ubicacion;
            })
            ->unique()
            ->values();

        // En un rango: días en que cada lugar está ocupado, ej. { "5": ["Mié 8 de Oct.", "Jue 9 de Oct."] }
        $ocupadosDias = array();
        if ($esRango) {
            foreach ($filas as $fila) {
                $texto = $this->textoDia($fila->fecha);
                $lugar = (string) (int) $fila->ubicacion;
                if (!isset($ocupadosDias[$lugar]) || !in_array($texto, $ocupadosDias[$lugar])) {
                    $ocupadosDias[$lugar][] = $texto;
                }
            }
        }

        $data = $data->toArray();
        $data['ocupados'] = $ocupados;
        $data['ocupadosDias'] = (object) $ocupadosDias;

        return response()->json($data);
    }

    public function storeReserva(Request $request)
    {
        $reglas     = array(
            'horario_id' => 'required',
            'fecha' => 'required|date',
            'ubicacion' => 'nullable|integer|min:1|max:100',
        );
        $mensajes = array(
            'horario_id.required' => 'Debe seleccionar un horario',
            'fecha.required' => 'Debe seleccionar una fecha',
            'fecha.date' => 'La fecha no es válida',
            'ubicacion.integer' => 'La ubicación debe ser un número entero',
            'ubicacion.min' => 'La ubicación debe ser al menos 1',
            'ubicacion.max' => 'La ubicación no puede ser mayor a 100',
        );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return response()->json(['ok' => false, 'mensaje' => $validacion->messages()->first()], 422);
        }

        $lugarTomado = Reserva::where('horario_id', '=', $request->input('horario_id'))
            ->where('fecha', '=', $request->input('fecha'))
            ->where('ubicacion', '=', $request->input('ubicacion'))
            ->whereNull('deleted_at')
            ->exists();
        if ($lugarTomado) {
            return response()->json(['ok' => false, 'mensaje' => 'El lugar ya fue tomado, elige otro.']);
        }

        // El cliente solo puede tener una reserva por día en la misma sede con el mismo instructor
        $horario = Horario::find($request->input('horario_id'));
        if (!$horario) {
            return response()->json(['ok' => false, 'mensaje' => 'El horario no existe.'], 404);
        }
        if (!$this->horarioCorreEn($horario, $request->input('fecha'))) {
            $vigencia = $this->vigenciaTexto($horario);
            return response()->json(['ok' => false, 'mensaje' => 'Este horario no se dicta el ' . $this->textoDia($request->input('fecha'))
                . ($vigencia ? ' (' . $vigencia . ')' : '') . ' Elige otro día u horario.']);
        }
        if ($this->horarioTermino($horario, $request->input('fecha'))) {
            return response()->json(['ok' => false, 'mensaje' => 'Este horario ya terminó el ' . $this->textoDia($request->input('fecha')) . '. Elige otro día u horario.']);
        }
        // Capacidad de la clase: lugar válido y clase no llena
        $capacidad = $this->capacidadHorario($horario);
        if ($capacidad > 0) {
            if ((int) $request->input('ubicacion') > $capacidad) {
                return response()->json(['ok' => false, 'mensaje' => 'El lugar N.° ' . $request->input('ubicacion') . ' no existe en esta clase (capacidad ' . $capacidad . ').']);
            }
            $ocupados = Reserva::where('horario_id', '=', $horario->id)
                ->where('fecha', '=', $request->input('fecha'))
                ->whereNull('deleted_at')
                ->count();
            if ($ocupados >= $capacidad) {
                return response()->json(['ok' => false, 'mensaje' => 'La clase está llena para ese día. Elige otro horario.']);
            }
        }
        $yaReservo = Reserva::join('horario', 'reserva.horario_id', '=', 'horario.id')
            ->where('reserva.cliente_id', '=', auth()->user()->cliente_id)
            ->where('reserva.fecha', '=', $request->input('fecha'))
            ->where('horario.sucursal_id', '=', $horario->sucursal_id)
            ->where('horario.instructor_id', '=', $horario->instructor_id)
            ->whereNull('reserva.deleted_at')
            ->exists();
        if ($yaReservo) {
            return response()->json(['ok' => false, 'mensaje' => 'Ya tienes una reserva para este día en esta sede con el mismo instructor. Solo puedes reservar una vez por día.']);
        }

        // Cada reserva consume 1 crédito del plan
        if ($this->creditosCliente() < 1) {
            return response()->json(['ok' => false, 'sinCreditos' => true, 'mensaje' => 'No tienes créditos disponibles. Renueva o cambia tu plan para seguir reservando.']);
        }

        $obj = null;
        $error = DB::transaction(function () use ($request, &$obj) {
            // Descuento atómico: solo si aún tiene el crédito (evita doble reserva simultánea)
            if (!$this->descontarCreditos(1)) {
                return 'SIN_CREDITOS';
            }
            $obj = new Reserva();
            $obj->codigo = $this->generateUniqueCode(); // Asumiendo que tienes una función para generar un código único
            $obj->fecha = strtoupper($request->input('fecha'));
            $obj->cliente_id = auth()->user()->cliente_id; // Asumiendo que el cliente está autenticado
            $obj->horario_id = strtoupper($request->input('horario_id'));
            $obj->sucursal_id = Horario::find($request->input('horario_id'))->sucursal_id; // Obtener la sucursal del horario
            $obj->ubicacion = strtoupper($request->input('ubicacion'));
            $obj->situacion = 'RESERVADO';
            $obj->save();
        });

        if ($error === 'SIN_CREDITOS') {
            return response()->json(['ok' => false, 'sinCreditos' => true, 'mensaje' => 'No tienes créditos disponibles. Renueva o cambia tu plan para seguir reservando.']);
        }
        if ($error || !$obj) {
            return response()->json(['ok' => false, 'mensaje' => 'Error al crear la reserva'], 500);
        }

        // La reserva no genera pago: el pago se hace al activar/cambiar el plan
        $restantes = $this->creditosCliente();
        return response()->json([
            'ok'       => true,
            'mensaje'  => 'Reserva registrada. Te ' . ($restantes == 1 ? 'queda 1 crédito' : 'quedan ' . $restantes . ' créditos') . '.',
            'creditos' => $restantes,
            'id'       => $obj->id,
            'redirect' => route('cliente.reservas'),
        ]);
    }

    /**
     * Horarios de una sede para un rango de fechas. "ocupados" es el del día más lleno del rango.
     */
    private function horariosRango($sedeId, $desde, $hasta)
    {
        $horarios = Horario::select('horario.*', 'sucursal.nombre as sucursal',
            // La capacidad se toma de la clase (clase.capacidad), no del horario
            DB::raw('clase.capacidad as capacidad'), DB::raw('concat(horario.horainicio,\' - \',horario.horafin) as hora'),
            DB::raw("COALESCE(NULLIF(instructor.sobrenombre, ''), instructor.nombres) as instructor"),
            'clase.nombre as clase_nombre')
            ->join('sucursal', 'horario.sucursal_id', '=', 'sucursal.id')
            ->leftJoin('clase', 'horario.clase_id', '=', 'clase.id')
            ->leftJoin('instructor', 'horario.instructor_id', '=', 'instructor.id')
            ->where('horario.situacion', '=', 'DISPONIBLE')
            ->where('horario.sucursal_id', '=', $sedeId)
            ->orderBy('horario.horainicio', 'asc')
            ->get();

        $conteos = Reserva::select('horario_id', 'fecha', DB::raw('count(*) as total'))
            ->whereIn('horario_id', $horarios->pluck('id')->all())
            ->whereBetween('fecha', [$desde, $hasta])
            ->whereNull('deleted_at')
            ->groupBy('horario_id', 'fecha')
            ->get();

        $abrev = array(1 => 'Lun', 2 => 'Mar', 3 => 'Mié', 4 => 'Jue', 5 => 'Vie', 6 => 'Sáb', 7 => 'Dom');
        foreach ($horarios as $h) {
            $h->ocupados  = (int) $conteos->filter(function ($c) use ($h) {
                return $c->horario_id == $h->id;
            })->max('total');
            $h->ocupacion = $h->capacidad - $h->ocupados;
            // Días del rango en que se dicta (solo esos se reservan y consumen crédito)
            $fechas = $this->fechasDelHorario($h, $desde, $hasta);
            $h->diasEnRango  = count($fechas);
            $h->fechasRango  = array_map(function ($f) { return $this->textoDia($f); }, $fechas);
            $diasSemana = $this->diasHorario($h);
            $h->vigenciaTexto = $this->vigenciaTexto($h);
            $h->diasTexto = count($diasSemana) == 7 ? 'Todos los días'
                : implode(' · ', array_map(function ($d) use ($abrev) { return $abrev[$d]; }, $diasSemana));
        }
        // Se ocultan los horarios que no se dictan ningún día del rango
        return $horarios->filter(function ($h) {
            return $h->diasEnRango > 0;
        })->values();
    }

    /** Ej. "Lun 6 de Oct." */
    private function textoDia($fecha)
    {
        $dias  = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Set', 'Oct', 'Nov', 'Dic'];
        $f = \Carbon\Carbon::parse($fecha);
        return $dias[$f->dayOfWeek] . ' ' . $f->day . ' de ' . $meses[$f->month - 1] . '.';
    }

    /**
     * Reserva el mismo horario y lugar en los días del rango en que se dicta el horario
     * (máx. Configuracion id 1 "valor" días desde hoy). Si alguno de esos días no se puede reservar, no se guarda nada.
     */
    public function storeReservaRango(Request $request)
    {
        $reglas = array(
            'horario_id'  => 'required',
            'fechainicio' => 'required|date_format:Y-m-d',
            'fechafin'    => 'required|date_format:Y-m-d|after_or_equal:fechainicio',
            'ubicacion'   => 'required|integer|min:1|max:100',
        );
        $mensajes = array(
            'horario_id.required'     => 'Debe seleccionar un horario',
            'fechainicio.required'    => 'Debe seleccionar la fecha de inicio',
            'fechafin.required'       => 'Debe seleccionar la fecha de fin',
            'fechafin.after_or_equal' => 'La fecha de fin no puede ser anterior a la de inicio',
            'ubicacion.required'      => 'Debe seleccionar un lugar',
        );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return response()->json(['ok' => false, 'mensaje' => $validacion->messages()->first()], 422);
        }

        $hoy    = \Carbon\Carbon::today();
        $inicio = \Carbon\Carbon::parse($request->input('fechainicio'));
        $fin    = \Carbon\Carbon::parse($request->input('fechafin'));
        if ($inicio->lt($hoy)) {
            return response()->json(['ok' => false, 'mensaje' => 'La fecha de inicio no puede ser anterior a hoy.']);
        }
        // Máximo de días del rango configurable por el administrador (Configuracion id 1, campo valor)
        $maxDias = $this->diasMaximoRango();
        if ($fin->gt($hoy->copy()->addDays($maxDias - 1))) {
            return response()->json(['ok' => false, 'mensaje' => 'Solo puedes reservar hasta ' . $maxDias . ' días desde hoy.']);
        }

        $horario = Horario::find($request->input('horario_id'));
        if (!$horario) {
            return response()->json(['ok' => false, 'mensaje' => 'El horario no existe.'], 404);
        }

        $clienteId = auth()->user()->cliente_id;
        $ubicacion = (int) $request->input('ubicacion');
        $capacidad = $this->capacidadHorario($horario);
        if ($capacidad > 0 && $ubicacion > $capacidad) {
            return response()->json(['ok' => false, 'mensaje' => 'El lugar N.° ' . $ubicacion . ' no existe en esta clase (capacidad ' . $capacidad . ').']);
        }
        // Solo se reservan los días del rango en que se dicta el horario (ej. Lun, Vie y Sáb)
        $fechas    = $this->fechasDelHorario($horario, $inicio->format('Y-m-d'), $fin->format('Y-m-d'));
        $fallas    = array();
        if (!count($fechas)) {
            $vigencia = $this->vigenciaTexto($horario);
            return response()->json(['ok' => false, 'mensaje' => 'Este horario no se dicta ningún día del rango elegido'
                . ($vigencia ? ' (' . $vigencia . ')' : '') . '.']);
        }
        foreach ($fechas as $fecha) {
            $empezo = \Carbon\Carbon::parse($fecha . ' ' . $horario->horainicio)->lte(\Carbon\Carbon::now());
            $lugarTomado = Reserva::where('horario_id', '=', $horario->id)
                ->where('fecha', '=', $fecha)
                ->where('ubicacion', '=', $ubicacion)
                ->whereNull('deleted_at')
                ->exists();
            $ocupados = Reserva::where('horario_id', '=', $horario->id)
                ->where('fecha', '=', $fecha)
                ->whereNull('deleted_at')
                ->count();
            // Misma regla que la reserva de un día: una por día en la misma sede con el mismo instructor
            $yaReservo = Reserva::join('horario', 'reserva.horario_id', '=', 'horario.id')
                ->where('reserva.cliente_id', '=', $clienteId)
                ->where('reserva.fecha', '=', $fecha)
                ->where('horario.sucursal_id', '=', $horario->sucursal_id)
                ->where('horario.instructor_id', '=', $horario->instructor_id)
                ->whereNull('reserva.deleted_at')
                ->exists();

            if ($empezo) {
                $fallas[] = $this->textoDia($fecha) . ' la clase ya empezó';
            } elseif ($yaReservo) {
                $fallas[] = $this->textoDia($fecha) . ' ya tienes una reserva';
            } elseif ($capacidad > 0 && $ocupados >= $capacidad) {
                $fallas[] = $this->textoDia($fecha) . ' la clase está llena';
            } elseif ($lugarTomado) {
                $fallas[] = $this->textoDia($fecha) . ' el lugar N.° ' . $ubicacion . ' está ocupado';
            }
        }
        if (count($fallas)) {
            return response()->json(['ok' => false, 'mensaje' => 'No se pudo reservar el rango: ' . implode('; ', $fallas) . '.']);
        }

        // Cada día del rango consume 1 crédito
        $necesarios = count($fechas);
        $disponibles = $this->creditosCliente();
        if ($disponibles < $necesarios) {
            return response()->json(['ok' => false, 'sinCreditos' => true, 'mensaje' => 'Este rango necesita ' . $necesarios . ' créditos y tienes ' . $disponibles . '. Elige menos días o renueva tu plan.']);
        }

        $grupo = null;
        $error = DB::transaction(function () use ($horario, $clienteId, $ubicacion, $fechas, &$grupo) {
            if (!$this->descontarCreditos(count($fechas))) {
                return 'SIN_CREDITOS';
            }
            $grupo = new Reservagrupo();
            $grupo->codigo      = strtoupper(uniqid('RNG-'));
            $grupo->cliente_id  = $clienteId;
            $grupo->horario_id  = $horario->id;
            $grupo->sucursal_id = $horario->sucursal_id;
            $grupo->fechainicio = $fechas[0];
            $grupo->fechafin    = $fechas[count($fechas) - 1];
            $grupo->dias        = count($fechas);
            $grupo->ubicacion   = $ubicacion;
            $grupo->situacion   = 'RESERVADO';
            $grupo->save();

            foreach ($fechas as $fecha) {
                $obj = new Reserva();
                $obj->codigo          = $this->generateUniqueCode();
                $obj->reservagrupo_id = $grupo->id;
                $obj->fecha           = $fecha;
                $obj->cliente_id      = $clienteId;
                $obj->horario_id      = $horario->id;
                $obj->sucursal_id     = $horario->sucursal_id;
                $obj->ubicacion       = $ubicacion;
                $obj->situacion       = 'RESERVADO';
                $obj->save();
            }
        });

        if ($error === 'SIN_CREDITOS') {
            return response()->json(['ok' => false, 'sinCreditos' => true, 'mensaje' => 'No tienes créditos suficientes para este rango.']);
        }
        if ($error || !$grupo) {
            return response()->json(['ok' => false, 'mensaje' => 'Error al crear la reserva'], 500);
        }

        $restantes = $this->creditosCliente();
        return response()->json([
            'ok'       => true,
            'mensaje'  => 'Reservaste ' . count($fechas) . (count($fechas) == 1 ? ' día' : ' días') . ' (' . implode(', ', array_map(function ($f) { return $this->textoDia($f); }, $fechas)) . '). Te ' . ($restantes == 1 ? 'queda 1 crédito' : 'quedan ' . $restantes . ' créditos') . '.',
            'creditos' => $restantes,
            'id'       => $grupo->id,
            'redirect' => route('cliente.reservas'),
        ]);
    }

    /**
     * Máximo de días (desde hoy) para reservar en "Varios días".
     * Lo configura el administrador en Configuracion id 1 (campo valor); por defecto 30.
     */
    public static function diasMaximoRango()
    {
        $configuracion = Configuracion::find(1);
        $valor = $configuracion ? (int) $configuracion->valor : 0;
        return $valor > 0 ? $valor : 30;
    }

    /** Créditos disponibles del cliente autenticado */
    private function creditosCliente()
    {
        $cliente = Cliente::find(auth()->user()->cliente_id);
        return $cliente ? (int) $cliente->creditos : 0;
    }

    /** Descuenta créditos solo si alcanzan (true si se descontó) */
    private function descontarCreditos($cantidad)
    {
        return Cliente::where('id', '=', auth()->user()->cliente_id)
            ->where('creditos', '>=', $cantidad)
            ->decrement('creditos', $cantidad) > 0;
    }

    /** Devuelve créditos al cancelar a tiempo */
    private function devolverCreditos($cantidad)
    {
        if ($cantidad > 0) {
            Cliente::where('id', '=', auth()->user()->cliente_id)->increment('creditos', $cantidad);
        }
    }

    private function grupoDelCliente($id)
    {
        return Reservagrupo::where('id', '=', $id)
            ->where('cliente_id', '=', auth()->user()->cliente_id)
            ->whereNull('deleted_at')
            ->first();
    }

    /**
     * Cancela todos los días del rango que aún se pueden cancelar (hasta 40 min antes de cada clase).
     */
    public function cancelarRango($id)
    {
        $grupo = $this->grupoDelCliente($id);
        if (!$grupo) {
            return response()->json(['ok' => false, 'mensaje' => 'La reserva no existe.'], 404);
        }

        $horario = Horario::find($grupo->horario_id);
        $limite  = \Carbon\Carbon::now()->addMinutes(40);
        $cancelados = 0;
        DB::transaction(function () use ($grupo, $horario, $limite, &$cancelados) {
            $reservas = Reserva::where('reservagrupo_id', '=', $grupo->id)->whereNull('deleted_at')->get();
            foreach ($reservas as $reserva) {
                $inicio = \Carbon\Carbon::parse(\Carbon\Carbon::parse($reserva->fecha)->format('Y-m-d') . ' ' . $horario->horainicio);
                if ($inicio->gt($limite)) {
                    $reserva->deleted_at = \Carbon\Carbon::now();
                    $reserva->save();
                    $cancelados++;
                }
            }
            // Se devuelve 1 crédito por cada día cancelado a tiempo
            $this->devolverCreditos($cancelados);
            // Si ya no queda ningún día, el rango completo queda cancelado
            if (!Reserva::where('reservagrupo_id', '=', $grupo->id)->whereNull('deleted_at')->exists()) {
                $grupo->situacion  = 'CANCELADO';
                $grupo->deleted_at = \Carbon\Carbon::now();
                $grupo->save();
            }
        });

        if ($cancelados == 0) {
            return response()->json(['ok' => false, 'mensaje' => 'No queda ningún día del rango que se pueda cancelar (hasta 40 minutos antes de cada clase).']);
        }

        return response()->json(['ok' => true, 'mensaje' => 'Se cancelaron ' . $cancelados . ' ' . ($cancelados == 1 ? 'día' : 'días') . ' del rango y se devolvieron ' . $cancelados . ' ' . ($cancelados == 1 ? 'crédito' : 'créditos') . '.']);
    }

    private function reservaDelCliente($id)
    {
        return Reserva::where('id', '=', $id)
            ->where('cliente_id', '=', auth()->user()->cliente_id)
            ->whereNull('deleted_at')
            ->first();
    }

    /**
     * Mis reservas: lista de reservas del cliente autenticado.
     */
    public function reservas()
    {
        $reservas = Reserva::where('reserva.cliente_id', '=', auth()->user()->cliente_id)
            ->join('horario', 'reserva.horario_id', '=', 'horario.id')
            ->join('sucursal', 'reserva.sucursal_id', '=', 'sucursal.id')
            ->leftJoin('instructor', 'horario.instructor_id', '=', 'instructor.id')
            ->select('reserva.id', 'reserva.codigo', 'reserva.fecha', 'reserva.ubicacion',
                'reserva.situacion', 'reserva.reservagrupo_id',
                'horario.horainicio', 'horario.horafin', 'sucursal.nombre as sede',
                DB::raw("COALESCE(NULLIF(instructor.sobrenombre, ''), instructor.nombres) as instructor"),
                'reserva.asistencia')
            ->whereNull('reserva.deleted_at')
            ->orderBy('reserva.fecha', 'desc')
            ->orderBy('horario.horainicio', 'desc')
            ->get();

        $ahora = \Carbon\Carbon::now();
        $proximas = array();
        $historial = array();
        foreach ($reservas as $r) {
            $inicio = \Carbon\Carbon::parse(\Carbon\Carbon::parse($r->fecha)->format('Y-m-d') . ' ' . $r->horainicio);
            // Se puede cancelar hasta 40 minutos antes del inicio (términos y condiciones)
            $r->cancelable = $ahora->lt($inicio->copy()->subMinutes(40));
            if ($inicio->gte($ahora)) {
                $proximas[] = $r;
            } else {
                $historial[] = $r;
            }
        }
        // Las próximas, de la más cercana a la más lejana
        $proximas = array_reverse($proximas);

        // Días que son parte de un rango: texto del rango y un solo botón "Cancelar rango" (en el primer día próximo del rango)
        $grupoIds = collect($reservas)->pluck('reservagrupo_id')->filter()->unique()->values()->all();
        $grupos = Reservagrupo::whereIn('id', $grupoIds)->get()->keyBy('id');
        $conBoton = array();
        foreach (array_merge($proximas, $historial) as $r) {
            $r->grupo = $r->reservagrupo_id ? $grupos->get($r->reservagrupo_id) : null;
            $r->mostrarCancelarRango = false;
            if ($r->grupo) {
                $r->cancelable = false;
                $r->rangoTexto = $this->textoDia($r->grupo->fechainicio) . ' al ' . $this->textoDia($r->grupo->fechafin)
                    . ' (' . $r->grupo->dias . ' días)';
            }
        }
        foreach ($proximas as $r) {
            if ($r->grupo && !isset($conBoton[$r->grupo->id])) {
                $conBoton[$r->grupo->id] = true;
                $r->mostrarCancelarRango = true;
            }
        }

        $creditos = $this->creditosCliente();

        return view('app.cliente.list-reservas', compact('proximas', 'historial', 'creditos'));
    }

    /**
     * Cancela (elimina lógicamente) una reserva del cliente hasta 40 min antes.
     */
    public function cancelarReserva($id)
    {
        $reserva = $this->reservaDelCliente($id);
        if (!$reserva) {
            return response()->json(['ok' => false, 'mensaje' => 'La reserva no existe.'], 404);
        }
        if ($reserva->reservagrupo_id) {
            return response()->json(['ok' => false, 'mensaje' => 'Esta reserva es parte de un rango de fechas; debes cancelar el rango completo.']);
        }

        $horario = Horario::find($reserva->horario_id);
        $inicio = \Carbon\Carbon::parse(\Carbon\Carbon::parse($reserva->fecha)->format('Y-m-d') . ' ' . $horario->horainicio);
        if (\Carbon\Carbon::now()->gte($inicio->copy()->subMinutes(40))) {
            return response()->json(['ok' => false, 'mensaje' => 'Solo puedes cancelar hasta 40 minutos antes del inicio de la clase.']);
        }

        DB::transaction(function () use ($reserva) {
            $reserva->deleted_at = \Carbon\Carbon::now();
            $reserva->save();
            // Cancelada a tiempo: se devuelve el crédito
            $this->devolverCreditos(1);
        });

        return response()->json(['ok' => true, 'mensaje' => 'Reserva cancelada. Se devolvió 1 crédito.']);
    }

    /**
     * Planes: el cliente elige (activa) un plan o cambia su plan actual.
     */
    public function planes()
    {
        $cliente = Cliente::find(auth()->user()->cliente_id);
        if (!$cliente) {
            abort(403);
        }
        $planes = Plan::where('situacion', '=', 'ACTIVO')->orderBy('orden', 'asc')->get();
        $pendiente = $this->pagoPlanPendiente($cliente->id);
        // Último pago (para avisar si fue rechazado)
        $ultimoPago = Planpago::where('cliente_id', '=', $cliente->id)->orderBy('id', 'desc')->first();
        // Renovar el plan actual: sin créditos o con la vigencia vencida
        $renovacion = $this->motivoRenovacion($cliente);

        return view('app.cliente.planes', compact('cliente', 'planes', 'pendiente', 'ultimoPago', 'renovacion'));
    }

    /**
     * El cliente elige un plan (primera vez o cambio): lo envía a pagar.
     * El plan se asigna recién cuando el administrador valida el pago.
     */
    public function planesStore(Request $request)
    {
        $cliente = Cliente::find(auth()->user()->cliente_id);
        if (!$cliente) {
            abort(403);
        }
        $plan = Plan::find($request->input('plan_id'));
        if (!$plan) {
            return redirect()->route('cliente.planes')->with('error', 'Debe seleccionar un plan válido.');
        }
        // El mismo plan solo se puede volver a pagar para renovarlo (sin créditos o vencido)
        if ($cliente->plan_id == $plan->id && !$this->motivoRenovacion($cliente)) {
            return redirect()->route('cliente.planes')->with('error', 'Ese plan ya es tu plan actual.');
        }
        if ($this->pagoPlanPendiente($cliente->id)) {
            return redirect()->route('cliente.planes')->with('error', 'Ya tienes un pago en validación. Espera a que sea validado para elegir otro plan.');
        }

        return redirect()->route('cliente.planes.pagar', $plan->id);
    }

    /**
     * Motivo por el que el cliente puede renovar su plan actual, o null si no puede:
     * 'SIN_CREDITOS' (consumió todos sus créditos) o 'VENCIDO' (pasó la fecha de vencimiento).
     */
    private function motivoRenovacion($cliente)
    {
        if (!$cliente->plan) {
            return null;
        }
        if ((int) $cliente->creditos < 1) {
            return 'SIN_CREDITOS';
        }
        $vigente = Planpago::where('cliente_id', '=', $cliente->id)
            ->where('plan_id', '=', $cliente->plan_id)
            ->where('situacion', '=', 'VALIDADO')
            ->orderBy('id', 'desc')
            ->first();
        if ($vigente && $vigente->fechavencimiento && \Carbon\Carbon::parse($vigente->fechavencimiento)->lt(\Carbon\Carbon::today())) {
            return 'VENCIDO';
        }
        return null;
    }

    /**
     * Pantalla de pago (Yape) del plan elegido. Si ya hay un pago en validación, se muestra ese pago.
     */
    public function planesPagar($planId)
    {
        $cliente = Cliente::find(auth()->user()->cliente_id);
        if (!$cliente) {
            abort(403);
        }
        $pago = $this->pagoPlanPendiente($cliente->id);
        $plan = $pago ? $pago->plan : Plan::find($planId);
        if (!$plan) {
            return redirect()->route('cliente.planes')->with('error', 'Debe seleccionar un plan válido.');
        }
        // Pagar el mismo plan solo se permite como renovación
        $esRenovacion = $cliente->plan_id == $plan->id;
        if (!$pago && $esRenovacion && !$this->motivoRenovacion($cliente)) {
            return redirect()->route('cliente.planes')->with('error', 'Ese plan ya es tu plan actual.');
        }
        $monto = $pago ? $pago->monto : $this->montoPlan($plan);

        return view('app.cliente.pagar-plan', compact('cliente', 'plan', 'pago', 'monto', 'esRenovacion'));
    }

    /**
     * Registra el pago del plan como PENDIENTE de validación.
     */
    public function planesPagarStore(Request $request, $planId)
    {
        $cliente = Cliente::find(auth()->user()->cliente_id);
        if (!$cliente) {
            abort(403);
        }
        $plan = Plan::find($planId);
        if (!$plan) {
            return redirect()->route('cliente.planes')->with('error', 'Debe seleccionar un plan válido.');
        }
        if ($this->pagoPlanPendiente($cliente->id)) {
            return redirect()->route('cliente.planes.pagar', $plan->id);
        }
        if ($cliente->plan_id == $plan->id && !$this->motivoRenovacion($cliente)) {
            return redirect()->route('cliente.planes')->with('error', 'Ese plan ya es tu plan actual.');
        }

        $reglas = array(
            'operacion' => 'required|max:30',
            'fechapago' => 'required|date|before_or_equal:today',
        );
        $mensajes = array(
            'operacion.required'        => 'Debe ingresar el N.° de operación',
            'operacion.max'             => 'El N.° de operación es demasiado largo',
            'fechapago.required'        => 'Debe ingresar la fecha del voucher',
            'fechapago.date'            => 'La fecha del voucher no es válida',
            'fechapago.before_or_equal' => 'La fecha del voucher no puede ser futura',
        );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return redirect()->route('cliente.planes.pagar', $plan->id)->withErrors($validacion)->withInput();
        }

        $pago = new Planpago();
        $pago->cliente_id = $cliente->id;
        $pago->plan_id    = $plan->id;
        $pago->monto      = $this->montoPlan($plan);
        $pago->operacion  = trim($request->input('operacion'));
        $pago->fechapago  = $request->input('fechapago');
        $pago->situacion  = 'PENDIENTE';
        $pago->save();

        return redirect()->route('cliente.planes.pagar', $plan->id)->with('mensaje', 'Hemos recibido tu pago.');
    }

    /** Pago de plan del cliente que espera validación (como máximo uno) */
    private function pagoPlanPendiente($clienteId)
    {
        return Planpago::where('cliente_id', '=', $clienteId)
            ->where('situacion', '=', 'PENDIENTE')
            ->orderBy('id', 'desc')
            ->first();
    }

    /** Precio a pagar: el de promoción si existe */
    private function montoPlan($plan)
    {
        return $plan->preciopromocion > 0 ? $plan->preciopromocion : $plan->precio;
    }

    /**
     * (Administrador) Modal para validar el pago pendiente de plan de un cliente, con su historial de pagos.
     */
    public function validarPagoPlan($id, $listarLuego)
    {
        $pago = Planpago::find($id);
        if (!$pago) {
            return 'El pago no existe.';
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $cliente   = $pago->cliente;
        $historial = Planpago::where('cliente_id', '=', $pago->cliente_id)
            ->where('id', '<>', $pago->id)
            ->orderBy('id', 'desc')
            ->get();
        $entidad  = 'Cliente';
        $formData = array('route' => array('cliente.validarpago.store', $pago->id), 'method' => 'POST', 'class' => 'form-horizontal', 'id' => 'formMantenimiento' . $entidad, 'autocomplete' => 'off');
        $boton    = 'Confirmar Pago';
        return view($this->folderview . '.validar-plan')->with(compact('pago', 'cliente', 'historial', 'formData', 'entidad', 'boton', 'listar'));
    }

    /**
     * (Administrador) Confirma o rechaza el pago de plan.
     * CONFIRMAR: asigna el plan al cliente (plan_id, clases, créditos) y calcula la vigencia.
     * RECHAZAR: el pago queda RECHAZADO y el cliente conserva su plan anterior.
     */
    public function validarPagoPlanStore(Request $request, $id)
    {
        $pago = Planpago::find($id);
        if (!$pago) {
            return json_encode(array('pago' => array('El pago no existe.')));
        }
        if ($pago->situacion != 'PENDIENTE') {
            return json_encode(array('pago' => array('Este pago ya fue ' . strtolower($pago->situacion) . '.')));
        }
        $accion = $request->input('accion') == 'RECHAZAR' ? 'RECHAZAR' : 'CONFIRMAR';
        if ($accion == 'CONFIRMAR') {
            $reglas = array(
                'operacion' => 'required|max:30',
                'fechapago' => 'required|date',
                'montovalidado' => 'required|numeric|min:0',
            );
            $mensajes = array(
                'operacion.required' => 'Debe ingresar el N.° de operación',
                'operacion.max'      => 'El N.° de operación es demasiado largo',
                'fechapago.required' => 'Debe ingresar la fecha del pago',
                'fechapago.date'     => 'La fecha del pago no es válida',
                'montovalidado.required' => 'Debe ingresar el monto',
                'montovalidado.numeric' => 'El monto debe ser un número',
                'montovalidado.min' => 'El monto debe ser un número positivo',
            );
            $validacion = Validator::make($request->all(), $reglas, $mensajes);
            if ($validacion->fails()) {
                return $validacion->messages()->toJson();
            }
        }
        $error = DB::transaction(function () use ($request, $pago, $accion) {
            $pago->observacion = $request->input('observacion');
            if ($accion == 'CONFIRMAR') {
                $plan = Plan::find($pago->plan_id);
                $pago->operacion        = trim($request->input('operacion'));
                $pago->fechapago        = $request->input('fechapago');
                $pago->monto            = $request->input('montovalidado');
                $pago->situacion        = 'VALIDADO';
                $pago->fechainicio      = \Carbon\Carbon::today()->format('Y-m-d');
                $pago->fechavencimiento = ($plan && $plan->vigencia > 0)
                    ? \Carbon\Carbon::today()->addDays((int) $plan->vigencia)->format('Y-m-d')
                    : null;

                $cliente = Cliente::find($pago->cliente_id);
                $cliente->plan_id = $pago->plan_id;
                if ($plan && !is_null($plan->creditos)) {
                    $cliente->clases = $cliente->clases + $plan->creditos;
                }
                if ($plan && !is_null($plan->creditos)) {
                    $cliente->creditos = $cliente->creditos + $plan->creditos;
                }
                $cliente->save();
            } else {
                $pago->situacion = 'RECHAZADO';
            }
            $pago->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    /**
     * Mi perfil: datos del cliente autenticado.
     */
    public function perfil()
    {
        $cliente = Cliente::find(auth()->user()->cliente_id);
        if (!$cliente) {
            abort(403);
        }
        $usuario = auth()->user();

        return view('app.cliente.perfil', compact('cliente', 'usuario'));
    }

    /**
     * Actualiza los datos personales del cliente autenticado.
     */
    public function perfilStore(Request $request)
    {
        $cliente = Cliente::find(auth()->user()->cliente_id);
        if (!$cliente) {
            abort(403);
        }
        $reglas = array(
            'nombres'         => 'required|max:500',
            'apellidopaterno' => 'required|max:500',
            'apellidomaterno' => 'required|max:500',
            'fechanacimiento' => 'required|date_format:Y-m-d',
            'correo'          => 'nullable|email|max:200',
            'celular'         => 'nullable|max:20',
        );
        $mensajes = array(
            'nombres.required'         => 'Debe ingresar sus nombres',
            'apellidopaterno.required' => 'Debe ingresar su apellido paterno',
            'apellidomaterno.required' => 'Debe ingresar su apellido materno',
            'fechanacimiento.required' => 'Debe ingresar su fecha de nacimiento',
            'correo.email'             => 'El correo no es válido',
        );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        $validacion->after(function ($validator) use ($request, $cliente) {
            $this->validarUnicos($validator, $request, $cliente->id, false);
        });
        if ($validacion->fails()) {
            return redirect()->route('cliente.perfil')->withErrors($validacion, 'datos')->withInput();
        }

        $cliente->nombres = strtoupper($request->input('nombres'));
        $cliente->apellidopaterno = strtoupper($request->input('apellidopaterno'));
        $cliente->apellidomaterno = strtoupper($request->input('apellidomaterno'));
        $cliente->fechanacimiento = $request->input('fechanacimiento');
        $cliente->direccion = strtoupper($request->input('direccion'));
        $cliente->celular = strtoupper($request->input('celular'));
        $cliente->correo = strtoupper($request->input('correo'));
        $cliente->save();

        return redirect()->route('cliente.perfil')->with('mensaje', 'Tus datos se actualizaron correctamente.');
    }

    /**
     * Cambia la contraseña del usuario autenticado.
     */
    public function perfilPassword(Request $request)
    {
        $usuario = auth()->user();
        $reglas = array(
            'password_actual' => 'required',
            'password'        => 'required|min:6|confirmed',
        );
        $mensajes = array(
            'password_actual.required' => 'Debe ingresar su contraseña actual',
            'password.required'        => 'Debe ingresar la nueva contraseña',
            'password.min'             => 'La nueva contraseña debe tener al menos 6 caracteres',
            'password.confirmed'       => 'Las contraseñas no coinciden',
        );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        $validacion->after(function ($validator) use ($request, $usuario) {
            if ($request->input('password_actual') != '' && !Hash::check($request->input('password_actual'), $usuario->password)) {
                $validator->errors()->add('password_actual', 'La contraseña actual es incorrecta');
            }
        });
        if ($validacion->fails()) {
            return redirect()->route('cliente.perfil')->withErrors($validacion, 'password');
        }

        $usuario->password = Hash::make($request->input('password'));
        $usuario->save();

        return redirect()->route('cliente.perfil')->with('mensaje', 'Tu contraseña se cambió correctamente.');
    }

    public function generateUniqueCode() {
        // Genera un código único basado en la fecha y un número aleatorio
        return strtoupper(uniqid('RES-'));
    }
}
