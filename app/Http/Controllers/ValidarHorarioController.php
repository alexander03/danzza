<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Horario;
use App\Instructor;
use App\Reserva;
use App\Sucursal;
use App\Validahorario;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Validar horario: confirmación de asistencia del instructor por horario + fecha.
 *  - Instructor (usertype 6): ve solo sus horarios con reservas y confirma su asistencia
 *    (desde que empieza la clase).
 *  - Administrador (usertype 1 y 4): ve los horarios de todos los instructores y confirma
 *    después de que el instructor confirmó.
 */
class ValidarHorarioController extends Controller
{
    const USERTYPE_INSTRUCTOR = 6;
    const INSTRUCTOR_CONFIRMADO = 'INSTRUCTOR CONFIRMADO';
    const ADMIN_CONFIRMADO = 'ADMIN CONFIRMADO';

    protected $folderview  = 'app.validahorario';
    protected $tituloAdmin = 'Validar horario';
    protected $tituloConfirmar = 'Confirmar asistencia';
    protected $rutas       = array(
        'search'    => 'validarhorario.buscar',
        'index'     => 'validarhorario.index',
        'confirmar' => 'validarhorario.confirmar',
    );

    public function __construct()
    {
        $this->middleware('auth');
    }

    private function esInstructor($user)
    {
        return $user->usertype_id == self::USERTYPE_INSTRUCTOR;
    }

    private function esAdmin($user)
    {
        return in_array((int) $user->usertype_id, array(1, 4));
    }

    public function index()
    {
        $user             = Auth::user();
        $entidad          = 'Validahorario';
        $title            = $this->tituloAdmin;
        $ruta             = $this->rutas;
        $esInstructor     = $this->esInstructor($user);
        if($user->usertype_id==6){
            $desde              = \Carbon\Carbon::today()->format('Y-m-d');
            $hasta              = \Carbon\Carbon::today()->format('Y-m-d');
        }else{
            $desde              = date('Y-m-d',strtotime('first day of this month'));
            $hasta              = date('Y-m-d',strtotime('last day of this month'));
        }
        $cboInstructor    = array('' => '--Todos--') + Instructor::orderBy('sobrenombre', 'asc')->get()
            ->mapWithKeys(function ($i) {
                return array($i->id => $i->sobrenombre ?: $i->nombres);
            })->all();
        $cboSucursal      = array('' => '--Todos--') + Sucursal::orderBy('nombre', 'asc')->pluck('nombre', 'id')->all();
        $cboEstado        = array(
            ''                          => '--Todos--',
            'PENDIENTE'                 => 'PENDIENTE',
            self::INSTRUCTOR_CONFIRMADO => self::INSTRUCTOR_CONFIRMADO,
            self::ADMIN_CONFIRMADO      => self::ADMIN_CONFIRMADO,
        );
        return view($this->folderview . '.admin')->with(compact('entidad', 'title', 'ruta', 'esInstructor', 'desde', 'hasta', 'cboInstructor', 'cboSucursal', 'cboEstado'));
    }

