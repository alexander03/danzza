{{--
|--------------------------------------------------------------------------
| Pagar reserva (por ahora solo QR de Yape)
|--------------------------------------------------------------------------
| Llega desde reservar.blade al confirmar la reserva.
|
| Variables que envía el controlador (ClienteController@pagar):
|   $reserva  objeto: id, codigo, fecha, ubicacion, sede, horainicio, horafin,
|             nombres, apellidopaterno, apellidomaterno
|
| Rutas que usa:
|   cliente.pagar.store  -> POST operacion, fechapago
|   cliente.home         -> Cancelar / volver
|
| Imagen del QR: public/image/qr-yape.png
--}}
@extends('layouts.master-cliente')

@section('title', 'Pagar reserva | DANZZA')

@php
    $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Set', 'Oct', 'Nov', 'Dic'];
    // $esGrupo = true cuando $reserva es un Reservagrupo (rango de fechas, un solo pago)
    $esGrupo = isset($esGrupo) && $esGrupo;
    if ($esGrupo) {
        $fi = \Carbon\Carbon::parse($reserva->fechainicio);
        $ff = \Carbon\Carbon::parse($reserva->fechafin);
        $fechaTexto = $fi->day . ' de ' . $meses[$fi->month - 1] . '. al ' . $ff->day . ' de ' . $meses[$ff->month - 1] . '. '
                    . $ff->year . ' (' . $reserva->dias . ' días)';
    } else {
        $f = \Carbon\Carbon::parse($reserva->fecha);
        $fechaTexto = $f->day . ' de ' . $meses[$f->month - 1] . '. ' . $f->year;
    }
    $horaTexto  = \Carbon\Carbon::parse($reserva->horario->horainicio)->format('g:i a')
                . ' - ' . \Carbon\Carbon::parse($reserva->horario->horafin)->format('g:i a');

    $cliente        = $reserva->cliente;
    $nombreCompleto = $cliente ? trim($cliente->nombres . ' ' . $cliente->apellidopaterno . ' ' . $cliente->apellidomaterno) : '';
    $sede           = $reserva->sucursal ? $reserva->sucursal->nombre : '';
    $urlHome     = Route::has('cliente.home') ? route('cliente.home') : url('/');
    $urlGuardar  = $esGrupo ? route('cliente.pagar.grupo.store', $reserva->id) : route('cliente.pagar.store', $reserva->id);
    $whatsapp    = '51944199037';
    $yaPagado    = !empty($reserva->operacion);
    $pagoValidado = $yaPagado && in_array($reserva->situacion, ['PAGO VALIDADO', 'INGRESO VALIDADO']);
    $fechaPagoTexto = '';
    if ($reserva->fechapago) {
        $fp = \Carbon\Carbon::parse($reserva->fechapago);
        $fechaPagoTexto = $fp->day . ' de ' . $meses[$fp->month - 1] . '. ' . $fp->year;
    }
@endphp

