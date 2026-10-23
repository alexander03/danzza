{{--
|--------------------------------------------------------------------------
| Planes del CLIENTE (Activar plan / Cambiar plan)
|--------------------------------------------------------------------------
| Variables que envía ClienteController@planes:
|   $cliente   cliente autenticado (plan_id puede ser 0 / sin plan)
|   $planes    colección de planes ordenados por "orden"
|   $pendiente   Planpago PENDIENTE del cliente o null (solo uno a la vez)
|   $ultimoPago  último Planpago del cliente o null (para avisar si fue RECHAZADO)
|   $renovacion  'SIN_CREDITOS' / 'VENCIDO' si puede renovar su plan actual, o null
|
| Al elegir un plan se va a pagar (cliente.planes.pagar); el plan se asigna
| cuando el administrador valida el pago.
--}}
@extends('layouts.master-cliente')

@section('title', 'Planes | DANZZA')

@php
    $planActual = $cliente->plan;

    // Mismos términos que la lista pública de planes (app/plan/list-home.blade.php)
    $terminos = [
        'Todos los planes son personales e intransferibles.',
        'Las clases requieren reserva previa y están sujetas a disponibilidad y aforo.',
        'Las reservas pueden cancelarse o modificarse hasta 40 minutos antes del inicio de la clase.',
        'Las cancelaciones fuera de plazo o inasistencias se contabilizarán como clase utilizada.',
        'Las clases no utilizadas no son acumulables ni transferibles.',
        'La vigencia inicia desde la activación del plan.',
        'El Plan Diario permite una reserva por día.',
        'Los horarios pueden estar sujetos a cambios por motivos operativos.',
        'Al participar en las actividades de Danzza, autorizas el uso de fotografías y videos en los que aparezcas para fines promocionales y de comunicación de la marca.',
        'La adquisición de un plan implica la aceptación de estos términos y condiciones.',
    ];
@endphp

