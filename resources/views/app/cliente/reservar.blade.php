{{--
|--------------------------------------------------------------------------
| Reservar clase (asistente de 3 pasos + confirmación)
|--------------------------------------------------------------------------
| Sin plan activo o sin créditos: se muestra un aviso y solo se pueden ver
| los pasos 1 y 2 (sede y horarios); el botón del horario lleva a cliente.planes.
|
| Variables que debe enviar el controlador:
|   $sedes       colección/array, cada item: id, nombre
|   $planActivo  objeto/array o null: nombre
|
| Rutas (AJAX) y lo que deben responder:
|
|   GET  cliente.horarios   ?sede_id=1&fecha=2026-10-05
|        -> [ { "id": 7, "hora": "08:00", "ocupados": 12, "capacidad": 30 }, ... ]
|
|   GET  cliente.lugares    ?horario_id=7&fecha=2026-10-05
|        -> { "capacidad": 30, "ocupados": [3, 5, 12], "instructor": "Nombre" }
|
|   POST cliente.reservar.store   horario_id, fecha, lugar
|        -> { "ok": true,  "mensaje": "Reserva confirmada" }
|        -> { "ok": false, "mensaje": "El lugar ya fue tomado" }
|
|   cliente.home   -> flecha de regresar / Cancelar / después de reservar
|   cliente.planes -> si no hay plan activo
--}}
@extends('layouts.master-cliente')

@section('title', 'Reservar clase | DANZZA')

@php
    use App\Cliente;
    use App\Sucursal;
    $user                  = Auth::user();
    $cliente  = Cliente::find($user->cliente_id);
    $planActivo = $cliente->plan;
    // Cada reserva consume 1 crédito (en un rango, 1 por día)
    $creditos = (int) $cliente->creditos;
    // Sin plan activo o sin créditos solo puede ver sedes y horarios (pasos 1 y 2), no elegir lugar
    $puedeReservar = $planActivo && $creditos >= 1;
    $sedes = Sucursal::all();
    $ruta = function ($nombre) {
        return Route::has($nombre) ? route($nombre) : '#';
    };

    $dias  = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
    $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Set', 'Oct', 'Nov', 'Dic'];

    $proximosDias = [];
    for ($i = 0; $i < 7; $i++) {
        $f = \Carbon\Carbon::today()->addDays($i);
        $proximosDias[] = [
            'fecha'  => $f->format('Y-m-d'),
            'dia'    => $i === 0 ? 'Hoy' : $dias[$f->dayOfWeek],
            'numero' => $f->day,
            'mes'    => $meses[$f->month - 1],
            'texto'  => $dias[$f->dayOfWeek] . ' ' . $f->day . ' de ' . $meses[$f->month - 1] . '. ' . $f->year,
        ];
    }

    // Modo "Varios días": se puede reservar hasta N días desde hoy
    // (N = Configuracion id 1, campo valor, lo define el administrador; por defecto 30)
    $maxDiasRango = \App\Http\Controllers\ClienteController::diasMaximoRango();
    $rangoMin = \Carbon\Carbon::today()->format('Y-m-d');
    $rangoMax = \Carbon\Carbon::today()->addDays($maxDiasRango - 1)->format('Y-m-d');

    $urlHome = $ruta('cliente.home');
@endphp