@push('styles')
<style>
    .pago-titulo {
        margin: 0 0 18px;
        font-size: 26px;
        font-weight: 800;
        color: var(--charcoal);
    }

    .pago-resumen {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 18px;
    }

    .pago-resumen div small {
        display: block;
        color: var(--muted);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .pago-resumen div strong {
        color: var(--primary);
    }

    .pago-grid {
        display: grid;
        grid-template-columns: 260px 1fr;
        gap: 24px;
        align-items: start;
    }

    .qr-box {
        padding: 14px;
        border-radius: 16px;
        background: #ffffff;
        text-align: center;
    }

    .qr-box img {
        width: 100%;
        max-width: 230px;
        display: block;
        margin: 0 auto;
    }

    .qr-box span {
        display: block;
        margin-top: 8px;
        color: #742284;
        font-weight: 800;
        letter-spacing: 1px;
    }

    .pago-form h2 {
        margin: 0 0 6px;
        font-size: 17px;
        font-weight: 800;
    }

    .pago-form .ayuda {
        margin-bottom: 16px;
        color: var(--muted);
        font-size: 14px;
    }

    .pago-form label {
        display: block;
        margin-bottom: 6px;
        color: #ddd;
        font-size: 13px;
        font-weight: 700;
    }

    .pago-form input {
        width: 100%;
        height: 46px;
        margin-bottom: 14px;
        padding: 0 14px;
        border: 1px solid rgba(255, 255, 255, .15);
        border-radius: 12px;
        background: rgba(255, 255, 255, .06);
        color: #fff;
        font-size: 15px;
        outline: none;
        color-scheme: dark;
    }

    .pago-form input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(223, 255, 0, .15);
    }

    .pago-acciones {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 4px;
    }

    .casi-listo {
        display: none;
        text-align: center;
    }

    .casi-listo.visible {
        display: block;
    }

    .casi-listo .icono {
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

    .casi-listo h2 {
        margin: 0 0 10px;
        font-size: 22px;
        font-weight: 800;
    }

    .casi-listo p {
        color: #ddd;
    }

    .casi-listo ul {
        display: inline-block;
        margin: 6px 0 14px;
        padding: 0;
        list-style: none;
        text-align: left;
    }

    .casi-listo li {
        margin: 6px 0;
        color: #ddd;
    }

    .casi-listo li i {
        width: 22px;
        color: var(--primary);
    }

    .casi-listo li b {
        margin-left: 6px;
        color: #fff;
    }

    .estado-pago {
        display: inline-block;
        margin-bottom: 14px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 1px;
    }

    .estado-pago.validacion { background: rgba(59, 130, 246, .18); color: #93c5fd; }
    .estado-pago.validado   { background: rgba(223, 255, 0, .15);  color: var(--primary); }

    .casi-listo .pago-acciones {
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
        .pago-resumen {
            grid-template-columns: repeat(2, 1fr);
        }

        .pago-grid {
            grid-template-columns: 1fr;
        }

        .qr-box {
            max-width: 260px;
            margin: 0 auto;
        }
    }
</style>
@endpush

@section('content')

    <a href="{{ $urlHome }}" class="link-home">
        <i class="fa-solid fa-arrow-left"></i> Volver al inicio
    </a>

    <h1 class="pago-titulo">{{ $yaPagado ? 'Mi pago' : 'Paga tu reserva' }}</h1>

    <div class="card-dz pago-resumen">
        <div><small>Sede</small><strong>{{ $sede }}</strong></div>
        <div><small>{{ $esGrupo ? 'Fechas' : 'Fecha' }}</small><strong>{{ $fechaTexto }}</strong></div>
        <div><small>Horario</small><strong>{{ $horaTexto }}</strong></div>
        <div><small>Lugar</small><strong>N.° {{ $reserva->ubicacion }}</strong></div>
    </div>

    {{-- PASO 1: QR + datos del voucher --}}
    <div class="card-dz" id="pasoPago" @if ($yaPagado) style="display:none" @endif>
        <div class="pago-grid">
            <div class="qr-box">
                <img src="{{ asset('image/qr-yape.jpeg') }}" alt="QR de Yape">
                <span>YAPE</span>
            </div>

            <form class="pago-form" id="formPago" autocomplete="off">
                <h2>Datos del voucher</h2>
                <div class="ayuda">
                    Escanea el QR con Yape y luego ingresa el N.° de operación y la fecha del voucher
                    para poder confirmar tu pago.
                    @if ($esGrupo)
                        <br>Este pago cubre los <strong>{{ $reserva->dias }} días</strong> de tu reserva.
                    @endif
                </div>

                <label for="operacion">N.° de operación</label>
                <input type="text" id="operacion" name="operacion" maxlength="30" placeholder="Ej. 12345678">

                <label for="fechapago">Fecha del voucher</label>
                <input type="date" id="fechapago" name="fechapago" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}">

                <div class="pago-acciones">
                    <button type="submit" class="btn-dz" id="btnConfirmarPago" disabled>
                        <i class="fa-solid fa-check"></i> Confirmar
                    </button>
                    <a href="{{ $urlHome }}" class="btn-dz btn-dz-outline">
                        <i class="fa-solid fa-xmark"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- PASO 2: mensaje final --}}
    <div class="card-dz casi-listo {{ $yaPagado ? 'visible' : '' }}" id="pasoListo">
        <div class="icono"><i class="fa-solid {{ $pagoValidado ? 'fa-circle-check' : 'fa-wand-magic-sparkles' }}"></i></div>
        <h2>¡Hemos recibido tu pago!</h2>
        <span class="estado-pago {{ $pagoValidado ? 'validado' : 'validacion' }}" id="estadoPago">
            {{ $pagoValidado ? 'PAGO VALIDADO' : 'PAGO EN VALIDACIÓN' }}
        </span>
        <p>Estos son los datos de tu pago por <strong>Yape</strong>:</p>
        <ul>
            <li><i class="fa-regular fa-calendar"></i> Fecha de pago: <b id="vFechapago">{{ $fechaPagoTexto }}</b></li>
            <li><i class="fa-solid fa-hashtag"></i> N.° de operación: <b id="vOperacion">{{ $reserva->operacion }}</b></li>
            <li><i class="fa-regular fa-user"></i> Nombre completo: <b>{{ $nombreCompleto }}</b></li>
        </ul>
        @if ($pagoValidado)
            <p><strong>Tu reserva está confirmada.</strong> ¡Te esperamos! 💃✨</p>
        @else
            <p>Envíanos estos datos por WhatsApp. Una vez validado el pago, <strong>confirmaremos tu reserva.</strong> 💃✨</p>
        @endif

        <div class="pago-acciones">
            <a href="#" target="_blank" class="btn-dz btn-whatsapp" id="btnWhatsapp">
                <i class="fa-brands fa-whatsapp"></i> Enviar por WhatsApp
            </a>
            <a href="{{ $urlHome }}" class="btn-dz btn-dz-outline">
                <i class="fa-solid fa-house"></i> Ir al inicio
            </a>
        </div>
    </div>

