{{--
|--------------------------------------------------------------------------
| Pagar plan con Yape (primera vez o cambio de plan)
|--------------------------------------------------------------------------
| Variables que envía ClienteController@planesPagar:
|   $cliente  cliente autenticado
|   $plan     plan elegido (o el del pago en validación)
|   $pago     Planpago PENDIENTE del cliente o null
|   $monto    monto a pagar (precio de promoción si existe)
|
| Rutas que usa:
|   POST cliente.planes.pagar.store   operacion, fechapago
|        (queda PENDIENTE; el plan se asigna cuando el administrador valida)
|   cliente.planes                    -> Cancelar / volver
|
| Imagen del QR: public/image/qr-yape.jpeg
--}}
@extends('layouts.master-cliente')

@section('title', 'Paga tu plan | DANZZA')

@php
    $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Set', 'Oct', 'Nov', 'Dic'];
    $formatoFecha = function ($fecha) use ($meses) {
        $f = \Carbon\Carbon::parse($fecha);
        return $f->day . ' de ' . $meses[$f->month - 1] . '. ' . $f->year;
    };

    $nombreCompleto = trim($cliente->nombres . ' ' . $cliente->apellidopaterno . ' ' . $cliente->apellidomaterno);
    $esCambio  = $cliente->plan && $cliente->plan_id != $plan->id;
    // Renovación: vuelve a pagar su plan actual (sin créditos o vencido)
    $esRenovacion = isset($esRenovacion) ? $esRenovacion : ($cliente->plan_id == $plan->id);
    $urlPlanes = route('cliente.planes');
    $whatsapp  = '51944199037';

    $textoWhatsapp = '';
    if ($pago) {
        $textoWhatsapp = 'Hola Danzza, envío el pago ' . ($esRenovacion ? 'de la renovación de mi plan ' : 'de mi plan ') . $plan->nombre . "\n"
            . '• Monto: S/ ' . number_format($pago->monto, 2) . "\n"
            . '• Fecha de pago: ' . $formatoFecha($pago->fechapago) . "\n"
            . '• N.° de operación: ' . $pago->operacion . "\n"
            . '• Nombre completo: ' . $nombreCompleto;
    }
@endphp

