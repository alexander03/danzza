@php
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

    $urlElegir = Route::has('login') ? route('login') : '#';

    // "Ver horarios" (solo consulta): paso 1 sede, paso 2 horarios; no permite reservar
    $sedes        = isset($sedes) ? $sedes : collect();
    $maxDiasRango = isset($maxDiasRango) ? (int) $maxDiasRango : 30;
    $urlHorarios  = Route::has('webservices.horarios') ? route('webservices.horarios') : '';
    $diasSemana   = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
    $mesesCortos  = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Set', 'Oct', 'Nov', 'Dic'];
    $proximosDias = [];
    for ($i = 0; $i < 7; $i++) {
        $f = \Carbon\Carbon::today()->addDays($i);
        $proximosDias[] = [
            'fecha'  => $f->format('Y-m-d'),
            'dia'    => $i === 0 ? 'Hoy' : $diasSemana[$f->dayOfWeek],
            'numero' => $f->day,
            'mes'    => $mesesCortos[$f->month - 1],
        ];
    }
    $rangoMin = \Carbon\Carbon::today()->format('Y-m-d');
    $rangoMax = \Carbon\Carbon::today()->addDays($maxDiasRango - 1)->format('Y-m-d');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planes | DANZZA</title>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="icon" type="image/svg+xml"
          href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/svgs/solid/person-dress.svg">
    <style>

        /* Estilos limitados a .planes-wrap porque esta vista se carga dentro de un modal */
        .planes-wrap,
        .planes-wrap * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .planes-wrap {
            --primary: #dfff00;
            --primary-dark: #a9c900;
            --light: #f1f0ed;
            --charcoal: #6e7378;
            --line: #d3d2cd;
            --text: #ffffff;
            --muted: #a7a7a7;
            --muted-dark: #6b6b68;

            display: flex;
            flex-direction: column;
            background: var(--light);
            color: var(--charcoal);
            font-family: "Segoe UI", Arial, sans-serif;
            overflow-x: hidden;
        }


        /* =========================================================
           ENCABEZADO
        ========================================================= */

        .planes-header {
            text-align: center;
            padding: 30px 20px 30px;
        }

        .planes-header h1 {
            font-size: 36px;
            font-weight: 800;
            letter-spacing: 2px;
            color: var(--charcoal);
        }

        .planes-header h1 span {
            padding: 0 10px;
            border-radius: 8px;
            background: var(--charcoal);
            color: var(--primary);
        }

        .planes-header .subtitulo {
            margin-top: 12px;
            font-size: 18px;
            font-weight: 700;
            color: #6f8a00;
            text-transform: uppercase;
            letter-spacing: 3px;
        }

        .planes-header p {
            margin-top: 10px;
            font-size: 16px;
            color: var(--muted-dark);
        }


        /* =========================================================
           LISTA DE PLANES
        ========================================================= */

        .planes-main {
            flex: 1;
            width: 100%;
            max-width: 1150px;
            margin: 0 auto;
            padding: 0 20px 40px;
        }

        /* Tarjetas con el mismo estilo que app/cliente/planes.blade.php
           (card-dz / btn-dz del layout master-cliente, copiados aquí porque
           esta vista se carga en un modal sin ese layout) */
        .planes-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .planes-wrap .card-dz {
            padding: 22px;
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 18px;
            background: var(--charcoal);
            color: var(--text);
            box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
        }

        .plan-card {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .plan-card.destacado {
            border-color: var(--primary);
        }

        .plan-card .nombre {
            color: var(--primary);
            font-size: 22px;
            font-weight: 800;
            overflow-wrap: anywhere;
        }

        .plan-card .precio {
            font-size: 26px;
            font-weight: 800;
        }

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

        .plan-card li {
            list-style: disc;
            margin: 2px 0;
            font-size: 14px;
        }

        /* El nombre del plan ya es verde lima: el "recomendado" va en blanco y más pequeño */
        .plan-card .plan-recomendado {
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
        }

        .plan-card .badge-destacado {
            align-self: flex-start;
            padding: 2px 10px;
            border-radius: 20px;
            background: var(--primary);
            color: #070707;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1px;
        }

        .plan-card .acciones-plan {
            margin-top: auto;
        }

        .planes-wrap .btn-dz {
            width: 100%;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(90deg, #dfff00, #a9d000);
            color: #070707;
            font-size: 14px;
            font-weight: 800;
            text-decoration: none !important;
            cursor: pointer;
            transition: .25s;
        }

        .planes-wrap .btn-dz:hover {
            box-shadow: 0 8px 30px rgba(223, 255, 0, .30);
        }

        /* Mismo estilo que el botón "Cambiar plan" (btn-dz btn-dz-outline) de planes.blade.php */
        .planes-wrap .btn-dz.btn-dz-outline {
            border: 1px solid var(--primary);
            background: transparent;
            color: var(--primary);
        }

        .planes-wrap .btn-dz.btn-dz-outline:hover {
            background: linear-gradient(90deg, #dfff00, #a9d000);
            color: #070707;
        }


        /* =========================================================
           FOOTER - TERMINOS Y CONDICIONES
        ========================================================= */

        .planes-footer {
            border-top: 1px solid var(--line);
            background: #e6e5e1;
            padding: 30px 20px;
        }

        .terminos {
            max-width: 1150px;
            margin: 0 auto;
        }

        .terminos h2 {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
            font-size: 17px;
            font-weight: 800;
            letter-spacing: 2px;
            color: var(--charcoal);
        }

        .terminos h2 i {
            color: var(--primary-dark);
        }

        .terminos ul {
            list-style: none;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px 40px;
        }

        .terminos li {
            position: relative;
            padding-left: 18px;
            font-size: 13px;
            line-height: 1.6;
            color: #4a4a47;
        }

        .terminos li::before {
            content: "•";
            position: absolute;
            left: 0;
            color: var(--primary-dark);
        }


        /* =========================================================
           ALERTA DE REGISTRO
        ========================================================= */

        .alerta-overlay {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(28, 29, 29, .70);
            backdrop-filter: blur(6px);
            opacity: 0;
            visibility: hidden;
            transition: .25s;
        }

        .alerta-overlay.visible {
            opacity: 1;
            visibility: visible;
        }

        .alerta-box {
            width: 100%;
            max-width: 360px;
            padding: 32px 24px 24px;
            border-top: 4px solid var(--primary);
            border-radius: 20px;
            background: #f1f0ed;
            color: #2b2c2c;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .45);
            text-align: center;
            transform: scale(.85);
            transition: .25s;
        }

        .alerta-overlay.visible .alerta-box {
            transform: scale(1);
        }

        .alerta-icono {
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #2b2c2c;
            color: var(--primary);
            font-size: 28px;
        }

        .alerta-titulo {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 1px;
        }

        .alerta-texto {
            margin-top: 8px;
            font-size: 14px;
            color: #6b6b68;
        }

        .alerta-acciones {
            margin-top: 22px;
            display: flex;
            gap: 10px;
        }

        .alerta-acciones button {
            flex: 1;
            height: 44px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 1px;
            cursor: pointer;
            transition: .25s;
        }

        .alerta-cancelar {
            border: none;
            background: #2b2c2c;
            color: #ffffff;
        }

        .alerta-cancelar:hover {
            background: #3a3b3b;
            color: var(--primary);
        }

        .alerta-aceptar {
            border: none;
            background: linear-gradient(90deg, #dfff00, #a9d000);
            color: #070707;
        }

        .alerta-aceptar:hover {
            box-shadow: 0 6px 20px rgba(169, 208, 0, .45);
        }


        /* =========================================================
           VER HORARIOS (solo consulta: paso 1 sede, paso 2 horarios)
        ========================================================= */

        .btn-ver-horarios {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 18px;
            padding: 10px 22px;
            border: none;
            border-radius: 12px;
            background: var(--charcoal);
            color: var(--primary);
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
            transition: .25s;
        }

        .btn-ver-horarios:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, .18);
        }

        .hz-panel {
            display: none;
            width: 100%;
            max-width: 1150px;
            margin: 0 auto;
            padding: 0 20px 40px;
        }

        .planes-wrap.ver-horarios .hz-panel { display: block; }
        .planes-wrap.ver-horarios .planes-main,
        .planes-wrap.ver-horarios .planes-footer,
        .planes-wrap.ver-horarios .btn-ver-horarios { display: none; }

        .hz-volver {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
            border: none;
            background: transparent;
            color: var(--charcoal);
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
        }

        .hz-paso { display: none; }
        .hz-paso.activo { display: block; }

        .hz-paso-num {
            font-size: 13px;
            letter-spacing: 1px;
            color: var(--muted-dark);
        }

        .hz-paso h2 {
            margin: 4px 0 16px;
            font-size: 24px;
            font-weight: 800;
            color: var(--charcoal);
        }

        .hz-aviso {
            margin-bottom: 16px;
            padding: 10px 14px;
            border-radius: 12px;
            background: #e6e5e1;
            color: #4a4a47;
            font-size: 14px;
        }

        .hz-aviso i { color: var(--primary-dark); margin-right: 6px; }

        .hz-opciones {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
        }

        .hz-opcion {
            width: 100%;
            padding: 18px 20px;
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 16px;
            background: var(--charcoal);
            color: var(--text);
            text-align: left;
        }

        button.hz-opcion { cursor: pointer; transition: .25s; }
        button.hz-opcion:hover { border-color: var(--primary); }

        .hz-opcion strong { display: block; font-size: 19px; }
        .hz-opcion small { display: block; margin-top: 4px; color: #ddd; }

        .hz-opcion .hz-estado {
            display: inline-block;
            margin-top: 8px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #22c55e;
        }

        .hz-opcion .hz-estado.lleno { color: #ef4444; }

        .hz-modos {
            display: inline-flex;
            margin-bottom: 16px;
            padding: 4px;
            border-radius: 14px;
            background: var(--charcoal);
        }

        .hz-modo {
            padding: 8px 18px;
            border: none;
            border-radius: 10px;
            background: transparent;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
        }

        .hz-modo.activo { background: var(--primary); color: #070707; }

        .hz-dias {
            display: flex;
            gap: 10px;
            margin-bottom: 18px;
            overflow-x: auto;
            padding-bottom: 4px;
        }

        .hz-dia {
            flex: 0 0 72px;
            padding: 10px 0;
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 14px;
            background: var(--charcoal);
            color: #fff;
            text-align: center;
            cursor: pointer;
        }

        .hz-dia span { display: block; font-size: 12px; color: #ddd; }
        .hz-dia strong { display: block; font-size: 22px; }
        .hz-dia.activo { border-color: var(--primary); background: var(--primary); color: #070707; }
        .hz-dia.activo span { color: #070707; }

        .hz-rango {
            display: none;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 12px;
            margin-bottom: 18px;
        }

        .hz-rango.visible { display: flex; }

        .hz-rango label {
            display: block;
            margin-bottom: 6px;
            color: var(--muted-dark);
            font-size: 13px;
            font-weight: 700;
        }

        .hz-rango input {
            height: 44px;
            padding: 0 12px;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #fff;
            color: var(--charcoal);
        }

        .hz-rango .btn-dz { width: auto; padding: 0 18px; }

        .hz-vacio { padding: 10px 0; color: var(--muted-dark); }

        @media (max-width: 576px) {
            .hz-opciones { grid-template-columns: 1fr; }
            .hz-panel { padding: 0 16px 40px; }
        }


        /* =========================================================
           RESPONSIVE
        ========================================================= */

        @media (max-width: 992px) {
            .planes-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .planes-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .planes-header {
                padding: 40px 16px 30px;
            }

            .planes-header h1 {
                font-size: 30px;
            }

            .planes-header .subtitulo {
                font-size: 15px;
                letter-spacing: 2px;
            }

            .planes-main {
                padding: 0 16px 40px;
            }

            .planes-footer {
                padding: 30px 16px;
            }

            .terminos ul {
                grid-template-columns: 1fr;
            }
        }

    </style>
</head>

<body>
<div class="planes-wrap">

<header class="planes-header">
    <h1>✨ PLANES <span>DANZZA</span></h1>
    <p>Empieza a bailar, elige tu ritmo y disfruta Danzza.</p>
    <button type="button" class="btn-ver-horarios" id="btnVerHorarios">
        <i class="fa-regular fa-calendar"></i> Ver horarios
    </button>
</header>

{{-- VER HORARIOS: solo consulta (paso 1 sede, paso 2 horarios); no se puede reservar desde aquí --}}
<section class="hz-panel" id="hzPanel">
    <button type="button" class="hz-volver" id="hzVolverPlanes">
        <i class="fa-solid fa-arrow-left"></i> Volver a planes
    </button>

    <div class="hz-aviso">
        <i class="fa-solid fa-circle-info"></i>
        Aquí solo puedes ver los horarios. Para reservar, elige un plan e inicia sesión.
    </div>

    {{-- PASO 1: SEDE --}}
    <div class="hz-paso activo" data-paso="1">
        <div class="hz-paso-num">Paso 1 de 2</div>
        <h2>Elige la sede</h2>
        <div class="hz-opciones">
            @forelse ($sedes as $sede)
                <button type="button" class="hz-opcion js-hz-sede"
                        data-id="{{ $sede->id }}" data-nombre="{{ $sede->nombre }}">
                    <strong>{{ $sede->nombre }}</strong>
                    <small>Ver horarios disponibles</small>
                </button>
            @empty
                <div class="hz-vacio">No hay sedes registradas.</div>
            @endforelse
        </div>
    </div>

    {{-- PASO 2: DIA Y HORARIOS (solo información) --}}
    <div class="hz-paso" data-paso="2">
        <button type="button" class="hz-volver" id="hzVolverSede">
            <i class="fa-solid fa-arrow-left"></i> Cambiar sede
        </button>
        <div class="hz-paso-num">Paso 2 de 2</div>
        <h2>Horarios · <span id="hzSedeNombre"></span></h2>

        <div class="hz-modos">
            <button type="button" class="hz-modo js-hz-modo activo" data-modo="dia">Un día</button>
            <button type="button" class="hz-modo js-hz-modo" data-modo="rango">Varios días</button>
        </div>

        <div class="hz-rango" id="hzRango">
            <div>
                <label for="hzFechainicio">Desde</label>
                <input type="date" id="hzFechainicio" min="{{ $rangoMin }}" max="{{ $rangoMax }}" value="{{ $rangoMin }}">
            </div>
            <div>
                <label for="hzFechafin">Hasta</label>
                <input type="date" id="hzFechafin" min="{{ $rangoMin }}" max="{{ $rangoMax }}">
            </div>
            <button type="button" class="btn-dz" id="hzBtnRango">
                <i class="fa-solid fa-magnifying-glass"></i> Ver horarios
            </button>
        </div>

        <div class="hz-dias" id="hzDias">
            @foreach ($proximosDias as $dia)
                <button type="button" class="hz-dia js-hz-dia" data-fecha="{{ $dia['fecha'] }}">
                    <span>{{ $dia['dia'] }}</span>
                    <strong>{{ $dia['numero'] }}</strong>
                    <span>{{ $dia['mes'] }}</span>
                </button>
            @endforeach
        </div>

        <div class="hz-opciones" id="hzHorarios"></div>
    </div>
</section>

<main class="planes-main">
    <div class="planes-grid">
        @foreach ($planes as $plan)
            <div class="card-dz plan-card {{ !empty($plan['destacado']) ? 'destacado' : '' }}">
                @if (!empty($plan['destacado']))
                    <span class="badge-destacado"><i class="fa-solid fa-star"></i> MÁS ELEGIDO</span>
                @endif
                <div class="nombre">
                    {{ $plan['nombre'] }}
                    @if (!empty($plan['recomendado']))
                        {{-- "Recomendado" en la misma línea del nombre del plan, en otro color --}}
                        <span class="plan-recomendado">· {{ $plan['recomendado'] }}</span>
                    @endif
                </div>
                @if (($plan['preciopromocion'] ?? 0) > 0)
                    <div class="precio-anterior">S/ {{ number_format($plan['precio'], 2) }}</div>
                    <div class="precio">S/ {{ number_format($plan['preciopromocion'], 2) }}</div>
                @else
                    <div class="precio">S/ {{ number_format($plan['precio'], 2) }}</div>
                @endif
                <ul>
                    @if (!empty($plan['descripcion']))
                        <li>{{ $plan['descripcion'] }}</li>
                    @endif
                    @if (!is_null($plan['creditos']))
                        <li>{{ $plan['creditos'] }} clases</li>
                    @endif
                    @if (!empty($plan['vigencia']))
                        <li>Vigencia: {{ $plan['vigencia'] }} días</li>
                    @endif
                    @if (!empty($plan['nota']))
                        <li>{{ $plan['nota'] }}</li>
                    @endif
                </ul>
                <div class="acciones-plan">
                    <a href="{{ $urlElegir }}" class="btn-dz btn-dz-outline"
                       onclick="event.preventDefault(); mostrarAlertaRegistro(this.href);">
                        <i class="fa-solid fa-bolt"></i> Elegir plan
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</main>

<footer class="planes-footer">
    <div class="terminos">
        <h2><i class="fa-solid fa-file-contract"></i> TÉRMINOS Y CONDICIONES</h2>
        <ul>
            @foreach ($terminos as $termino)
                <li>{{ $termino }}</li>
            @endforeach
        </ul>
    </div>
</footer>

<div class="alerta-overlay" id="alertaRegistro" onclick="if (event.target === this) cerrarAlertaRegistro();">
    <div class="alerta-box">
        <div class="alerta-icono"><i class="fa-solid fa-user-plus"></i></div>
        <div class="alerta-titulo">Debe registrarse</div>
        <div class="alerta-texto">Para elegir un plan necesitas iniciar sesión o crear una cuenta.</div>
        <div class="alerta-acciones">
            <button type="button" class="alerta-cancelar" onclick="cerrarAlertaRegistro();">CANCELAR</button>
            <button type="button" class="alerta-aceptar" id="alertaRegistroAceptar">IR AL LOGIN</button>
        </div>
    </div>
</div>

<script type="text/javascript">
    function mostrarAlertaRegistro(url) {
        document.getElementById('alertaRegistroAceptar').onclick = function () {
            window.location.href = url;
        };
        document.getElementById('alertaRegistro').classList.add('visible');
    }

    function cerrarAlertaRegistro() {
        document.getElementById('alertaRegistro').classList.remove('visible');
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') cerrarAlertaRegistro();
    });

    /* ---------- VER HORARIOS (solo consulta, no permite reservar) ---------- */
    $(function () {
        var $wrap = $('.planes-wrap');
        var urlHorarios = {!! json_encode($urlHorarios) !!};
        var hz = { sede_id: null, modo: 'dia', fecha: null, fechainicio: null, fechafin: null };

        function irPaso(n) {
            $wrap.find('.hz-paso').removeClass('activo');
            $wrap.find('.hz-paso[data-paso="' + n + '"]').addClass('activo');
        }

        // "08:00:00 - 09:00:00" -> "8:00 am. - 9:00 am."
        function formatoHora(valor) {
            return String(valor).split('-').map(function (hhmm) {
                var p = hhmm.trim().split(':');
                var h = parseInt(p[0], 10);
                var m = (p[1] || '00').substring(0, 2);
                var sufijo = h < 12 ? 'am' : 'pm';
                h = h % 12 || 12;
                return h + ':' + m + ' ' + sufijo + '.';
            }).join(' - ');
        }

        $('#btnVerHorarios').on('click', function () {
            $wrap.addClass('ver-horarios');
            irPaso(1);
        });

        $('#hzVolverPlanes').on('click', function () {
            $wrap.removeClass('ver-horarios');
        });

        $('#hzVolverSede').on('click', function () { irPaso(1); });

        /* Paso 1: sede */
        $wrap.find('.js-hz-sede').on('click', function () {
            hz.sede_id = $(this).data('id');
            $('#hzSedeNombre').text($(this).data('nombre'));
            irPaso(2);
            if (hz.modo === 'rango') {
                $('#hzHorarios').empty();
            } else {
                ($wrap.find('.js-hz-dia.activo').length ? $wrap.find('.js-hz-dia.activo') : $wrap.find('.js-hz-dia').first()).trigger('click');
            }
        });

        /* Paso 2: un día / varios días */
        $wrap.find('.js-hz-modo').on('click', function () {
            hz.modo = $(this).data('modo');
            $wrap.find('.js-hz-modo').removeClass('activo');
            $(this).addClass('activo');
            var rango = hz.modo === 'rango';
            $('#hzDias').toggle(!rango);
            $('#hzRango').toggleClass('visible', rango);
            if (rango) {
                $('#hzHorarios').empty();
            } else {
                ($wrap.find('.js-hz-dia.activo').length ? $wrap.find('.js-hz-dia.activo') : $wrap.find('.js-hz-dia').first()).trigger('click');
            }
        });

        $wrap.find('.js-hz-dia').on('click', function () {
            $wrap.find('.js-hz-dia').removeClass('activo');
            $(this).addClass('activo');
            hz.fecha = $(this).data('fecha');
            cargarHorarios();
        });

        $('#hzFechainicio').on('change', function () {
            $('#hzFechafin').attr('min', $(this).val());
            if ($('#hzFechafin').val() && $('#hzFechafin').val() < $(this).val()) {
                $('#hzFechafin').val($(this).val());
            }
        });

        $('#hzBtnRango').on('click', function () {
            var desde = $('#hzFechainicio').val();
            var hasta = $('#hzFechafin').val();
            if (!desde || !hasta) {
                alert('Elige la fecha de inicio y la fecha de fin.');
                return;
            }
            if (hasta < desde) {
                alert('La fecha de fin no puede ser anterior a la de inicio.');
                return;
            }
            if (desde < $('#hzFechainicio').attr('min') || hasta > $('#hzFechainicio').attr('max')) {
                alert('Solo puedes consultar desde hoy y hasta {{ $maxDiasRango }} días.');
                return;
            }
            hz.fechainicio = desde;
            hz.fechafin = hasta;
            cargarHorarios();
        });

        // Lista de horarios: solo información (sin botón para elegir ni reservar)
        function cargarHorarios() {
            var rango = hz.modo === 'rango';
            var $cont = $('#hzHorarios').html('<div class="hz-vacio">Cargando horarios...</div>');
            var params = rango
                ? { sede_id: hz.sede_id, fechainicio: hz.fechainicio, fechafin: hz.fechafin }
                : { sede_id: hz.sede_id, fecha: hz.fecha };

            $.get(urlHorarios, params)
                .done(function (horarios) {
                    $cont.empty();
                    if (!horarios || !horarios.length) {
                        $cont.html('<div class="hz-vacio">' + (rango ? 'No hay clases para este rango.' : 'No hay clases para este día.') + '</div>');
                        return;
                    }
                    $.each(horarios, function (i, h) {
                        var libres = h.capacidad - h.ocupados;
                        $('<div class="hz-opcion"></div>')
                            .append($('<strong></strong>').text(formatoHora(h.hora)))
                            .append($('<span class="hz-estado"></span>')
                                .text(libres > 0 ? 'DISPONIBLE' : 'LLENO')
                                .toggleClass('lleno', libres <= 0))
                            .append(h.clase_nombre ? $('<small></small>').text(h.clase_nombre) : '')
                            .append($('<small></small>').text(h.instructor || 'Instructor no asignado'))
                            .append($('<small></small>').text(libres + '/' + h.capacidad + ' lugares' + (rango ? ' (día más lleno)' : '')))
                            .append(rango ? $('<small></small>').text(h.diasTexto + ' · ' + h.diasEnRango + (h.diasEnRango === 1 ? ' clase' : ' clases') + ' en el rango') : '')
                            .appendTo($cont);
                    });
                })
                .fail(function () {
                    $cont.html('<div class="hz-vacio">No se pudieron cargar los horarios. Inténtalo nuevamente.</div>');
                });
        }
    });
</script>

</div>
</body>
<script type="text/javascript">
$(document).ready(function() {
	configurarAnchoModal('900');
});
</script>
</html>
