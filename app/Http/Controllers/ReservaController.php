<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Reserva;
use App\Horario;
use App\Instructor;
use App\Sucursal;
use App\Clase;
use App\Cliente;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use Excel;
use File;

class ReservaController extends Controller
{
    protected $folderview      = 'app.reserva';
    protected $tituloAdmin     = 'Reserva';
    protected $tituloRegistrar = 'Registrar Reserva';
    protected $tituloModificar = 'Modificar Reserva';
    protected $tituloConfirmar = 'Validar Ingreso';
    protected $tituloEliminar  = 'Eliminar Reserva';
    protected $tituloValidarInstructor = 'Validar Instructor';
    protected $rutas           = array(
        'create' => 'reserva.create',
        'edit'   => 'reserva.edit',
        'delete' => 'reserva.eliminar',
        'search' => 'reserva.buscar',
        'index'  => 'reserva.index',
        'confirmar' => 'reserva.confirmar',
        'validarinstructor' => 'reserva.validarinstructor',
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
        $entidad          = 'Reserva';
        $codigo             = Libreria::getParam($request->input('codigo'));
        $cliente            = Libreria::getParam($request->input('cliente'));
        $sucursal             = Libreria::getParam($request->input('sucursal'));
        $instructor             = Libreria::getParam($request->input('instructor'));
        $clase             = Libreria::getParam($request->input('clase'));
        $horario             = Libreria::getParam($request->input('horario'));
        $resultado        = Reserva::join('horario', 'horario.id', '=', 'reserva.horario_id')
                                ->join('instructor', 'instructor.id', '=', 'horario.instructor_id')
                                ->join('clase', 'clase.id', '=', 'horario.clase_id')
                                ->join('cliente', 'cliente.id', '=', 'reserva.cliente_id')
                                ->where('codigo', 'LIKE', '%' . strtoupper($codigo) . '%')
                                ->where(function($query) use ($cliente) {
                                    $query->where(DB::raw('concat(cliente.apellidopaterno, " ", cliente.apellidomaterno, " ", cliente.nombres)'), 'LIKE', '%' . strtoupper($cliente) . '%');
                                })
                                ->orderBy('codigo', 'ASC');
        if($sucursal!=""){
            $resultado->where('reserva.sucursal_id','=',$sucursal);
        }
        if($instructor!=""){
            $resultado->where('instructor_id','=',$instructor);
        }
        if($horario!=""){
            $resultado->where('horario_id','=',$horario);
        }
        if($clase!=""){
            $resultado->where('clase_id','=',$clase);
        }
        $lista            = $resultado->select('reserva.*')->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Código', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Cliente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Sede', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Horario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Clase', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Instructor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Ubicación', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Estado', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Asistencia', 'numero' => '1');
        // La validación del instructor ahora se hace por horario en "Validar horario"
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '3');

        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
        $titulo_confirmar = $this->tituloConfirmar;
        $titulo_validarinstructor = $this->tituloValidarInstructor;
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
            return view($this->folderview . '.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta','titulo_confirmar','titulo_validarinstructor'));
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
        $entidad          = 'Reserva';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboInstructor = array('' => '--Todos--') + $this->listaInstructores();
        $cboSucursal = array('' => '--Todos--') + Sucursal::orderBy('nombre','asc')->pluck('nombre','id')->toArray();
        $cboClase = array('' => '--Todos--') + Clase::orderBy('nombre','asc')->pluck('nombre','id')->toArray();
        $cboHorario = array('' => '--Todos--') + $this->listaHorariosFiltro(null, null, null);
        return view($this->folderview . '.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta','cboInstructor','cboSucursal','cboClase','cboHorario'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Reserva';
        $reserva = null;
        $formData = array('reserva.store');
        $cboInstructor = array('' => '--Seleccione--') + $this->listaInstructores();
        $cboSucursal = array('' => '--Seleccione--') + Sucursal::orderBy('nombre','asc')->pluck('nombre','id')->toArray();
        $cboClase = array('' => '--Seleccione--') + Clase::orderBy('nombre','asc')->pluck('nombre','id')->toArray();
        $cboHorario = array('' => '--Seleccione--');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento' . $entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar';
        return view($this->folderview . '.mant')->with(compact('reserva', 'formData', 'entidad', 'boton', 'listar', 'cboInstructor','cboSucursal','cboClase','cboHorario'));
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
        $validacion = Validator::make($request->all(), $this->reglasReserva(), $this->mensajesReserva());
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        // Mismas reglas que la reserva del cliente: días/vigencia, capacidad, lugar y una por día
        $mensaje = $this->validarReglasReserva($request, null);
        if ($mensaje) {
            return json_encode(array('reserva' => array($mensaje)));
        }
        $horario = Horario::find($request->input('horario_id'));
        $error = DB::transaction(function () use ($request, $horario) {
            // Cada reserva consume 1 crédito del cliente (descuento atómico)
            if (!$this->descontarCredito($request->input('cliente_id'))) {
                return json_encode(array('reserva' => array('El cliente no tiene créditos disponibles.')));
            }
            $obj = new Reserva();
            $obj->codigo = strtoupper(uniqid('RES-'));
            $obj->fecha = $request->input('fecha');
            $obj->cliente_id = $request->input('cliente_id');
            $obj->horario_id = $horario->id;
            $obj->sucursal_id = $horario->sucursal_id;
            $obj->ubicacion = (int) $request->input('ubicacion');
            $obj->situacion = 'RESERVADO';
            $obj->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    /** Reglas del formulario de reserva del panel (registrar y modificar) */
    private function reglasReserva()
    {
        return array(
            'fecha'      => 'required|date',
            'cliente_id' => 'required|integer',
            'horario_id' => 'required|integer',
            'ubicacion'  => 'required|integer|min:1',
        );
    }

    private function mensajesReserva()
    {
        return array(
            'fecha.required'      => 'Debe ingresar una fecha',
            'fecha.date'          => 'La fecha no es válida',
            'cliente_id.required' => 'Debe seleccionar un cliente de la lista',
            'cliente_id.integer'  => 'Debe seleccionar un cliente de la lista',
            'horario_id.required' => 'Debe seleccionar un horario',
            'ubicacion.required'  => 'Debe seleccionar un lugar',
            'ubicacion.integer'   => 'El lugar no es válido',
            'ubicacion.min'       => 'El lugar no es válido',
        );
    }

    /**
     * Valida una reserva del panel con las mismas reglas que la del cliente.
     * $exceptoId = reserva que se modifica (no cuenta contra sí misma). Devuelve el mensaje de error o null.
     */
    private function validarReglasReserva(Request $request, $exceptoId)
    {
        $fecha   = \Carbon\Carbon::parse($request->input('fecha'))->format('Y-m-d');
        $horario = Horario::find($request->input('horario_id'));
        if (!$horario) {
            return 'El horario no existe.';
        }
        if (!Cliente::find($request->input('cliente_id'))) {
            return 'El cliente no existe.';
        }
        // Día de la semana y rango de vigencia del horario
        if (!$this->horarioCorreEn($horario, $fecha)) {
            return 'El horario no se dicta el ' . \Carbon\Carbon::parse($fecha)->format('d/m/Y') . ' (días o vigencia del horario).';
        }
        // Capacidad de la clase y lugar
        $capacidad = $this->capacidadHorario($horario);
        $ubicacion = (int) $request->input('ubicacion');
        if ($capacidad > 0 && $ubicacion > $capacidad) {
            return 'El lugar N.° ' . $ubicacion . ' no existe en esta clase (capacidad ' . $capacidad . ').';
        }
        $delDia = Reserva::where('horario_id', '=', $horario->id)
            ->where('fecha', '=', $fecha)
            ->whereNull('deleted_at');
        if ($exceptoId) {
            $delDia->where('id', '<>', $exceptoId);
        }
        if ($capacidad > 0 && (clone $delDia)->count() >= $capacidad) {
            return 'La clase está llena para ese día.';
        }
        if ((clone $delDia)->where('ubicacion', '=', $ubicacion)->exists()) {
            return 'El lugar N.° ' . $ubicacion . ' ya está ocupado ese día.';
        }
        // Una reserva por día en la misma sede con el mismo instructor
        $yaReservo = Reserva::join('horario', 'reserva.horario_id', '=', 'horario.id')
            ->where('reserva.cliente_id', '=', $request->input('cliente_id'))
            ->where('reserva.fecha', '=', $fecha)
            ->where('horario.sucursal_id', '=', $horario->sucursal_id)
            ->where('horario.instructor_id', '=', $horario->instructor_id)
            ->whereNull('reserva.deleted_at');
        if ($exceptoId) {
            $yaReservo->where('reserva.id', '<>', $exceptoId);
        }
        if ($yaReservo->exists()) {
            return 'El cliente ya tiene una reserva ese día en esta sede con el mismo instructor.';
        }
        return null;
    }

    /** Descuenta 1 crédito al cliente solo si lo tiene (true si se descontó) */
    private function descontarCredito($clienteId)
    {
        return Cliente::where('id', '=', $clienteId)
            ->where('creditos', '>=', 1)
            ->decrement('creditos', 1) > 0;
    }

    /** Devuelve 1 crédito al cliente */
    private function devolverCredito($clienteId)
    {
        Cliente::where('id', '=', $clienteId)->increment('creditos', 1);
    }

    /** Capacidad del horario: se toma de su clase (clase.capacidad); 0 si no tiene */
    private function capacidadHorario($horario)
    {
        $clase = $horario->clase;
        return $clase ? (int) $clase->capacidad : 0;
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
        $dias = $horario->dias != '' ? array_map('intval', explode(',', $horario->dias)) : array(1, 2, 3, 4, 5, 6, 7);
        return in_array((int) \Carbon\Carbon::parse($fecha)->format('N'), $dias);
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
        $existe = Libreria::verificarExistencia($id, 'reserva');
        if ($existe !== true) {
            return $existe;
        }
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $reserva = Reserva::find($id);
        $entidad  = 'Reserva';
        $formData = array('reserva.update', $id);
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento' . $entidad, 'autocomplete' => 'off');
        $boton    = 'Modificar';
        $cboInstructor = array('' => '--Seleccione--') + $this->listaInstructores();
        $cboSucursal = array('' => '--Seleccione--') + Sucursal::orderBy('nombre','asc')->pluck('nombre','id')->toArray();
        $cboClase = array('' => '--Seleccione--') + Clase::orderBy('nombre','asc')->pluck('nombre','id')->toArray();
        // Los horarios y lugares se cargan por AJAX según sede/clase/instructor y fecha
        $cboHorario = array('' => '--Seleccione--');
        return view($this->folderview . '.mant')->with(compact('reserva', 'formData', 'entidad', 'boton', 'listar', 'cboInstructor','cboSucursal','cboClase','cboHorario'));
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
        $existe = Libreria::verificarExistencia($id, 'reserva');
        if ($existe !== true) {
            return $existe;
        }
        $validacion = Validator::make($request->all(), $this->reglasReserva(), $this->mensajesReserva());
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $mensaje = $this->validarReglasReserva($request, $id);
        if ($mensaje) {
            return json_encode(array('reserva' => array($mensaje)));
        }
        $horario = Horario::find($request->input('horario_id'));
        $error = DB::transaction(function () use ($request, $id, $horario) {
            $obj = Reserva::find($id);
            // Si cambia el cliente: se devuelve el crédito al anterior y se descuenta al nuevo
            if ($obj->cliente_id != $request->input('cliente_id')) {
                if (!$this->descontarCredito($request->input('cliente_id'))) {
                    return json_encode(array('reserva' => array('El nuevo cliente no tiene créditos disponibles.')));
                }
                $this->devolverCredito($obj->cliente_id);
            }
            // El código, el estado, la asistencia y la validación del instructor no se cambian aquí
            $obj->fecha = $request->input('fecha');
            $obj->cliente_id = $request->input('cliente_id');
            $obj->horario_id = $horario->id;
            $obj->sucursal_id = $horario->sucursal_id;
            $obj->ubicacion = (int) $request->input('ubicacion');
            $obj->save();
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
        $existe = Libreria::verificarExistencia($id, 'reserva');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function () use ($id) {
            $person = Reserva::find($id);
            // Eliminar desde el panel siempre devuelve el crédito al cliente
            $this->devolverCredito($person->cliente_id);
            $person->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'reserva');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Reserva::find($id);
        $entidad  = 'Reserva';
        $formData = array('route' => array('reserva.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento' . $entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function buscarDNI(Request $request)
    {
        $respuesta = array();
        $dni = $request->input('dni');
        $client = new Client();
        $res = $client->get('http://facturae-garzasoft.com/facturacion/buscaHorario/BuscaHorario2.php?' . 'dni=' . $dni . '&fe=N&token=qusEj_w7aHEpX');
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
        $res = $client->get('https://comprobante-e.com/facturacion/buscaHorario/BuscaHorarioRuc.php?fe=N&token=qusEj_w7aHEpX&' . 'ruc=' . $ruc);
        if ($res->getStatusCode() == 200) { // 200 OK
            $response_data = $res->getBody()->getContents();
            $respuesta = json_decode($response_data);
        }
        return json_encode($respuesta);
    }

    /**
     * Horarios DISPONIBLES filtrados por sede/clase/instructor y, si se envía, por la fecha
     * (día de la semana + vigencia). Incluye lugares libres de ese día.
     * reserva_id (al modificar) hace que la propia reserva no cuente como ocupada.
     */
    public function cargarHorarios(Request $request)
    {
        $sucursal_id = $request->input('sucursal_id');
        $instructor_id = $request->input('instructor_id');
        $clase_id = $request->input('clase_id');
        $fecha = $request->input('fecha');
        $reservaId = $request->input('reserva_id');
        $horarios = Horario::where(function ($query) use ($sucursal_id, $instructor_id, $clase_id) {
            if (!empty($sucursal_id)) {
                $query->where('sucursal_id', '=', $sucursal_id);
            }
            if (!empty($instructor_id)) {
                $query->where('instructor_id', '=', $instructor_id);
            }
            if (!empty($clase_id)) {
                $query->where('clase_id', '=', $clase_id);
            }
        })
            ->where('situacion', '=', 'DISPONIBLE')
            ->orderBy('horainicio', 'asc')
            ->get();

        $data = array();
        foreach ($horarios as $horario) {
            if (!empty($fecha) && !$this->horarioCorreEn($horario, $fecha)) {
                continue;
            }
            $capacidad = $this->capacidadHorario($horario);
            $ocupados = 0;
            if (!empty($fecha)) {
                $consulta = Reserva::where('horario_id', '=', $horario->id)
                    ->where('fecha', '=', \Carbon\Carbon::parse($fecha)->format('Y-m-d'))
                    ->whereNull('deleted_at');
                if (!empty($reservaId)) {
                    $consulta->where('id', '<>', $reservaId);
                }
                $ocupados = $consulta->count();
            }
            $data[] = array(
                'id'         => $horario->id,
                'horainicio' => $horario->horainicio,
                'horafin'    => $horario->horafin,
                'capacidad'  => $capacidad,
                'libres'     => max($capacidad - $ocupados, 0),
            );
        }
        return response()->json($data);
    }

    /**
     * Lugares libres (1..capacidad de la clase) de un horario en una fecha.
     * reserva_id (al modificar) hace que su propio lugar aparezca como libre.
     */
    public function lugaresLibres(Request $request)
    {
        $horario = Horario::find($request->input('horario_id'));
        if (!$horario || $request->input('fecha') == '') {
            return response()->json(array('capacidad' => 0, 'libres' => array()));
        }
        $capacidad = $this->capacidadHorario($horario);
        $consulta = Reserva::where('horario_id', '=', $horario->id)
            ->where('fecha', '=', \Carbon\Carbon::parse($request->input('fecha'))->format('Y-m-d'))
            ->whereNotNull('ubicacion')
            ->whereNull('deleted_at');
        if ($request->input('reserva_id') != '') {
            $consulta->where('id', '<>', $request->input('reserva_id'));
        }
        $ocupados = $consulta->pluck('ubicacion')->map(function ($u) {
            return (int) $u;
        })->all();
        $libres = array();
        for ($n = 1; $n <= $capacidad; $n++) {
            if (!in_array($n, $ocupados)) {
                $libres[] = $n;
            }
        }
        $instructor = $horario->instructor;
        return response()->json(array(
            'capacidad'  => $capacidad,
            'libres'     => $libres,
            'ocupados'   => array_values(array_unique($ocupados)),
            // Instructor por su sobrenombre (si no tiene, sus nombres), como en la vista del cliente
            'instructor' => $instructor ? ($instructor->sobrenombre ?: $instructor->nombres) : '',
        ));
    }

    public function clienteautocompletar($searching){
        $resultado        = Cliente::where(function($sql) use($searching){
                                $sql->where(DB::raw('CONCAT(apellidopaterno," ",apellidomaterno," ",nombres)'), 'LIKE', '%'.strtoupper($searching).'%')
                                ->orWhere('documento', 'LIKE', '%'.strtoupper($searching).'%');
                            })
                            ->orderBy('apellidopaterno', 'ASC');
        $list      = $resultado->select('cliente.*')->get();
        $data = array();
        foreach ($list as $key => $value) {
            $name = $value->apellidopaterno." ".$value->apellidomaterno." ".$value->nombres;
            $data[] = array(
                            'label' => $value->documento.' '.trim($name),
                            'id'    => $value->id,
                            'value' => $value->documento.' '.trim($name),
                            'dni' => $value->documento,
                        );
        }
        return json_encode($data);
    }

    /**
     * Horarios DISPONIBLES para el filtro del listado, opcionalmente por sede/instructor/clase.
     * Texto: "08:00 - 09:00 · CLASE · SEDE · SOBRENOMBRE"
     */
    private function listaHorariosFiltro($sucursalId, $instructorId, $claseId)
    {
        $consulta = Horario::join('clase', 'horario.clase_id', '=', 'clase.id')
            ->join('sucursal', 'horario.sucursal_id', '=', 'sucursal.id')
            ->leftJoin('instructor', 'horario.instructor_id', '=', 'instructor.id')
            ->where('horario.situacion', '=', 'DISPONIBLE')
            ->select('horario.id',
                DB::raw("CONCAT(TIME_FORMAT(horario.horainicio, '%H:%i'), ' - ', TIME_FORMAT(horario.horafin, '%H:%i'), ' · ', clase.nombre, ' · ', sucursal.nombre, ' · ', COALESCE(NULLIF(instructor.sobrenombre, ''), instructor.nombres, '')) as texto"))
            ->orderBy('sucursal.nombre', 'asc')
            ->orderBy('clase.nombre', 'asc')
            ->orderBy('horario.horainicio', 'asc');
        if (!empty($sucursalId)) {
            $consulta->where('horario.sucursal_id', '=', $sucursalId);
        }
        if (!empty($instructorId)) {
            $consulta->where('horario.instructor_id', '=', $instructorId);
        }
        if (!empty($claseId)) {
            $consulta->where('horario.clase_id', '=', $claseId);
        }
        return $consulta->pluck('texto', 'id')->toArray();
    }

    /**
     * (AJAX) Horarios para el filtro del listado según sede/instructor/clase elegidos.
     */
    public function horariosFiltro(Request $request)
    {
        $lista = $this->listaHorariosFiltro($request->input('sucursal'), $request->input('instructor'), $request->input('clase'));
        $data = array();
        foreach ($lista as $id => $texto) {
            $data[] = array('id' => $id, 'texto' => $texto);
        }
        return response()->json($data);
    }

    /**
     * Instructores para los combos: id => "NOMBRES APELLIDOPATERNO APELLIDOMATERNO"
     */
    private function listaInstructores()
    {
        return Instructor::orderBy('nombres', 'asc')
            ->orderBy('apellidopaterno', 'asc')
            // Instructor por su sobrenombre (si no tiene, sus nombres)
            ->select('id', DB::raw("COALESCE(NULLIF(sobrenombre, ''), nombres) as nombrecompleto"))
            ->pluck('nombrecompleto', 'id')
            ->toArray();
    }

    public function confirmarAsistencia($id)
    {
        $existe = Libreria::verificarExistencia($id, 'reserva');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function () use ($id) {
            $obj = Reserva::find($id);
            $obj->asistencia = 'INGRESO VALIDADO';
            $obj->situacion = 'FINALIZADO';
            $obj->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function confirmar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'reserva');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Reserva::find($id);
        $entidad  = 'Reserva';
        $formData = array('route' => array('reserva.confirmarAsistencia', $id), 'method' => 'POST', 'class' => 'form-horizontal', 'id' => 'formMantenimiento' . $entidad, 'autocomplete' => 'off');
        $boton    = 'Validar Ingreso';
        return view('app.confirmar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }

    /**
     * Modal "Validar ingreso en grupo": lista las reservas seleccionadas (aún sin ingreso validado).
     * Recibe ?ids=1,2,3
     */
    public function confirmarGrupo(Request $request)
    {
        $ids = array_filter(array_map('intval', explode(',', (string) $request->input('ids'))));
        $listar = Libreria::getParam($request->input('listar'), 'NO');
        $reservas = Reserva::whereIn('id', $ids)
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->whereNull('asistencia')->orWhere('asistencia', '<>', 'INGRESO VALIDADO');
            })
            ->orderBy('fecha', 'asc')
            ->get();
        $entidad  = 'Reserva';
        $formData = array('route' => array('reserva.confirmarAsistenciaGrupo'), 'method' => 'POST', 'class' => 'form-horizontal', 'id' => 'formMantenimiento' . $entidad, 'autocomplete' => 'off');
        $boton    = 'Validar Ingreso';
        return view($this->folderview . '.validar-ingreso-grupo')->with(compact('reservas', 'formData', 'entidad', 'boton', 'listar'));
    }

    /**
     * Valida el ingreso de varias reservas a la vez: solo cambia asistencia = INGRESO VALIDADO
     * (a diferencia del botón individual, no cambia la situación).
     */
    public function confirmarAsistenciaGrupo(Request $request)
    {
        $ids = array_filter(array_map('intval', (array) $request->input('ids')));
        if (!count($ids)) {
            return json_encode(array('reserva' => array('Debe seleccionar al menos una reserva.')));
        }
        $error = DB::transaction(function () use ($ids) {
            $reservas = Reserva::whereIn('id', $ids)
                ->whereNull('deleted_at')
                ->where(function ($q) {
                    $q->whereNull('asistencia')->orWhere('asistencia', '<>', 'INGRESO VALIDADO');
                })
                ->get();
            foreach ($reservas as $obj) {
                $obj->asistencia = 'INGRESO VALIDADO';
                $obj->save();
            }
        });
        return is_null($error) ? "OK" : $error;
    }

    /**
     * Modal "Validar Instructor": muestra los datos de la reserva para confirmar.
     */
    public function validarInstructor($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'reserva');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $reserva  = Reserva::find($id);
        $entidad  = 'Reserva';
        $formData = array('route' => array('reserva.validarinstructor.store', $id), 'method' => 'POST', 'class' => 'form-horizontal', 'id' => 'formMantenimiento' . $entidad, 'autocomplete' => 'off');
        $boton    = 'Validar Instructor';
        return view($this->folderview . '.validar-instructor')->with(compact('reserva', 'formData', 'entidad', 'boton', 'listar'));
    }

    /**
     * Marca la reserva como INSTRUCTOR VALIDADO (guarda fecha y usuario que validó).
     * No modifica "situacion" ni "asistencia".
     */
    public function validarInstructorStore($id)
    {
        $existe = Libreria::verificarExistencia($id, 'reserva');
        if ($existe !== true) {
            return $existe;
        }
        $reserva = Reserva::find($id);
        if ($reserva->situacioninstructor == 'INSTRUCTOR VALIDADO') {
            return json_encode(array('reserva' => array('El instructor de esta reserva ya fue validado.')));
        }
        $error = DB::transaction(function () use ($reserva) {
            $reserva->situacioninstructor = 'INSTRUCTOR VALIDADO';
            $reserva->fechavalidacioninstructor = \Carbon\Carbon::now();
            $reserva->usuariovalidacioninstructor_id = auth()->user()->id;
            $reserva->save();
        });
        return is_null($error) ? "OK" : $error;
    }
}