@endsection

@push('scripts')
<script>
$(function () {

    var datos = {!! json_encode([
        'guardar'   => $urlGuardar,
        'whatsapp'  => $whatsapp,
        'nombre'    => $nombreCompleto,
        'codigo'    => $reserva->codigo,
        'reserva'   => $sede . ' · ' . $fechaTexto . ' · ' . $horaTexto . ' · Lugar N.° ' . $reserva->ubicacion,
        'operacion' => $reserva->operacion ?? '',
        'fechapago' => $reserva->fechapago ?? '',
    ]) !!};

    function linkWhatsapp(operacion, fechapago) {
        var texto = 'Hola Danzza, envío el pago de mi reserva ' + datos.codigo + '\n'
            + datos.reserva + '\n'
            + '• Fecha de pago: ' + fechapago + '\n'
            + '• N.° de operación: ' + operacion + '\n'
            + '• Nombre completo: ' + datos.nombre;
        return 'https://wa.me/' + datos.whatsapp + '?text=' + encodeURIComponent(texto);
    }

    if (datos.operacion) {
        $('#btnWhatsapp').attr('href', linkWhatsapp(datos.operacion, datos.fechapago));
    }

    // Confirmar solo se habilita con operación y fecha
    $('#operacion, #fechapago').on('input change', function () {
        var ok = $.trim($('#operacion').val()) !== '' && $('#fechapago').val() !== '';
        $('#btnConfirmarPago').prop('disabled', !ok);
    });

    $('#formPago').on('submit', function (e) {
        e.preventDefault();

        var operacion = $.trim($('#operacion').val());
        var fechapago = $('#fechapago').val();
        if (!operacion || !fechapago) {
            toastr.warning('Debe ingresar operación y fecha del voucher para poder confirmar.');
            return;
        }

        var $btn = $('#btnConfirmarPago').prop('disabled', true);

        $.post(datos.guardar, { operacion: operacion, fechapago: fechapago })
            .done(function (r) {
                if (r && r.ok) {
                    $('#btnWhatsapp').attr('href', linkWhatsapp(operacion, fechapago));
                    // Muestra los datos recién registrados al lado de cada etiqueta
                    var p = fechapago.split('-');
                    var meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Set', 'Oct', 'Nov', 'Dic'];
                    $('#vFechapago').text(parseInt(p[2], 10) + ' de ' + meses[parseInt(p[1], 10) - 1] + '. ' + p[0]);
                    $('#vOperacion').text(operacion);
                    $('#pasoPago').hide();
                    $('#pasoListo').addClass('visible');
                    window.scrollTo(0, 0);
                } else {
                    toastr.error((r && r.mensaje) || 'No se pudo registrar el pago.');
                    $btn.prop('disabled', false);
                }
            })
            .fail(function (xhr) {
                var r = xhr.responseJSON;
                toastr.error((r && r.mensaje) || 'No se pudo registrar el pago. Inténtalo nuevamente.');
                $btn.prop('disabled', false);
            });
    });

});
</script>
@endpush