@push('styles')
<style>
    .pago h1 {
        margin: 0 0 6px;
        font-size: 26px;
        font-weight: 800;
        color: var(--charcoal);
    }

    .pago .plan-info {
        margin-bottom: 22px;
        color: var(--muted-dark);
    }

    .pago .plan-info strong {
        color: var(--charcoal);
    }

    .pago-grid {
        display: grid;
        grid-template-columns: 260px 1fr;
        gap: 22px;
        align-items: start;
    }

    .qr {
        text-align: center;
    }

    .qr img {
        display: block;
        width: 100%;
        max-width: 220px;
        margin: 0 auto 10px;
        border-radius: 12px;
        background: #fff;
        padding: 8px;
    }

    .qr small {
        color: var(--muted);
    }

    .form-pago h2 {
        margin: 0 0 6px;
        font-size: 17px;
        font-weight: 800;
    }

    .form-pago .ayuda {
        margin-bottom: 16px;
        color: var(--muted);
        font-size: 14px;
    }

    .campo {
        margin-bottom: 14px;
    }

    .campo label {
        display: block;
        margin-bottom: 6px;
        color: #ddd;
        font-weight: 600;
    }

    .campo input {
        width: 100%;
        height: 46px;
        padding: 0 16px;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: rgba(5, 7, 8, .75);
        color: #fff;
        font-size: 15px;
        outline: none;
        color-scheme: dark;
    }

    .campo input:focus {
        border-color: var(--primary);
    }

    .campo .error {
        display: block;
        margin-top: 4px;
        color: #ff9999;
        font-size: 13px;
    }

    .acciones {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 6px;
    }

    /* Pago recibido */
    .recibido {
        text-align: center;
    }

    .recibido .icono {
        width: 70px;
        height: 70px;
        margin: 0 auto 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: rgba(223, 255, 0, .12);
        color: var(--primary);
        font-size: 32px;
    }

    .recibido h2 {
        margin: 0 0 10px;
        font-size: 22px;
        font-weight: 800;
    }

    .recibido p {
        color: #ddd;
    }

    .recibido ul {
        display: inline-block;
        margin: 6px 0 14px;
        padding: 0;
        list-style: none;
        text-align: left;
    }

    .recibido li {
        margin: 6px 0;
        color: #ddd;
    }

    .recibido li i {
        width: 22px;
        color: var(--primary);
    }

    .recibido li b {
        margin-left: 6px;
        color: #fff;
    }

    .estado-pago {
        display: inline-block;
        margin-bottom: 14px;
        padding: 4px 12px;
        border-radius: 20px;
        background: rgba(59, 130, 246, .18);
        color: #93c5fd;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 1px;
    }

    .recibido .acciones {
        justify-content: center;
        margin-top: 16px;
    }

    .btn-whatsapp {
        background: #25d366 !important;
        color: #ffffff !important;
    }

    .btn-whatsapp:hover {
        box-shadow: 0 6px 20px rgba(37, 211, 102, .40) !important;
    }

    @media (max-width: 768px) {
        .pago-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')

    <a href="{{ $urlPlanes }}" class="link-home">
        <i class="fa-solid fa-arrow-left"></i> Volver a planes
    </a>

    <div class="pago">
        <h1>{{ $pago ? 'Mi pago' : ($esRenovacion ? 'Renueva tu plan' : ($esCambio ? 'Paga tu cambio de plan' : 'Paga tu plan')) }}</h1>
        <div class="plan-info">
            Plan <strong>{{ $plan->nombre }}</strong> ·
            Total <strong>S/ {{ number_format($monto, 2) }}</strong>
            @if (!empty($plan->vigencia))
                · Vigencia <strong>{{ $plan->vigencia }} días</strong>
            @endif
            @if ($esCambio && !$pago)
                <br>Tu plan actual ({{ $cliente->plan->nombre }}) seguirá activo hasta que validemos este pago.
            @elseif ($esRenovacion && !$pago)
                <br>Cuando validemos tu pago se recargarán tus créditos{{ !is_null($plan->creditos) ? ' (' . $plan->creditos . ')' : '' }}
                @if (!empty($plan->vigencia)) y tu vigencia de {{ $plan->vigencia }} días empezará de nuevo @endif.
            @endif
        </div>

        @if ($pago)
            {{-- Pago registrado, esperando validación --}}
            <div class="card-dz recibido">
                <div class="icono"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
                <h2>¡Hemos recibido tu pago!</h2>
                <span class="estado-pago">PAGO EN VALIDACIÓN</span>
                <p>Estos son los datos de tu pago por <strong>Yape</strong>:</p>
                <ul>
                    <li><i class="fa-regular fa-calendar"></i> Fecha de pago: <b>{{ $formatoFecha($pago->fechapago) }}</b></li>
                    <li><i class="fa-solid fa-hashtag"></i> N.° de operación: <b>{{ $pago->operacion }}</b></li>
                    <li><i class="fa-solid fa-money-bill"></i> Monto: <b>S/ {{ number_format($pago->monto, 2) }}</b></li>
                    <li><i class="fa-regular fa-user"></i> Nombre completo: <b>{{ $nombreCompleto }}</b></li>
                </ul>
                <p>Envíanos estos datos por WhatsApp. Una vez validado el pago, <strong>{{ $esRenovacion ? 'renovaremos tu plan y recargaremos tus créditos.' : 'activaremos tu plan.' }}</strong> 💃✨</p>

                <div class="acciones">
                    <a href="https://wa.me/{{ $whatsapp }}?text={{ rawurlencode($textoWhatsapp) }}" target="_blank" class="btn-dz btn-whatsapp">
                        <i class="fa-brands fa-whatsapp"></i> Enviar por WhatsApp
                    </a>
                    <a href="{{ route('cliente.home') }}" class="btn-dz btn-dz-outline">
                        <i class="fa-solid fa-house"></i> Ir al inicio
                    </a>
                </div>
            </div>
        @else
            {{-- QR + datos del voucher --}}
            <div class="pago-grid">
                <div class="card-dz qr">
                    <img src="{{ asset('image/qr-yape.jpeg') }}" alt="QR de Yape">
                    <small>Escanea el QR con tu app Yape</small>
                </div>

                <form method="POST" action="{{ route('cliente.planes.pagar.store', $plan->id) }}" class="card-dz form-pago" autocomplete="off">
                    @csrf
                    <h2>Datos del voucher</h2>
                    <div class="ayuda">
                        Paga <b>S/ {{ number_format($monto, 2) }}</b> con Yape y luego ingresa el N.° de operación y la fecha del voucher.
                    </div>

                    <div class="campo">
                        <label for="operacion">N.° de operación</label>
                        <input type="text" id="operacion" name="operacion" maxlength="30" placeholder="Ej. 12345678"
                               value="{{ old('operacion') }}" required>
                        @if ($errors->has('operacion'))
                            <span class="error">{{ $errors->first('operacion') }}</span>
                        @endif
                    </div>

                    <div class="campo">
                        <label for="fechapago">Fecha del voucher</label>
                        <input type="date" id="fechapago" name="fechapago"
                               value="{{ old('fechapago', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                        @if ($errors->has('fechapago'))
                            <span class="error">{{ $errors->first('fechapago') }}</span>
                        @endif
                    </div>

                    <div class="acciones">
                        <button type="submit" class="btn-dz">
                            <i class="fa-solid fa-check"></i> Confirmar
                        </button>
                        <a href="{{ $urlPlanes }}" class="btn-dz btn-dz-outline">Cancelar</a>
                    </div>
                </form>
            </div>
        @endif
    </div>

@endsection
