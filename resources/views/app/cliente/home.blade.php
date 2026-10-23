{{--
|--------------------------------------------------------------------------
| Inicio del CLIENTE (al loguearse)
|--------------------------------------------------------------------------
| Variables que debe enviar el controlador:
|   $cliente          objeto/array: nombre, fecha_nacimiento (Y-m-d, puede ser null)
|   $planActivo       objeto/array o null: nombre, fecha_vencimiento (Y-m-d)
|   $proximasReservas colección/array (puede estar vacía), cada item:
|                     fecha (Y-m-d), hora (H:i), sede, lugar
|
| Rutas que usa:
|   cliente.reservar  -> reserva de clase (solo si hay plan activo)
|   cliente.planes    -> pantalla de planes (Cambiar plan / Activar plan / Reservar sin plan)
|   cliente.reservas  -> Mis reservas
|   cliente.perfil    -> Mi perfil
--}}
@extends('layouts.master-cliente')

@section('title', 'Inicio | DANZZA')

@php
    use App\Cliente;
    $user                  = Auth::user();
    $cliente  = Cliente::find($user->cliente_id);
    $planActivo = $cliente->plan;
    // select explícito: con join + select * las columnas de horario (id, situacion...) pisan las de reserva
    $proximasReservas = $cliente->reservas()
        ->join('horario', 'reserva.horario_id', '=', 'horario.id')
        ->join('sucursal', 'reserva.sucursal_id', '=', 'sucursal.id')
        ->select('reserva.*', 'horario.horainicio', 'horario.horafin', 'sucursal.nombre as sede')
        ->whereNull('reserva.deleted_at')
        ->where('reserva.fecha', '>=', now()->format('Y-m-d'))
        ->where('horario.horainicio', '>=', now()->format('H:i:s'))
        ->orderBy('reserva.fecha', 'asc')
        ->orderBy('horario.horainicio', 'asc')
        ->take(3)
        ->get();
    $ruta = function ($nombre, $params = []) {
        return Route::has($nombre) ? route($nombre, $params) : '#';
    };

    $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Set', 'Oct', 'Nov', 'Dic'];
    $formatoFecha = function ($fecha) use ($meses) {
        $f = \Carbon\Carbon::parse($fecha);
        return $f->day . ' de ' . $meses[$f->month - 1] . '. ' . $f->year;
    };

    $nombreCliente = data_get($cliente, 'nombres');
    $nacimiento    = data_get($cliente, 'fecha_nacimiento');
    $esCumpleanos  = $nacimiento
        && \Carbon\Carbon::parse($nacimiento)->format('m-d') === \Carbon\Carbon::today()->format('m-d');

    // Detalle del plan activo: solo se muestran los datos que tengan valor
    $datosPlan = [];
    if ($planActivo) {
        $datosPlan = array_filter([
            'Clase'               => data_get($planActivo->clase, 'nombre'),
            'Precio'              => !is_null($planActivo->precio) ? 'S/ ' . number_format($planActivo->precio, 2) : null,
            'Clases del plan'     => $planActivo->clases,
            //'Créditos del plan'   => $planActivo->creditos,
            // 'Clases disponibles'  => $cliente->clases,
            'Clases disponibles'  => $cliente->creditos,
            'Sede'                => data_get($cliente->sucursal, 'nombre'),
            'Situación'           => $cliente->situacion,
        ], function ($valor) {
            return !is_null($valor) && $valor !== '';
        });
    }

    // Pago de plan en validación (primera vez o cambio) y vencimiento del plan vigente
    $pagoPendiente = \App\Planpago::where('cliente_id', '=', $cliente->id)
        ->where('situacion', '=', 'PENDIENTE')->orderBy('id', 'desc')->first();
    $pagoVigente = $planActivo
        ? \App\Planpago::where('cliente_id', '=', $cliente->id)->where('plan_id', '=', $planActivo->id)
            ->where('situacion', '=', 'VALIDADO')->orderBy('id', 'desc')->first()
        : null;
    $fechaVencimiento = $pagoVigente ? $pagoVigente->fechavencimiento : null;

    $urlPlanes   = $ruta('cliente.planes');
    // Sin plan también entra: puede ver sedes y horarios, pero no elegir lugar
    $urlReservar = $ruta('cliente.reservar');