@push('styles')
<style>
    .planes-titulo {
        margin-bottom: 22px;
    }

    .planes-titulo h1 {
        margin: 0;
        font-size: 28px;
        font-weight: 800;
        color: var(--charcoal);
    }

    .planes-titulo-fila {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .planes-titulo span {
        color: var(--muted-dark);
    }

    .alerta-error {
        margin-bottom: 18px;
        padding: 12px 16px;
        border-radius: 12px;
        background: rgba(239, 68, 68, .15);
        color: #f87171;
        font-weight: 700;
    }

    .planes-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }

    .plan-card {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .plan-card.actual {
        border-color: var(--primary);
    }

    .plan-card .nombre {
        color: var(--primary);
        font-size: 22px;
        font-weight: 800;
    }

    /* El nombre del plan ya es verde lima: el "recomendado" va en blanco y más pequeño */
    .plan-card .plan-recomendado {
        color: #ffffff;
        font-size: 14px;
        font-weight: 600;
    }

    .plan-card .precio {
        font-size: 26px;
        font-weight: 800;
    }

    /* Precio normal tachado cuando hay precio de promoción (igual que list-home) */
    .plan-card .precio-anterior {
        margin-bottom: -8px;
        color: var(--muted);
        font-size: 16px;
        font-weight: 700;
        text-decoration: line-through;
        text-decoration-color: #ff4d4d;
        text-decoration-thickness: 2px;
    }

    .plan-card ul {
        margin: 0;
        padding-left: 18px;
        color: #ddd;
    }

    .plan-card .badge-actual {
        align-self: flex-start;
        padding: 2px 10px;
        border-radius: 20px;
        background: var(--primary);
        color: #070707;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 1px;
    }

    .plan-card form {
        margin-top: auto;
    }

    .plan-card .btn-dz {
        width: 100%;
        justify-content: center;
    }

    .volver {
        display: inline-block;
        margin-top: 22px;
        font-weight: 700;
    }

    .plan-card .motivo-renovar {
        padding: 8px 12px;
        border-radius: 10px;
        background: rgba(245, 158, 11, .15);
        color: #fbbf24;
        font-size: 13px;
        font-weight: 700;
    }

    .terminos {
        margin-top: 24px;
    }

    .terminos h2 {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0 0 16px;
        font-size: 16px;
        font-weight: 800;
        letter-spacing: 2px;
    }

    .terminos h2 i {
        color: var(--primary);
    }

    .terminos ul {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px 40px;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .terminos li {
        position: relative;
        padding-left: 18px;
        color: #ddd;
        font-size: 13px;
        line-height: 1.6;
    }

    .terminos li::before {
        content: "•";
        position: absolute;
        left: 0;
        color: var(--primary);
    }

    @media (max-width: 768px) {
        .terminos ul {
            grid-template-columns: 1fr;
        }
    }

    .aviso-plan {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 18px;
        padding: 14px 18px;
        border-radius: 12px;
        font-size: 14px;
    }

    .aviso-plan > i { font-size: 22px; }
    .aviso-plan > div { flex: 1; }

    .aviso-plan.validacion { background: rgba(59, 130, 246, .15); color: #1e3a8a; }
    .aviso-plan.validacion > i { color: #3b82f6; }
    .aviso-plan.rechazado { background: rgba(239, 68, 68, .15); color: #991b1b; }
    .aviso-plan.rechazado > i { color: #ef4444; }

    @media (max-width: 992px) {
        .planes-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .aviso-plan {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    @media (max-width: 576px) {
        .planes-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')

    <div class="planes-titulo">
        {{-- Título a la izquierda y "Ver horarios" a la derecha, en la misma línea --}}
        <div class="planes-titulo-fila">
            <h1>{{ $planActual ? 'Planes' : 'Elige tu plan' }}</h1>
            <a href="{{ route('cliente.reservar') }}" class="btn-dz btn-dz-outline">
                <i class="fa-regular fa-calendar-plus"></i> Ver horarios
            </a>
        </div>
        <span>
            @if ($planActual)
                Tu plan actual es <b>{{ $planActual->nombre }}</b>. Puedes cambiarlo cuando quieras.
            @else
                Aún no tienes un plan activo. Elige uno para empezar a reservar tus clases.
            @endif
        </span>
    </div>

    @if (session('error'))
        <div class="alerta-error">{{ session('error') }}</div>
    @endif

    @if ($pendiente)
        <div class="aviso-plan validacion">
            <i class="fa-solid fa-hourglass-half"></i>
            <div>
                Tu pago del plan <b>{{ data_get($pendiente->plan, 'nombre') }}</b> está <b>en validación</b>.
                Cuando lo validemos se activará tu plan. Mientras tanto no puedes elegir otro plan.
            </div>
            <a href="{{ route('cliente.planes.pagar', $pendiente->plan_id) }}" class="btn-dz btn-dz-outline">
                <i class="fa-regular fa-eye"></i> Ver pago
            </a>
        </div>
    @elseif ($ultimoPago && $ultimoPago->situacion == 'RECHAZADO')
        <div class="aviso-plan rechazado">
            <i class="fa-solid fa-circle-xmark"></i>
            <div>
                Tu último pago (plan <b>{{ data_get($ultimoPago->plan, 'nombre') }}</b>, operación {{ $ultimoPago->operacion }}) fue <b>rechazado</b>.
                @if (!empty($ultimoPago->observacion))
                    Motivo: {{ $ultimoPago->observacion }}.
                @endif
                Puedes volver a elegir tu plan y registrar el pago nuevamente.
            </div>
        </div>
    @endif

    <div class="planes-grid">
        @forelse ($planes as $plan)
            @php
                $esActual = $planActual && $planActual->id == $plan->id;
                $esPendiente = $pendiente && $pendiente->plan_id == $plan->id;
                // Plan actual sin créditos o vencido: se puede renovar (volver a pagarlo)
                $puedeRenovar = $esActual && $renovacion && !$pendiente;
                $montoPlan = $plan->preciopromocion > 0 ? $plan->preciopromocion : $plan->precio;
            @endphp
            <div class="card-dz plan-card {{ $esActual || $esPendiente ? 'actual' : '' }}">
                @if ($esActual && $esPendiente)
                    <span class="badge-actual">RENOVACIÓN EN VALIDACIÓN</span>
                @elseif ($esActual)
                    <span class="badge-actual">PLAN ACTUAL</span>
                @elseif ($esPendiente)
                    <span class="badge-actual">PAGO EN VALIDACIÓN</span>
                @endif
                @if ($puedeRenovar)
                    <div class="motivo-renovar">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        {{ $renovacion == 'VENCIDO' ? 'Tu plan venció. Renuévalo para seguir reservando.' : 'Ya usaste todos tus clases. Renueva tu plan para seguir reservando.' }}
                    </div>
                @endif
                <div class="nombre">
                    {{ $plan->nombre }}
                    @if (!empty($plan->recomendado))
                        {{-- "Recomendado" en la misma línea del nombre del plan, en otro color (igual que list-home) --}}
                        <span class="plan-recomendado">· {{ $plan->recomendado }}</span>
                    @endif
                </div>
                @if ($plan->preciopromocion > 0)
                    <div class="precio-anterior">S/ {{ number_format($plan->precio, 2) }}</div>
                    <div class="precio">S/ {{ number_format($plan->preciopromocion, 2) }}</div>
                @elseif (!is_null($plan->precio))
                    <div class="precio">S/ {{ number_format($plan->precio, 2) }}</div>
                @endif
                <ul>
                    @if (!empty($plan->descripcion))
                        <li>{{ $plan->descripcion }}</li>
                    @endif
                    @if (!is_null($plan->creditos))
                        <li>{{ $plan->creditos }} clases</li>
                    @endif
                    @if (!empty($plan->vigencia))
                        <li>Vigencia: {{ $plan->vigencia }} días</li>
                    @endif
                    @if (!empty($plan->nota))
                        <li>{{ $plan->nota }}</li>
                    @endif
                </ul>
                <form method="POST" action="{{ route('cliente.planes.store') }}"
                    @if ($puedeRenovar) onsubmit="return confirm('¿Deseas renovar tu plan {{ addslashes($plan->nombre) }}? Deberás pagar S/ {{ number_format($montoPlan, 2) }} y tus créditos se recargarán cuando validemos tu pago.');"
                    @elseif ($planActual && !$esActual && !$pendiente) onsubmit="return confirm('¿Deseas cambiar tu plan a {{ addslashes($plan->nombre) }}? Deberás pagar S/ {{ number_format($montoPlan, 2) }} y el cambio se hará cuando validemos tu pago.');" @endif>
                    @csrf
                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                    @if ($esPendiente)
                        <a href="{{ route('cliente.planes.pagar', $plan->id) }}" class="btn-dz btn-dz-outline">
                            <i class="fa-regular fa-eye"></i> Ver pago
                        </a>
                    @elseif ($puedeRenovar)
                        <button type="submit" class="btn-dz">
                            <i class="fa-solid fa-rotate-right"></i> Renovar
                        </button>
                    @elseif ($esActual)
                        <button type="button" class="btn-dz btn-dz-outline" disabled>Plan actual</button>
                    @elseif ($esPendiente)
                        <a href="{{ route('cliente.planes.pagar', $plan->id) }}" class="btn-dz btn-dz-outline">
                            <i class="fa-regular fa-eye"></i> Ver pago
                        </a>
                    @elseif ($pendiente)
                        <button type="button" class="btn-dz btn-dz-outline" disabled>Pago en validación</button>
                    @elseif ($planActual)
                        <button type="submit" class="btn-dz btn-dz-outline">
                            <i class="fa-solid fa-arrows-rotate"></i> Cambiar plan
                        </button>
                    @else
                        <button type="submit" class="btn-dz">
                            <i class="fa-solid fa-bolt"></i> Activar plan
                        </button>
                    @endif
                </form>
            </div>
        @empty
            <div class="card-dz">No hay planes disponibles por el momento.</div>
        @endforelse
    </div>

    <div class="card-dz terminos">
        <h2><i class="fa-solid fa-file-contract"></i> TÉRMINOS Y CONDICIONES</h2>
        <ul>
            @foreach ($terminos as $termino)
                <li>{{ $termino }}</li>
            @endforeach
        </ul>
    </div>

    <a href="{{ route('cliente.home') }}" class="volver">
        <i class="fa-solid fa-arrow-left"></i> {{ $planActual ? 'Volver al inicio' : 'Ahora no, ir al inicio' }}
    </a>

@endsection