    /**
     * Horarios con reservas por día (agrupado por horario + fecha) en el rango elegido
     */
    public function buscar(Request $request)
    {
        $user         = Auth::user();
        $entidad      = 'Validahorario';
        $esInstructor = $this->esInstructor($user);
        $esAdmin      = $this->esAdmin($user);
        $desde        = $request->input('fechainicio') ?: \Carbon\Carbon::today()->format('Y-m-d');
        $hasta        = $request->input('fechafin') ?: $desde;
        if ($hasta < $desde) {
            $hasta = $desde;
        }
        $sucursal     = $request->input('sucursal') != '' ? $request->input('sucursal') : null;
        $instructor   = $request->input('instructor') != '' ? $request->input('instructor') : null;
        $estado       = $request->input('estado') != '' ? $request->input('estado') : null;

        $consulta = Reserva::join('horario', 'reserva.horario_id', '=', 'horario.id')
            ->join('clase', 'horario.clase_id', '=', 'clase.id')
            ->join('sucursal', 'horario.sucursal_id', '=', 'sucursal.id')
            ->leftJoin('instructor', 'horario.instructor_id', '=', 'instructor.id')
            ->leftJoin('validahorario', function ($join) {
                $join->on('validahorario.horario_id', '=', 'reserva.horario_id')
                    ->on('validahorario.fecha', '=', DB::raw('DATE(reserva.fecha)'));
            })
            ->whereNull('reserva.deleted_at')
            ->whereBetween(DB::raw('DATE(reserva.fecha)'), array($desde, $hasta))
            ->select(
                DB::raw('DATE(reserva.fecha) as fecha'),
                'reserva.horario_id',
                'horario.horainicio',
                'horario.horafin',
                'horario.instructor_id',
                'clase.nombre as clase',
                'sucursal.nombre as sede',
                DB::raw("COALESCE(NULLIF(instructor.sobrenombre, ''), instructor.nombres) as instructor"),
                DB::raw('COUNT(reserva.id) as total'),
                DB::raw('MAX(validahorario.situacion) as situacion'),
                DB::raw('MAX(validahorario.fechainstructor) as fechainstructor'),
                DB::raw('MAX(validahorario.fechaadmin) as fechaadmin')
            )
            ->groupBy(DB::raw('DATE(reserva.fecha)'), 'reserva.horario_id', 'horario.horainicio', 'horario.horafin',
                'horario.instructor_id', 'clase.nombre', 'sucursal.nombre', 'instructor.sobrenombre', 'instructor.nombres')
            ->orderBy('fecha', 'asc')
            ->orderBy('horario.horainicio', 'asc');

        // El instructor solo ve sus horarios; el administrador puede filtrar por instructor
        if ($esInstructor) {
            $consulta->where('horario.instructor_id', '=', (int) $user->instructor_id);
        } elseif (!is_null($instructor)) {
            $consulta->where('horario.instructor_id', '=', $instructor);
        }
        if (!is_null($sucursal)) {
            $consulta->where('horario.sucursal_id', '=', $sucursal);
        }
        if ($estado == 'PENDIENTE') {
            $consulta->whereNull('validahorario.id');
        } elseif (!is_null($estado)) {
            $consulta->where('validahorario.situacion', '=', $estado);
        }
        $lista = $consulta->get();

        // Clientes de cada horario + fecha (para "Ver reservas")
        $clientes = array();
        if (count($lista) > 0) {
            $filas = Reserva::join('cliente', 'reserva.cliente_id', '=', 'cliente.id')
                ->whereNull('reserva.deleted_at')
                ->whereIn('reserva.horario_id', $lista->pluck('horario_id')->unique()->all())
                ->whereBetween(DB::raw('DATE(reserva.fecha)'), array($desde, $hasta))
                ->select('reserva.horario_id', DB::raw('DATE(reserva.fecha) as fecha'), 'reserva.ubicacion', 'reserva.asistencia',
                    DB::raw("CONCAT(cliente.apellidopaterno, ' ', cliente.apellidomaterno, ' ', cliente.nombres) as cliente"))
                ->orderBy('reserva.ubicacion', 'asc')
                ->get();
            foreach ($filas as $f) {
                $clientes[$f->horario_id . '|' . $f->fecha][] = $f;
            }
        }

        $ahora            = \Carbon\Carbon::now();
        $ruta             = $this->rutas;
        $titulo_confirmar = $this->tituloConfirmar;
        return view($this->folderview . '.list')->with(compact('lista', 'clientes', 'entidad', 'ruta', 'titulo_confirmar', 'esInstructor', 'esAdmin', 'ahora'));
    }

