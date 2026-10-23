<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Horario;
use App\Reserva;
use App\Instructor;
use App\Sucursal;
use App\Clase;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use Excel;
use File;

class HorarioController extends Controller
{
    protected $folderview      = 'app.horario';
    protected $tituloAdmin     = 'Horario';
    protected $tituloRegistrar = 'Registrar Horario';
    protected $tituloModificar = 'Modificar Horario';
    protected $tituloEliminar  = 'Eliminar Horario';
    protected $rutas           = array(
        'create' => 'horario.create',
        'edit'   => 'horario.edit',
        'delete' => 'horario.eliminar',
        'search' => 'horario.buscar',
        'index'  => 'horario.index',
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
        $entidad          = 'Horario';
        $nombre             = Libreria::getParam($request->input('nombre'));
        $sucursal             = Libreria::getParam($request->input('sucursal'));
        $instructor             = Libreria::getParam($request->input('instructor'));
        $clase             = Libreria::getParam($request->input('clase'));
        $situacion         = Libreria::getParam($request->input('situacion'));
        // Sin filtro por nombre (el campo nombre del horario no se usa)
        $resultado        = Horario::orderBy('horainicio', 'ASC');
        if($situacion!=""){
            $resultado->where('situacion','=',$situacion);
        }
        // Días marcados (1=Lun ... 7=Dom): horarios que tienen al menos uno de esos días
        // (los horarios sin días marcados no aparecen al filtrar por días)
        $diasFiltro = array_filter(array_map('intval', (array) $request->input('dias', array())), function ($d) {
            return $d >= 1 && $d <= 7;
        });
        if (count($diasFiltro)) {
            $resultado->where(function ($q) use ($diasFiltro) {
                foreach ($diasFiltro as $d) {
                    $q->orWhereRaw('FIND_IN_SET(?, dias) > 0', array($d));
                }
            });
        }
        // Hora: valor "HH:MM:SS|HH:MM:SS" (hora inicio | hora fin)
        $hora = Libreria::getParam($request->input('hora'));
        if ($hora != "" && strpos($hora, '|') !== false) {
            list($horaInicio, $horaFin) = explode('|', $hora, 2);
            $resultado->where('horainicio', '=', $horaInicio)->where('horafin', '=', $horaFin);
        }
        // Vigencia respecto a hoy (sin fecha de inicio/fin = rango abierto por ese lado)
        $vigencia = Libreria::getParam($request->input('vigencia'));
        $hoy      = \Carbon\Carbon::today()->format('Y-m-d');
        if ($vigencia == 'VIGENTE') {
            $resultado->where(function ($q) use ($hoy) {
                $q->whereNull('fechainicio')->orWhere('fechainicio', '<=', $hoy);
            })->where(function ($q) use ($hoy) {
                $q->whereNull('fechafin')->orWhere('fechafin', '>=', $hoy);
            });
        } elseif ($vigencia == 'VENCIDO') {
            $resultado->whereNotNull('fechafin')->where('fechafin', '<', $hoy);
        } elseif ($vigencia == 'POR EMPEZAR') {
            $resultado->whereNotNull('fechainicio')->where('fechainicio', '>', $hoy);
        }
        if($sucursal!=""){
            $resultado->where('sucursal_id','=',$sucursal);
        }
        if($instructor!=""){
            $resultado->where('instructor_id','=',$instructor);
        }
        if($clase!=""){
            $resultado->where('clase_id','=',$clase);
        }
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Sede', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Clase', 'numero' => '1');
        //$cabecera[]       = array('valor' => 'Nombre', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Hora Inicio', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Hora Fin', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Días', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Vigencia', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Instructor', 'numero' => '1');
        //$cabecera[]       = array('valor' => 'Ocupacion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Estado', 'numero' => '1');
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
        $entidad          = 'Horario';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboInstructor = array('' => '--Todos--') + $this->listaInstructores();
        $cboSucursal = array('' => '--Todos--') + Sucursal::orderBy('nombre','asc')->pluck('nombre','id')->toArray();
        $cboClase = array('' => '--Todos--') + Clase::orderBy('nombre','asc')->pluck('nombre','id')->toArray();
        // Estados que existen en los horarios registrados (ej. DISPONIBLE, PROXIMAMENTE)
        $estados = Horario::whereNotNull('situacion')->where('situacion', '<>', '')
            ->distinct()->orderBy('situacion', 'asc')->pluck('situacion')->toArray();
        $cboSituacion = array('' => '--Todos--') + array_combine($estados, $estados);
        // Rangos de hora que existen en los horarios, ej. "08:00 - 09:00"
        $cboHora = array('' => '--Todos--');
        foreach (Horario::select('horainicio', 'horafin')->distinct()->orderBy('horainicio', 'asc')->orderBy('horafin', 'asc')->get() as $h) {
            $cboHora[$h->horainicio . '|' . $h->horafin] = substr($h->horainicio, 0, 5) . ' - ' . substr($h->horafin, 0, 5);
        }
        return view($this->folderview . '.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta','cboInstructor','cboSucursal','cboClase','cboSituacion','cboHora'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Horario';
        $horario = null;
        $formData = array('horario.store');
        $cboInstructor = array('' => '--Seleccione--') + $this->listaInstructores();
        $cboSucursal = array('' => '--Seleccione--') + Sucursal::orderBy('nombre','asc')->pluck('nombre','id')->toArray();
        $cboClase = array('' => '--Seleccione--') + Clase::orderBy('nombre','asc')->pluck('nombre','id')->toArray();
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento' . $entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar';
        return view($this->folderview . '.mant')->with(compact('horario', 'formData', 'entidad', 'boton', 'listar', 'cboInstructor','cboSucursal','cboClase'));
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
            //'nombre' => 'required|max:500',
            'horainicio' => 'required|max:500',
            'horafin' => 'required|max:500',
            'sucursal_id' => 'required',
            'instructor_id' => 'required',
            'clase_id' => 'required',
            'dias' => 'required|array',
            'dias.*' => 'in:1,2,3,4,5,6,7',
            'fechainicio' => 'required|date',
            'fechafin' => 'required|date|after_or_equal:fechainicio',
            //'aforo' => 'required|integer|min:1',
        );
        $mensajes = $this->mensajesValidacion();
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $cruce = $this->mensajeCruce($request, null);
        if ($cruce) {
            return json_encode(array('horario' => array($cruce)));
        }
        $error = DB::transaction(function () use ($request) {
            $obj = new Horario();
            $obj->dias = $this->diasRequest($request);
            $obj->fechainicio = $request->input('fechainicio');
            $obj->fechafin = $request->input('fechafin');
            $obj->nombre = strtoupper($request->input('nombre'));
            $obj->horainicio = strtoupper($request->input('horainicio'));
            $obj->horafin = strtoupper($request->input('horafin'));
            $obj->sucursal_id = strtoupper($request->input('sucursal_id'));
            $obj->instructor_id = strtoupper($request->input('instructor_id'));
            $obj->clase_id = strtoupper($request->input('clase_id'));
            //$obj->aforo = strtoupper($request->input('aforo'));
            $obj->ocupacion = 0;
            $obj->situacion = $request->input('situacion');
            $obj->save();
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
        $existe = Libreria::verificarExistencia($id, 'horario');
        if ($existe !== true) {
            return $existe;
        }
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $horario = Horario::find($id);
        $entidad  = 'Horario';
        $formData = array('horario.update', $id);
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento' . $entidad, 'autocomplete' => 'off');
        $boton    = 'Modificar';
        $cboInstructor = array('' => '--Seleccione--') + $this->listaInstructores();
        $cboSucursal = array('' => '--Seleccione--') + Sucursal::orderBy('nombre','asc')->pluck('nombre','id')->toArray();
        $cboClase = array('' => '--Seleccione--') + Clase::orderBy('nombre','asc')->pluck('nombre','id')->toArray();
        // Reservas activas desde hoy: el formulario avisa si el nuevo rango de vigencia las deja fuera
        $fechasReservadas = Reserva::where('horario_id', '=', $id)
            ->whereNull('deleted_at')
            ->where('fecha', '>=', \Carbon\Carbon::today()->format('Y-m-d'))
            ->get()
            ->map(function ($r) {
                return \Carbon\Carbon::parse($r->fecha)->format('Y-m-d');
            })
            ->all();
        return view($this->folderview . '.mant')->with(compact('horario', 'formData', 'entidad', 'boton', 'listar', 'cboInstructor','cboSucursal','cboClase','fechasReservadas'));
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
        $existe = Libreria::verificarExistencia($id, 'horario');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array(
            //'nombre' => 'required|max:500',
            'horainicio' => 'required|max:500',
            'horafin' => 'required|max:500',
            'sucursal_id' => 'required',
            'instructor_id' => 'required',
            'clase_id' => 'required',
            'dias' => 'required|array',
            'dias.*' => 'in:1,2,3,4,5,6,7',
            'fechainicio' => 'required|date',
            'fechafin' => 'required|date|after_or_equal:fechainicio',
            //'aforo' => 'required|integer|min:1',
        );
        $mensajes = $this->mensajesValidacion();
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $cruce = $this->mensajeCruce($request, $id);
        if ($cruce) {
            return json_encode(array('horario' => array($cruce)));
        }
        $error = DB::transaction(function () use ($request, $id) {
            $obj = Horario::find($id);
            $obj->dias = $this->diasRequest($request);
            $obj->fechainicio = $request->input('fechainicio');
            $obj->fechafin = $request->input('fechafin');
            $obj->nombre = strtoupper($request->input('nombre'));
            $obj->horainicio = strtoupper($request->input('horainicio'));
            $obj->horafin = strtoupper($request->input('horafin'));
            $obj->sucursal_id = strtoupper($request->input('sucursal_id'));
            $obj->instructor_id = strtoupper($request->input('instructor_id'));
            $obj->clase_id = strtoupper($request->input('clase_id'));
            //$obj->aforo = strtoupper($request->input('aforo'));
            $obj->situacion = $request->input('situacion');
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
        $existe = Libreria::verificarExistencia($id, 'horario');
        if ($existe !== true) {
            return $existe;
        }
        // No se elimina un horario que tiene reservas registradas
        $mensajeBloqueo = $this->mensajeReservas($id);
        if ($mensajeBloqueo) {
            return json_encode(array('horario' => array($mensajeBloqueo)));
        }
        $error = DB::transaction(function () use ($id) {
            $person = Horario::find($id);
            $person->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    /** Mensaje si el horario tiene reservas registradas (pasadas o futuras), o null si no tiene */
    private function mensajeReservas($id)
    {
        $total = Reserva::where('horario_id', '=', $id)->whereNull('deleted_at')->count();
        if ($total == 0) {
            return null;
        }
        return 'No se puede eliminar este horario: tiene ' . $total . ($total == 1 ? ' reserva registrada.' : ' reservas registradas.');
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'horario');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Horario::find($id);
        $entidad  = 'Horario';
        $formData = array('route' => array('horario.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento' . $entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        // Con reservas registradas el modal solo muestra el aviso (sin botón Eliminar)
        $mensajeBloqueo = $this->mensajeReservas($id);
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar', 'mensajeBloqueo'));
    }

    /**
     * Cruce de horarios: no puede existir otro horario (DISPONIBLE o PROXIMAMENTE) con la misma
     * sede, clase, instructor, hora inicio y hora fin cuyo rango de vigencia se cruce con el nuevo
     * y que comparta al menos un día de la semana. Con rangos que no se cruzan sí se permite.
     * Devuelve el mensaje de error o null si no hay cruce. $exceptoId = horario que se modifica.
     */
    private function mensajeCruce(Request $request, $exceptoId)
    {
        $consulta = Horario::where('sucursal_id', '=', $request->input('sucursal_id'))
            ->where('clase_id', '=', $request->input('clase_id'))
            ->where('instructor_id', '=', $request->input('instructor_id'))
            ->whereRaw('TIME(horainicio) = TIME(?)', array($request->input('horainicio')))
            ->whereRaw('TIME(horafin) = TIME(?)', array($request->input('horafin')))
            // Rangos que se cruzan: inicio existente <= fin nuevo y fin existente >= inicio nuevo
            ->where('fechainicio', '<=', $request->input('fechafin'))
            ->where('fechafin', '>=', $request->input('fechainicio'));
        if ($exceptoId) {
            $consulta->where('id', '<>', $exceptoId);
        }

        $diasNuevos = array_map('intval', (array) $request->input('dias'));
        $abrev = array(1 => 'Lun', 2 => 'Mar', 3 => 'Mié', 4 => 'Jue', 5 => 'Vie', 6 => 'Sáb', 7 => 'Dom');
        foreach ($consulta->get() as $otro) {
            $diasOtro = $otro->dias != '' ? array_map('intval', explode(',', $otro->dias)) : array(1, 2, 3, 4, 5, 6, 7);
            // Mismos datos y fechas cruzadas, pero en días distintos: se permite
            if (!count(array_intersect($diasNuevos, $diasOtro))) {
                continue;
            }
            $diasTexto = implode(' · ', array_map(function ($d) use ($abrev) {
                return isset($abrev[$d]) ? $abrev[$d] : $d;
            }, $diasOtro));
            return 'Ya existe un horario igual (misma sede, clase, instructor y horas) vigente del '
                . \Carbon\Carbon::parse($otro->fechainicio)->format('d/m/Y') . ' al '
                . \Carbon\Carbon::parse($otro->fechafin)->format('d/m/Y') . ' (' . $diasTexto . ').'
                . ' Cambia las fechas de vigencia o los días para que no se crucen.';
        }
        return null;
    }

    /** Mensajes de validación del formulario de horario (registrar y modificar) */
    private function mensajesValidacion()
    {
        return array(
            'horainicio.required'     => 'Debe ingresar una hora de inicio',
            'horafin.required'        => 'Debe ingresar una hora de fin',
            'sucursal_id.required'    => 'Debe seleccionar una sede',
            'instructor_id.required'  => 'Debe seleccionar un instructor',
            'clase_id.required'       => 'Debe seleccionar una clase',
            'dias.required'           => 'Debe marcar al menos un día disponible',
            'fechainicio.required'    => 'Debe ingresar la fecha desde la que está vigente',
            'fechainicio.date'        => 'La fecha "vigente desde" no es válida',
            'fechafin.required'       => 'Debe ingresar la fecha hasta la que está vigente',
            'fechafin.date'           => 'La fecha "vigente hasta" no es válida',
            'fechafin.after_or_equal' => 'La fecha "vigente hasta" no puede ser anterior a "vigente desde"',
        );
    }

    /**
     * Instructores para los combos: id => "NOMBRES APELLIDOPATERNO APELLIDOMATERNO"
     */
    private function listaInstructores()
    {
        // Instructor por su sobrenombre (si no tiene, sus nombres)
        return Instructor::select('id', DB::raw("COALESCE(NULLIF(sobrenombre, ''), nombres) as texto"))
            ->orderBy('texto', 'asc')
            ->pluck('texto', 'id')
            ->toArray();
    }

    /**
     * Días marcados en el formulario como texto ordenado, ej. "1,5,6" (1=Lunes ... 7=Domingo)
     */
    private function diasRequest(Request $request)
    {
        $dias = array_unique(array_map('intval', (array) $request->input('dias')));
        sort($dias);
        return implode(',', $dias);
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

}