@push('styles')
<style>
    .paso { display: none; }
    .paso.activo { display: block; }

    .paso-num {
        color: black;
        font-size: 14px;
        letter-spacing: 1px;
    }

    .paso h1 {
        margin: 4px 0 20px;
        font-size: 26px;
        font-weight: 800;
    }

    .opciones {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
    }

    .opcion {
        width: 100%;
        padding: 18px 20px;
        border: 1px solid var(--line);
        border-radius: 16px;
        background: var(--card);
        color: #fff;
        text-align: left;
        cursor: pointer;
        transition: .25s;
    }

    .opcion:hover:not(:disabled) {
        border-color: var(--primary);
    }

    .opcion:disabled {
        opacity: .45;
        cursor: not-allowed;
    }

    .opcion strong {
        display: block;
        font-size: 19px;
    }

    .opcion small {
        display: block;
        margin-top: 4px;
        color: var(--muted);
    }

    .opcion .estado {
        display: inline-block;
        margin-top: 8px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 1px;
        color: var(--libre);
    }

    .opcion .estado.lleno { color: var(--ocupado); }

    /* boton visible dentro de cada horario para indicar que se puede elegir */
    .opcion .btn-elegir {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 14px;
        padding: 10px 14px;
        border-radius: 12px;
        background: var(--primary);
        color: #070707;
        font-size: 14px;
        font-weight: 800;
        transition: .25s;
    }

    .opcion:hover:not(:disabled) .btn-elegir {
        filter: brightness(1.08);
    }

    .opcion:disabled .btn-elegir {
        background: transparent;
        border: 1px solid var(--line);
        color: var(--muted);
    }

    .ayuda-horario {
        margin: -6px 0 14px;
        color: black;
        font-size: 14px;
    }

    .ayuda-horario i { color: var(--primary); margin-right: 6px; }

    /* dias */
    .dias {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        overflow-x: auto;
        padding-bottom: 4px;
    }

    .dia {
        flex: 0 0 72px;
        padding: 10px 0;
        border: 1px solid var(--line);
        border-radius: 14px;
        background: var(--card);
        color: #fff;
        text-align: center;
        cursor: pointer;
        transition: .25s;
    }

    .dia span { display: block; font-size: 12px; color: var(--muted); }
    .dia strong { display: block; font-size: 22px; }

    .dia.activo {
        border-color: var(--primary);
        background: var(--primary);
        color: #070707;
    }

    .dia.activo span { color: #070707; }

    /* modo: un dia / varios dias */
    .modos {
        display: inline-flex;
        margin-bottom: 18px;
        padding: 4px;
        border: 1px solid var(--line);
        border-radius: 14px;
        background: var(--card);
    }

    .modo {
        padding: 8px 18px;
        border: none;
        border-radius: 10px;
        background: transparent;
        color: #fff;
        font-weight: 700;
        cursor: pointer;
        transition: .2s;
    }

    .modo.activo {
        background: var(--primary);
        color: #070707;
    }

    .rango {
        display: none;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 12px;
        margin-bottom: 20px;
    }

    .rango.visible { display: flex; }

    .rango label {
        display: block;
        margin-bottom: 6px;
        color: var(--muted);
        font-size: 13px;
        font-weight: 700;
    }

    .rango input {
        height: 44px;
        padding: 0 12px;
        border: 1px solid var(--line);
        border-radius: 12px;
        background: var(--card);
        color: #fff;
        color-scheme: dark;
    }

    .rango input:focus {
        outline: none;
        border-color: var(--primary);
    }

    .rango .btn-dz { height: 44px; }

    .aviso-rango { display: none; }

    .creditos-tag {
        display: inline-flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
        margin-bottom: 16px;
        padding: 6px 14px;
        border-radius: 20px;
        background: var(--charcoal);
        color: #fff;
        font-size: 14px;
    }

    .creditos-tag i,
    .creditos-tag b { color: var(--primary); }

    .creditos-tag small { color: var(--muted); }

    .mensaje-vacio {
        color: black;
        padding: 10px 0;
    }

    /* plano */
    .capacidad {
        margin: -12px 0 16px;
        color: black;
    }

    .plano {
        padding: 18px;
    }

    .instructor {
        margin-bottom: 18px;
        padding: 10px;
        border-radius: 10px;
        background: rgba(223, 255, 0, .10);
        border: 1px dashed var(--border);
        color: var(--primary);
        font-weight: 800;
        letter-spacing: 2px;
        text-align: center;
    }

    .lugares {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 10px;
        max-width: 420px;
        margin: 0 auto;
    }

    .lugar {
        aspect-ratio: 1 / 1;
        border: none;
        border-radius: 10px;
        background: var(--libre);
        color: #fff;
        font-weight: 800;
        cursor: pointer;
        transition: .15s;
    }

    .lugar:hover:not(:disabled) { transform: scale(1.06); }

    .lugar.ocupado {
        background: var(--ocupado);
        cursor: not-allowed;
        opacity: .85;
    }

    .lugar-ocupado-msg {
        display: none;
        max-width: 420px;
        margin: 16px auto 0;
        padding: 10px 14px;
        border-radius: 10px;
        background: rgba(239, 68, 68, .15);
        color: #f87171;
        font-size: 14px;
        font-weight: 700;
    }

    .lugar-ocupado-msg i { margin-right: 6px; }

    .lugar.elegido {
        background: var(--elegido);
        color: #070707;
    }

    .leyenda {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 18px;
        margin: 18px 0 22px;
        color: black;
        font-size: 14px;
    }

    .leyenda span::before {
        content: "";
        display: inline-block;
        width: 14px;
        height: 14px;
        margin-right: 6px;
        border-radius: 4px;
        vertical-align: -2px;
    }

    .leyenda .l-libre::before { background: var(--libre); }
    .leyenda .l-ocupado::before { background: var(--ocupado); }
    .leyenda .l-elegido::before { background: var(--elegido); }

    .acciones {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 12px;
    }

    /* confirmacion */
    .resumen dl {
        display: grid;
        grid-template-columns: 110px 1fr;
        gap: 10px 14px;
        margin: 0;
    }

    .resumen dt { color: var(--muted); font-weight: 400; }
    .resumen dd { margin: 0; font-weight: 700; }

    .pregunta {
        margin: 22px 0 14px;
        font-size: 17px;
        font-weight: 700;
    }

    .aviso {
        margin-top: 20px;
        padding: 14px 18px;
        border: 1px solid var(--line);
        border-radius: 14px;
        color: black;
        font-size: 13px;
        line-height: 1.6;
    }

    .aviso i { color: var(--primary); margin-right: 6px; }

    @media (max-width: 576px) {
        .opciones { grid-template-columns: 1fr; }
        .lugares { gap: 7px; }
    }
</style>
@endpush

@section('content')

    <a href="{{ $urlHome }}" class="link-home">
        <i class="fa-solid fa-arrow-left"></i> Regresar al inicio
    </a>

    @if (!$planActivo)

        <div class="card-dz" style="margin-bottom: 20px;">
            <p>Necesitas un plan activo para reservar una clase.</p>
            <a href="{{ $ruta('cliente.planes') }}" class="btn-dz">
                <i class="fa-solid fa-bolt"></i> Activar plan
            </a>
        </div>

    @elseif ($creditos < 1)

        <div class="card-dz" style="margin-bottom: 20px;">
            <p><b>No tienes clases disponibles.</b> Cada reserva consume 1 clase de tu plan.
               Renueva o cambia tu plan para seguir reservando.</p>
            <a href="{{ $ruta('cliente.planes') }}" class="btn-dz">
                <i class="fa-solid fa-arrows-rotate"></i> Ver planes
            </a>
        </div>

    @endif

    @if ($puedeReservar)
        <div class="creditos-tag">
            <!--i class="fa-solid fa-coins"></i-->
            Clases disponibles: <b id="creditosDisponibles">{{ $creditos }}</b>
            <small>· cada reserva consume 1 clase</small>
        </div>
    @endif

        {{-- PASO 1: SEDE --}}
        <section class="paso activo" data-paso="1">
            <div class="paso-num">Paso 1 de 3</div>
            <h1>Elige tu sede</h1>

            <div class="opciones">
                @foreach ($sedes as $sede)
                    <button type="button" class="opcion js-sede"
                            data-id="{{ data_get($sede, 'id') }}"
                            data-nombre="{{ data_get($sede, 'nombre') }}">
                        <strong>{{ data_get($sede, 'nombre') }}</strong>
                        <small>Ver horarios disponibles</small>
                    </button>
                @endforeach
            </div>
        </section>

        {{-- PASO 2: DIA Y HORARIO --}}
        <section class="paso" data-paso="2">
            <div class="paso-num">Paso 2 de 3</div>
            <h1>Elige tu horario</h1>

            <div class="modos">
                <button type="button" class="modo js-modo activo" data-modo="dia">Un día</button>
                <button type="button" class="modo js-modo" data-modo="rango">Varios días</button>
            </div>

            <div class="rango" id="rango">
                <div>
                    <label for="fechainicio">Desde</label>
                    <input type="date" id="fechainicio" min="{{ $rangoMin }}" max="{{ $rangoMax }}" value="{{ $rangoMin }}">
                </div>
                <div>
                    <label for="fechafin">Hasta</label>
                    <input type="date" id="fechafin" min="{{ $rangoMin }}" max="{{ $rangoMax }}">
                </div>
                <button type="button" class="btn-dz" id="btnVerRango">
                    <i class="fa-solid fa-magnifying-glass"></i> Ver horarios
                </button>
                <small style="flex-basis: 100%; color: var(--muted);">
                    Puedes reservar desde hoy hasta el {{ \Carbon\Carbon::parse($rangoMax)->format('d/m/Y') }} ({{ $maxDiasRango }} días).
                </small>
            </div>

            <div class="dias" id="dias">
                @foreach ($proximosDias as $dia)
                    <button type="button" class="dia js-dia"
                            data-fecha="{{ $dia['fecha'] }}"
                            data-texto="{{ $dia['texto'] }}">
                        <span>{{ $dia['dia'] }}</span>
                        <strong>{{ $dia['numero'] }}</strong>
                        <span>{{ $dia['mes'] }}</span>
                    </button>
                @endforeach
            </div>

            <div class="ayuda-horario" id="ayudaHorario">
                <i class="fa-regular fa-hand-pointer"></i>
                <span id="ayudaTexto">Elige el día y luego presiona <b>"Elegir horario"</b> en la clase que prefieras.</span>
            </div>

            <div class="opciones" id="horarios"></div>
        </section>

        {{-- PASO 3: LUGAR --}}
        <section class="paso" data-paso="3">
            <div class="paso-num">Paso 3 de 3</div>
            <h1>Reserva tu lugar</h1>
            <div class="capacidad">Capacidad máxima: <strong id="capacidad">-</strong></div>

            <div class="card-dz plano">
                <div class="instructor">
                    <i class="fa-solid fa-person-chalkboard"></i>
                    INSTRUCTOR<span id="instructor"></span>
                </div>
                <div class="lugares" id="lugares"></div>
                <div class="lugar-ocupado-msg" id="lugarOcupadoMsg"></div>
            </div>

            <div class="leyenda">
                <span class="l-libre">Libre</span>
                <span class="l-ocupado">Ocupado</span>
                <span class="l-elegido">Tu lugar</span>
            </div>

            <div class="acciones">
                <button type="button" class="btn-dz" id="btnContinuar" disabled>
                    Continuar con la reserva <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>
        </section>

        {{-- CONFIRMACION --}}
        <section class="paso" data-paso="4">
            <h1>Confirma tu reserva</h1>

            <div class="card-dz resumen">
                <dl>
                    <dt>Sede</dt>      <dd id="rSede"></dd>
                    <dt id="rFechaEtiqueta">Fecha</dt> <dd id="rFecha"></dd>
                    <dt>Horario</dt>   <dd id="rHorario"></dd>
                    <dt>Instructor</dt> <dd id="rInstructor"></dd>
                    <dt>Ubicación</dt> <dd id="rLugar"></dd>
                    <dt>Plan</dt>      <dd>{{ data_get($planActivo, 'nombre') }}</dd>
                    <dt>Clases</dt>  <dd id="rCreditos"></dd>
                </dl>
            </div>

            <div class="pregunta">¿Deseas confirmar la reserva?</div>

            <div class="acciones" style="justify-content: flex-start;">
                <button type="button" class="btn-dz" id="btnConfirmar">
                    <i class="fa-solid fa-check"></i> Continuar
                </button>
                <a href="{{ $urlHome }}" class="btn-dz btn-dz-outline">Cancelar</a>
            </div>

            <div class="aviso">
                <i class="fa-solid fa-circle-info"></i>
                Puedes cancelar tu reserva hasta 40 min antes del inicio de la clase.
                Si reservas y no asistes sin cancelar dentro del plazo establecido,
                podrá aplicarse una penalidad por inasistencia según las condiciones
                del plan adquirido.
                <span class="aviso-rango" id="avisoRango">
                    <br>Al reservar varios días se cancela el <b>rango completo</b>
                    (solo los días que aún estén a más de 40 min de empezar).
                </span>
            </div>
        </section>

@endsection

@push('scripts')
<script>
$(function () {

    var urls = {!! json_encode([
        'horarios' => $ruta('cliente.horarios'),
        'lugares'  => $ruta('cliente.lugares'),
        'guardar'  => $ruta('cliente.reservar.store'),
        'guardarRango' => $ruta('cliente.reservar.rango'),
        'home'     => $urlHome,
        'planes'   => $ruta('cliente.planes'),
    ]) !!};

    // Sin plan activo o sin créditos: solo se ven sedes y horarios; el botón del horario lleva a Planes
    var puedeReservar = {{ $puedeReservar ? 'true' : 'false' }};
    var textoSinPlan = {!! json_encode($planActivo ? 'Renueva tu plan para reservar' : 'Activa tu plan para reservar') !!};

    var reserva = {
        modo: 'dia',
        sede_id: null, sede: '',
        fecha: null, fechaTexto: '',
        fechainicio: null, fechafin: null, rangoTexto: '',
        horario_id: null, hora: '',
        lugar: null,
        dias: 1
    };

    // Créditos disponibles (cada día reservado consume 1)
    var creditos = {{ (int) $creditos }};

    var DIAS  = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
    var MESES = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Set', 'Oct', 'Nov', 'Dic'];

    // "2026-10-06" -> Date local (sin desfase de zona horaria)
    function aFecha(ymd) {
        var p = ymd.split('-');
        return new Date(parseInt(p[0], 10), parseInt(p[1], 10) - 1, parseInt(p[2], 10));
    }

    function textoDia(ymd) {
        var f = aFecha(ymd);
        return DIAS[f.getDay()] + ' ' + f.getDate() + ' de ' + MESES[f.getMonth()] + '.';
    }

    // Parámetros de fecha según el modo (un día o rango)
    function paramsFecha() {
        return reserva.modo === 'rango'
            ? { fechainicio: reserva.fechainicio, fechafin: reserva.fechafin }
            : { fecha: reserva.fecha };
    }

    function irAPaso(n) {
        $('.paso').removeClass('activo');
        $('.paso[data-paso="' + n + '"]').addClass('activo');
        window.scrollTo(0, 0);
    }

    // Acepta "08:00:00" o un rango "08:00:00-10:00:00"
    function formatoHora(valor) {
        return String(valor).split('-').map(function (hhmm) {
            var p = hhmm.trim().split(':');
            var h = parseInt(p[0], 10);
            var m = p[1] || '00';
            var sufijo = h < 12 ? 'am' : 'pm';
            h = h % 12 || 12;
            return h + ':' + m.substring(0, 2) + ' ' + sufijo + '.';
        }).join(' - ');
    }

    function errorAjax() {
        toastr.error('No se pudo cargar la información. Inténtalo nuevamente.');
    }

    /* ---------- PASO 1: SEDE ---------- */
    $('.js-sede').on('click', function () {
        reserva.sede_id = $(this).data('id');
        reserva.sede = $(this).data('nombre');
        irAPaso(2);
        if (reserva.modo === 'rango') {
            $('#horarios').empty();
        } else {
            $('.js-dia').first().trigger('click');
        }
    });

    /* ---------- PASO 2: MODO (UN DIA / VARIOS DIAS) ---------- */
    $('.js-modo').on('click', function () {
        reserva.modo = $(this).data('modo');
        $('.js-modo').removeClass('activo');
        $(this).addClass('activo');

        var rango = reserva.modo === 'rango';
        $('#dias').toggle(!rango);
        $('#rango').toggleClass('visible', rango);
        $('#avisoRango').toggle(rango);
        $('#ayudaTexto').html(rango
            ? 'Elige desde qué día y hasta qué día, presiona <b>"Ver horarios"</b> y luego <b>"Elegir horario"</b>. Se reservará el mismo horario y lugar en los días del rango en que se dicta esa clase.'
            : 'Elige el día y luego presiona <b>"Elegir horario"</b> en la clase que prefieras.');

        if (rango) {
            $('#horarios').empty();
        } else {
            $('.js-dia.activo').length ? $('.js-dia.activo').trigger('click') : $('.js-dia').first().trigger('click');
        }
    });

    // La fecha "Hasta" no puede ser anterior a "Desde"
    $('#fechainicio').on('change', function () {
        $('#fechafin').attr('min', $(this).val());
        if ($('#fechafin').val() && $('#fechafin').val() < $(this).val()) {
            $('#fechafin').val($(this).val());
        }
    });

    $('#btnVerRango').on('click', function () {
        var desde = $('#fechainicio').val();
        var hasta = $('#fechafin').val();
        var min = $('#fechainicio').attr('min');
        var max = $('#fechainicio').attr('max');

        if (!desde || !hasta) {
            toastr.warning('Elige la fecha de inicio y la fecha de fin.');
            return;
        }
        if (hasta < desde) {
            toastr.warning('La fecha de fin no puede ser anterior a la de inicio.');
            return;
        }
        if (desde < min || hasta > max) {
            toastr.warning('Solo puedes reservar desde hoy y hasta {{ $maxDiasRango }} días.');
            return;
        }

        // Los créditos se validan al elegir el horario: solo cuentan los días en que se dicta
        reserva.fechainicio = desde;
        reserva.fechafin = hasta;
        cargarHorarios();
    });

    /* ---------- PASO 2: DIA + HORARIO ---------- */
    $('.js-dia').on('click', function () {
        $('.js-dia').removeClass('activo');
        $(this).addClass('activo');

        reserva.fecha = $(this).data('fecha');
        reserva.fechaTexto = $(this).data('texto');
        cargarHorarios();
    });

    function cargarHorarios() {
        var rango = reserva.modo === 'rango';
        var $cont = $('#horarios').html('<div class="mensaje-vacio">Cargando horarios...</div>');

        $.get(urls.horarios, $.extend({ sede_id: reserva.sede_id }, paramsFecha()))
            .done(function (horarios) {
                $cont.empty();

                if (!horarios || !horarios.length) {
                    $cont.html('<div class="mensaje-vacio">' + (rango ? 'No hay clases para este rango.' : 'No hay clases para este día.') + '</div>');
                    return;
                }

                $.each(horarios, function (i, h) {
                    var libres = h.capacidad - h.ocupados;
                    var $btn = $('<button type="button" class="opcion"></button>')
                        .append($('<strong></strong>').text(formatoHora(h.hora)))
                        .append($('<span class="estado"></span>')
                            .text(libres > 0 ? 'DISPONIBLE' : 'LLENO')
                            .toggleClass('lleno', libres <= 0))
                        .append($('<small></small>').text(libres + '/' + h.capacidad + ' lugares' + (rango ? ' (día más lleno)' : '') + ' · ' + (h.instructor || 'Instructor no asignado')))
                        // En un rango: qué días se dicta y cuántas clases se reservarían
                        .append(rango ? $('<small></small>').text(h.diasTexto + ' · ' + h.diasEnRango + (h.diasEnRango === 1 ? ' clase' : ' clases') + ' en el rango') : '')
                        // Rango de fechas en que el horario está vigente
                        //.append(h.vigenciaTexto ? $('<small></small>').text(h.vigenciaTexto) : '')
                        .append(h.clase_nombre ? $('<small></small>').text(h.clase_nombre) : '')
                        .append($('<span class="btn-elegir"></span>').html(libres <= 0
                            ? 'Sin lugares disponibles'
                            : (puedeReservar
                                ? 'Elegir horario <i class="fa-solid fa-arrow-right"></i>'
                                : $('<div>').text(textoSinPlan).html() + ' <i class="fa-solid fa-bolt"></i>')))
                        .prop('disabled', libres <= 0)
                        .on('click', function () {
                            // No puede pasar al paso 3 (lugar) sin plan activo o sin créditos
                            if (!puedeReservar) {
                                window.location.href = urls.planes;
                                return;
                            }
                            if (rango) {
                                // Cada día en que se dicta el horario consume 1 crédito
                                if (h.diasEnRango > creditos) {
                                    toastr.warning('Este horario tiene ' + h.diasEnRango + ' clases en el rango y tienes ' + creditos + ' créditos. Elige menos días.');
                                    return;
                                }
                                reserva.dias = h.diasEnRango;
                                reserva.rangoTexto = h.fechasRango.join(', ') + ' (' + h.diasEnRango + (h.diasEnRango === 1 ? ' clase)' : ' clases)');
                            }
                            reserva.horario_id = h.id;
                            reserva.hora = formatoHora(h.hora);
                            cargarLugares();
                        });
                    $cont.append($btn);
                });
            })
            .fail(errorAjax);
    }

    /* ---------- PASO 3: LUGAR ---------- */
    function cargarLugares() {
        reserva.lugar = null;
        $('#btnContinuar').prop('disabled', true);

        // En un rango, un lugar aparece ocupado si lo está en cualquier día del rango
        $.get(urls.lugares, $.extend({ horario_id: reserva.horario_id }, paramsFecha()))
            .done(function (data) {
                var ocupados = (data.ocupados || []).map(Number);
                var ocupadosDias = data.ocupadosDias || {};
                var rango = reserva.modo === 'rango';
                var $cont = $('#lugares').empty();
                $('#lugarOcupadoMsg').hide();

                $('#capacidad').text(data.capacidad);
                $('#instructor').text(data.instructor ? ': ' + data.instructor : '');
                // Sobrenombre del instructor para la confirmación
                reserva.instructor = data.instructor || '';

                for (var n = 1; n <= data.capacidad; n++) {
                    var ocupado = ocupados.indexOf(n) !== -1;
                    var dias = ocupadosDias[n] || [];
                    var $lugar = $('<button type="button" class="lugar"></button>')
                        .text(n)
                        .attr('data-lugar', n)
                        .toggleClass('ocupado', ocupado)
                        // En un rango el lugar ocupado se puede tocar para ver qué días está ocupado
                        .prop('disabled', ocupado && !rango)
                        .appendTo($cont);
                    if (ocupado && rango && dias.length) {
                        $lugar.attr('data-dias', dias.join(', ')).attr('title', 'Ocupado: ' + dias.join(', '));
                    }
                }

                irAPaso(3);
            })
            .fail(errorAjax);
    }

    // Rango: al tocar un lugar ocupado se indica en qué días está ocupado
    $('#lugares').on('click', '.lugar.ocupado', function () {
        var dias = $(this).data('dias');
        if (!dias) return;
        var mensaje = 'El lugar N.° ' + $(this).data('lugar') + ' está ocupado el ' + dias
            + '. Elige otro lugar libre para todo el rango o cambia las fechas.';
        $('#lugarOcupadoMsg').html('<i class="fa-solid fa-circle-exclamation"></i> ' + $('<div>').text(mensaje).html()).show();
        toastr.warning(mensaje);
    });

    $('#lugares').on('click', '.lugar:not(.ocupado)', function () {
        $('#lugarOcupadoMsg').hide();
        $('#lugares .lugar').removeClass('elegido');
        $(this).addClass('elegido');
        reserva.lugar = $(this).data('lugar');
        $('#btnContinuar').prop('disabled', false);
    });

    $('#btnContinuar').on('click', function () {
        if (!reserva.lugar) return;

        $('#rSede').text(reserva.sede);
        $('#rFechaEtiqueta').text(reserva.modo === 'rango' ? 'Fechas' : 'Fecha');
        $('#rFecha').text(reserva.modo === 'rango' ? reserva.rangoTexto : reserva.fechaTexto);
        $('#rHorario').text(reserva.hora);
        $('#rInstructor').text(reserva.instructor || '-');
        $('#rLugar').text('Lugar N.° ' + reserva.lugar);
        var usados = reserva.modo === 'rango' ? reserva.dias : 1;
        $('#rCreditos').text('Se ' + (usados === 1 ? 'descontará 1 clase' : 'descontarán ' + usados + ' clases')
            + ' · te quedarán ' + (creditos - usados));
        irAPaso(4);
    });

    /* ---------- CONFIRMAR ---------- */
    $('#btnConfirmar').on('click', function () {
        var $btn = $(this).prop('disabled', true);

        var url = reserva.modo === 'rango' ? urls.guardarRango : urls.guardar;
        $.post(url, $.extend({
            horario_id: reserva.horario_id,
            ubicacion: reserva.lugar
        }, paramsFecha()))
            .done(function (r) {
                if (r && r.ok) {
                    toastr.success(r.mensaje || 'Reserva registrada');
                    setTimeout(function () { window.location.href = r.redirect || urls.home; }, 800);
                } else {
                    toastr.error((r && r.mensaje) || 'No se pudo registrar la reserva.');
                    $btn.prop('disabled', false);
                    // Sin créditos suficientes: se queda en la confirmación (no tiene sentido elegir otro lugar)
                    if (!(r && r.sinCreditos)) {
                        cargarLugares();
                    }
                }
            })
            .fail(function (xhr) {
                var r = xhr.responseJSON;
                if (r && r.mensaje) {
                    toastr.error(r.mensaje);
                } else {
                    errorAjax();
                }
                $btn.prop('disabled', false);
            });
    });

});
</script>
@endpush