@endphp

@push('styles')
<style>
    .saludo {
        margin-bottom: 22px;
    }

    .saludo span {
        display: block;
        color: var(--muted-dark);
        font-size: 18px;
    }

    .saludo h1 {
        margin: 2px 0 0;
        font-size: 30px;
        font-weight: 800;
        color: var(--charcoal);
    }

    .cumple {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 18px;
        padding: 16px 20px;
        border-radius: 16px;
        background: linear-gradient(90deg, #dfff00, #a9d000);
        color: #070707;
        font-weight: 700;
    }

    .cumple i {
        font-size: 28px;
    }

    .plan-activo {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
        border-color: var(--border);
    }

    .plan-activo .etiqueta {
        color: var(--muted);
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 2px;
    }

    .plan-activo .nombre {
        margin: 4px 0;
        color: var(--primary);
        font-size: 26px;
        font-weight: 800;
    }

    .plan-activo .vence {
        color: #ddd;
    }

    .pago-plan-pendiente {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 18px;
        border-color: rgba(59, 130, 246, .5);
        color: #ddd;
    }

    .pago-plan-pendiente > i {
        color: #93c5fd;
        font-size: 24px;
    }

    .pago-plan-pendiente > div {
        flex: 1;
    }

    @media (max-width: 768px) {
        .pago-plan-pendiente {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    .plan-detalle {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px 24px;
        margin-top: 14px;
    }

    .plan-detalle small {
        display: block;
        color: var(--muted);
        font-size: 12px;
    }

    .plan-detalle b {
        color: #fff;
        font-size: 16px;
    }

    .plan-descripcion {
        margin-top: 12px;
        color: #ddd;
    }

    @media (max-width: 768px) {
        .plan-detalle {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    .reservas {
        margin-bottom: 24px;
    }

    .reservas h2 {
        margin: 0 0 14px;
        font-size: 17px;
        font-weight: 800;
    }

    .reservas .vacio {
        color: var(--muted);
    }

    .reserva-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 0;
        border-top: 1px solid var(--line);
    }

    .reserva-item:first-of-type {
        border-top: none;
        padding-top: 0;
    }

    .reserva-item i {
        color: var(--primary);
        font-size: 22px;
    }

    .reserva-item small {
        display: block;
        color: var(--muted);
    }

    .reserva-item .datos {
        flex: 1;
        min-width: 0;
    }

    .reserva-item .btn-pagar {
        height: 36px;
        padding: 0 14px;
        font-size: 13px;
    }

    .reserva-item .btn-pagar i {
        color: #070707;
        font-size: 14px;
    }

    .badge-pendiente {
        display: inline-block;
        margin-top: 4px;
        padding: 2px 9px;
        border-radius: 20px;
        background: rgba(245, 158, 11, .18);
        color: #fbbf24;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 1px;
    }

    .reservas .ver-todas {
        display: inline-block;
        margin-top: 14px;
        font-size: 14px;
        font-weight: 700;
    }

    .accesos {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
    }

    .acceso {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
        min-height: 120px;
        padding: 18px 10px;
        border: 1px solid var(--line);
        border-radius: 18px;
        background: var(--card);
        color: #fff !important;
        font-weight: 700;
        text-align: center;
        transition: .25s;
    }

    .acceso i {
        color: var(--primary);
        font-size: 30px;
    }

    .acceso {
        box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
    }

    .acceso:hover {
        border-color: var(--primary);
        transform: translateY(-3px);
        box-shadow: 0 14px 34px rgba(0, 0, 0, .22);
    }

    @media (max-width: 768px) {
        .plan-activo {
            flex-direction: column;
            align-items: flex-start;
        }

        .accesos {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
@endpush

@section('content')

    <div class="saludo">
        <span>Hola,</span>
        <h1>{{ $nombreCliente }}</h1>
    </div>

    @if ($esCumpleanos)
        <div class="cumple">
            <i class="fa-solid fa-cake-candles"></i>
            <div>¡Feliz cumpleaños, {{ $nombreCliente }}! Todo el equipo Danzza te desea un gran día.</div>
        </div>
    @endif

    @if (session('mensaje'))
        <div class="cumple">
            <i class="fa-solid fa-circle-check"></i>
            <div>{{ session('mensaje') }}</div>
        </div>
    @endif

    @if ($pagoPendiente)
        <div class="card-dz pago-plan-pendiente">
            <i class="fa-solid fa-hourglass-half"></i>
            <div>
                <b>Pago en validación</b> del plan {{ data_get($pagoPendiente->plan, 'nombre') }}
                (S/ {{ number_format($pagoPendiente->monto, 2) }}).
                Activaremos tu plan cuando validemos tu pago.
            </div>
            <a href="{{ $ruta('cliente.planes.pagar', $pagoPendiente->plan_id) }}" class="btn-dz btn-dz-outline">
                <i class="fa-regular fa-eye"></i> Ver pago
            </a>
        </div>
    @endif

    <div class="card-dz plan-activo">
        @if ($planActivo)
            <div>
                <div class="etiqueta">Plan activo</div>
                <div class="nombre">{{ data_get($planActivo, 'nombre') }}</div>
                @if ($fechaVencimiento)
                    <div class="vence">Vence el {{ $formatoFecha($fechaVencimiento) }}</div>
                @endif
                @if (count($datosPlan))
                    <div class="plan-detalle">
                        @foreach ($datosPlan as $etiqueta => $valor)
                            @if ($etiqueta!="Clase" && $etiqueta!="Créditos disponibles")
                            <div>
                                <small>{{ $etiqueta }}</small>
                                <b>{{ $valor }}</b>
                            </div>
                            @endif
                        @endforeach
                    </div>
                @endif
                @if (!empty($planActivo->descripcion))
                    <!--div class="plan-descripcion">{{ $planActivo->descripcion }}</div-->
                @endif
            </div>
            <a href="{{ $urlPlanes }}" class="btn-dz btn-dz-outline">
                <i class="fa-solid fa-arrows-rotate"></i> Activar plan
            </a>
        @else
            <div>
                <div class="etiqueta">Plan activo</div>
                <div class="vence">Aún no tienes un plan activo.</div>
            </div>
            <a href="{{ $urlPlanes }}" class="btn-dz">
                <i class="fa-solid fa-bolt"></i> Activar plan
            </a>
        @endif
    </div>

    <div class="card-dz reservas">
        <h2>Próximas reservas</h2>
        @forelse ($proximasReservas as $reserva)
            <div class="reserva-item">
                <i class="fa-regular fa-calendar-check"></i>
                <div class="datos">
                    <strong>{{ $formatoFecha($reserva->fecha) }} · {{ \Carbon\Carbon::parse($reserva->horainicio)->format('g:i a') }}</strong>
                    <small>{{ $reserva->sede }} · Lugar N.° {{ $reserva->ubicacion }}</small>
                </div>
            </div>
        @empty
            <div class="vacio">Aún no tienes reservas próximas</div>
        @endforelse
        <a href="{{ $ruta('cliente.reservas') }}" class="ver-todas">
            Ver todas mis reservas <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>

    <div class="accesos">
        <a href="{{ $urlReservar }}" class="acceso">
            <i class="fa-regular fa-calendar-plus"></i>
            Ver horarios
        </a>
        <a href="{{ $urlPlanes }}" class="acceso">
            <i class="fa-solid {{ $planActivo ? 'fa-arrows-rotate' : 'fa-bolt' }}"></i>
            {{ $planActivo ? 'Ver planes' : 'Ver planes' }}
        </a>
        <a href="{{ $ruta('cliente.reservas') }}" class="acceso">
            <i class="fa-solid fa-list-check"></i>
            Mis reservas
        </a>
        <a href="{{ $ruta('cliente.perfil') }}" class="acceso">
            <i class="fa-regular fa-user"></i>
            Mi perfil
        </a>
    </div>

@endsection