    /**
     * Modal para confirmar la asistencia (instructor o administrador según quien ingresa)
     */
    public function confirmar($horarioId, $fecha, Request $request)
    {
        $user    = Auth::user();
        $entidad = 'Validahorario';
        $listar  = Libreria::getParam($request->input('listar'), 'NO');
        $horario = Horario::find($horarioId);
        $error   = $horario ? $this->errorConfirmar($user, $horario, $fecha) : 'El horario no existe.';
        $validacion = Validahorario::where('horario_id', '=', $horarioId)->where('fecha', '=', $fecha)->first();
        $reservas = Reserva::join('cliente', 'reserva.cliente_id', '=', 'cliente.id')
            ->whereNull('reserva.deleted_at')
            ->where('reserva.horario_id', '=', $horarioId)
            ->where(DB::raw('DATE(reserva.fecha)'), '=', $fecha)
            ->select('reserva.ubicacion', 'reserva.asistencia',
                DB::raw("CONCAT(cliente.apellidopaterno, ' ', cliente.apellidomaterno, ' ', cliente.nombres) as cliente"))
            ->orderBy('reserva.ubicacion', 'asc')
            ->get();
        $esInstructor = $this->esInstructor($user);
        $formData = array('route' => 'validarhorario.confirmarstore', 'method' => 'POST', 'class' => 'form-horizontal', 'id' => 'formMantenimiento' . $entidad, 'autocomplete' => 'off');
        $boton    = $esInstructor ? 'Confirmar mi asistencia' : 'Confirmar asistencia del instructor';
        return view($this->folderview . '.mant')->with(compact('horario', 'fecha', 'validacion', 'reservas', 'error', 'esInstructor', 'formData', 'entidad', 'boton', 'listar'));
    }

    /**
     * Guarda la confirmación: el instructor crea el registro; el administrador lo confirma después
     */
    public function confirmarStore(Request $request)
    {
        $user    = Auth::user();
        $fecha   = $request->input('fecha');
        $horario = Horario::find($request->input('horario_id'));
        if (!$horario || !$fecha) {
            return json_encode(array('horario_id' => array('El horario no existe.')));
        }
        $error = $this->errorConfirmar($user, $horario, $fecha);
        if ($error) {
            return json_encode(array('horario_id' => array($error)));
        }

        $error = DB::transaction(function () use ($user, $horario, $fecha) {
            $ahora = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
            if ($this->esInstructor($user)) {
                $obj = new Validahorario();
                $obj->horario_id            = $horario->id;
                $obj->fecha                 = $fecha;
                $obj->instructor_id         = $horario->instructor_id;
                $obj->situacion             = self::INSTRUCTOR_CONFIRMADO;
                $obj->fechainstructor       = $ahora;
                $obj->usuarioinstructor_id  = $user->id;
                $obj->save();
            } else {
                $obj = Validahorario::where('horario_id', '=', $horario->id)->where('fecha', '=', $fecha)->first();
                $obj->situacion        = self::ADMIN_CONFIRMADO;
                $obj->fechaadmin       = $ahora;
                $obj->usuarioadmin_id  = $user->id;
                $obj->save();
            }
        });
        return is_null($error) ? "OK" : $error;
    }

    /**
     * Motivo por el que el usuario no puede confirmar ese horario + fecha, o null si puede
     */
    private function errorConfirmar($user, $horario, $fecha)
    {
        $tieneReservas = Reserva::whereNull('deleted_at')
            ->where('horario_id', '=', $horario->id)
            ->where(DB::raw('DATE(fecha)'), '=', $fecha)
            ->exists();
        if (!$tieneReservas) {
            return 'Este horario no tiene reservas ese día.';
        }
        $validacion = Validahorario::where('horario_id', '=', $horario->id)->where('fecha', '=', $fecha)->first();

        if ($this->esInstructor($user)) {
            if ((int) $horario->instructor_id !== (int) $user->instructor_id) {
                return 'Este horario no es tuyo.';
            }
            if ($validacion) {
                return 'Ya confirmaste tu asistencia en este horario.';
            }
            $inicio = \Carbon\Carbon::parse($fecha . ' ' . $horario->horainicio);
            if ($inicio->gt(\Carbon\Carbon::now())) {
                return 'Podrás confirmar tu asistencia cuando empiece la clase (' . $inicio->format('d/m/Y H:i') . ').';
            }
            return null;
        }

        if ($this->esAdmin($user)) {
            if (!$validacion) {
                return 'Primero el instructor debe confirmar su asistencia.';
            }
            if ($validacion->situacion == self::ADMIN_CONFIRMADO) {
                return 'La asistencia del instructor ya fue confirmada.';
            }
            return null;
        }

        return 'No tienes permiso para confirmar asistencias.';
    }
}
