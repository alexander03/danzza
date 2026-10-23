{{--
|--------------------------------------------------------------------------
| Mis reservas del CLIENTE
|--------------------------------------------------------------------------
| Variables que envía el controlador (ClienteController@reservas):
|   $proximas   array de reservas futuras (la más cercana primero)
|   $historial  array de reservas pasadas (la más reciente primero)
|   $creditos   créditos disponibles del cliente (cada reserva consume 1)
|   Cada item: id, codigo, fecha, ubicacion, situacion,
|              horainicio, horafin, sede, instructor (sobrenombre), cancelable (bool)
|   (Las reservas no generan pago: se paga al activar/cambiar el plan)
|
| Rutas que usa:
|   cliente.reservas.cancelar   -> POST, cancelar hasta 40 min antes
|   cliente.reservar            -> nueva reserva
|   cliente.home                -> volver
--}}
@extends('layouts.master-cliente')

@section('title', 'Mis reservas | DANZZA')

@php
    $ruta = function ($nombre, $params = []) {
        return Route::has($nombre) ? route($nombre, $params) : '#';
    };

    $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Set', 'Oct', 'Nov', 'Dic'];
    $formatoFecha = function ($fecha) use ($meses) {
        $f = \Carbon\Carbon::parse($fecha);
        return $f->day . ' de ' . $meses[$f->month - 1] . '. ' . $f->year;
    };
    $formatoHora = function ($r) {
        return \Carbon\Carbon::parse($r->horainicio)->format('g:i a')
            . ' - ' . \Carbon\Carbon::parse($r->horafin)->format('g:i a');
    };

    // Estado que ve el cliente: [texto, clase css]
    $estado = function ($r) {
        if (strtoupper($r->asistencia) === 'INGRESO VALIDADO') {
            return ['FINALIZADO', 'confirmado'];
        }
        if(strtoupper($r->situacion) === 'FINALIZADO') {
            return ['FINALIZADO', 'confirmado'];
        }
        return ['RESERVADO', 'confirmado'];
    };

    $urlHome     = $ruta('cliente.home');
    $urlReservar = $ruta('cliente.reservar');
@endphp

@push('styles')
<style>
    .reservas-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 18px;
    }

    .reservas-top h1 {
        margin: 0;
        font-size: 26px;
        font-weight: 800;
        color: var(--charcoal);
    }

    .aviso-pendiente {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 18px;
        padding: 14px 18px;
        border-left: 4px solid #f59e0b;
        border-radius: 12px;
        background: #fff7e6;
        color: #7a4b00;
        font-size: 14px;
    }

    .aviso-pendiente i {
        font-size: 20px;
    }

    .creditos-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 6px;
        padding: 4px 12px;
        border-radius: 20px;
        background: var(--charcoal);
        color: #fff;
        font-size: 13px;
    }

    .creditos-tag i,
    .creditos-tag b { color: var(--primary); }

    .creditos-tag.sin b,
    .creditos-tag.sin i { color: #f87171; }

    .seccion-titulo {
        margin: 26px 0 12px;
        font-size: 15px;
        font-weight: 800;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: var(--muted-dark);
    }

    .reserva-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 12px;
    }

    .reserva-card.pasada {
        opacity: .7;
    }

    .reserva-info {
        display: flex;
        align-items: center;
        gap: 16px;
        min-width: 0;
    }

    .reserva-dia {
        flex: 0 0 64px;
        height: 64px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: var(--primary);
        color: #070707;
        line-height: 1;
    }

    .reserva-dia strong {
        font-size: 24px;
        font-weight: 800;
    }

    .reserva-dia small {
        margin-top: 3px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .reserva-card.pasada .reserva-dia {
        background: rgba(255, 255, 255, .12);
        color: #fff;
    }

    .reserva-datos strong {
        display: block;
        font-size: 16px;
    }

    .reserva-datos small {
        display: block;
        margin-top: 3px;
        color: var(--muted);
    }

    .estado {
        display: inline-block;
        margin-top: 8px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 1px;
    }

    .estado.pendiente  { background: rgba(245, 158, 11, .18); color: #fbbf24; }
    .estado.validacion { background: rgba(59, 130, 246, .18); color: #93c5fd; }
    .estado.confirmado { background: rgba(223, 255, 0, .15);  color: var(--primary); }

    .reserva-acciones {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 8px;
        flex-shrink: 0;
    }

    .reserva-acciones .btn-dz {
        height: 40px;
        padding: 0 16px;
        font-size: 13px;
    }

    .btn-cancelar-reserva {
        background: transparent !important;
        border: 1px solid rgba(239, 68, 68, .6) !important;
        color: #fca5a5 !important;
    }

    .btn-cancelar-reserva:hover {
        background: rgba(239, 68, 68, .12) !important;
        box-shadow: none !important;
    }

    .rango-tag {
        display: inline-block;
        margin: 6px 6px 0 0;
        padding: 3px 10px;
        border: 1px solid var(--line);
        border-radius: 20px;
        color: #ddd;
        font-size: 11px;
        font-weight: 700;
    }

    .rango-tag i { color: var(--primary); margin-right: 4px; }

    .vacio {
        text-align: center;
        padding: 30px 20px;
    }

    .vacio i {
        display: block;
        margin-bottom: 10px;
        color: var(--primary);
        font-size: 34px;
    }

    .vacio p {
        margin: 0 0 16px;
        color: #ddd;
    }

    @media (max-width: 768px) {
        .reserva-card {
            flex-direction: column;
            align-items: stretch;
        }

        .reserva-acciones {
            justify-content: stretch;
        }

        .reserva-acciones .btn-dz {
            flex: 1;
        }
    }
</style>
@endpush

@section('content')

    <a href="{{ $urlHome }}" class="link-home">
        <i class="fa-solid fa-arrow-left"></i> Volver al inicio
    </a>

    <div class="reservas-top">
        <div>
            <h1>Mis reservas</h1>
            <span class="creditos-tag {{ $creditos < 1 ? 'sin' : '' }}">
                <i class="fa-solid fa-coins"></i>
                Clases disponibles: <b>{{ $creditos }}</b>
            </span>
        </div>
        <a href="{{ $urlReservar }}" class="btn-dz">
            <i class="fa-regular fa-calendar-plus"></i> Nueva reserva
        </a>
    </div>

    {{-- PRÓXIMAS --}}
    <div class="seccion-titulo">Próximas</div>

    @forelse ($proximas as $r)
        @php
            list($estadoTexto, $estadoClase) = $estado($r);
            $f = \Carbon\Carbon::parse($r->fecha);
        @endphp
        <div class="card-dz reserva-card" id="reserva-{{ $r->id }}">
            <div class="reserva-info">
                <div class="reserva-dia">
                    <strong>{{ $f->day }}</strong>
                    <small>{{ $meses[$f->month - 1] }}</small>
                </div>
                <div class="reserva-datos">
                    <strong>{{ $formatoHora($r) }}</strong>
                    <small>{{ $r->sede }} · Lugar N.° {{ $r->ubicacion }} · {{ $r->codigo }}</small>
                    @if (!empty($r->instructor))
                        <small><i class="fa-solid fa-person-chalkboard"></i> Instructor: {{ $r->instructor }}</small>
                    @endif
                    @if ($r->grupo)
                        <span class="rango-tag"><i class="fa-regular fa-calendar-days"></i> Rango: {{ $r->rangoTexto }}</span>
                    @endif
                    <span class="estado {{ $estadoClase }}">{{ $estadoTexto }}</span>
                </div>
            </div>

            <div class="reserva-acciones">
                @if ($r->cancelable)
                    <button type="button" class="btn-dz btn-cancelar-reserva"
                            data-url="{{ $ruta('cliente.reservas.cancelar', $r->id) }}"
                            data-id="{{ $r->id }}">
                        <i class="fa-solid fa-xmark"></i> Cancelar
                    </button>
                @endif

                @if ($r->mostrarCancelarRango)
                    <button type="button" class="btn-dz btn-cancelar-reserva"
                            data-url="{{ $ruta('cliente.reservas.cancelarrango', $r->grupo->id) }}"
                            data-id="{{ $r->id }}"
                            data-mensaje="¿Seguro que deseas cancelar el rango completo ({{ $r->rangoTexto }})? Se cancelarán todos los días que aún estén a más de 40 minutos de empezar y se te devolverá 1 crédito por cada día.">
                        <i class="fa-solid fa-xmark"></i> Cancelar rango
                    </button>
                @endif
            </div>
        </div>
    @empty
        <div class="card-dz vacio">
            <i class="fa-regular fa-calendar"></i>
            <p>Aún no tienes reservas próximas.</p>
            <a href="{{ $urlReservar }}" class="btn-dz">
                <i class="fa-regular fa-calendar-plus"></i> Reservar clase
            </a>
        </div>
    @endforelse

    {{-- HISTORIAL --}}
    @if (count($historial))
        <div class="seccion-titulo">Historial</div>

        @foreach ($historial as $r)
            @php
                list($estadoTexto, $estadoClase) = $estado($r);
                $f = \Carbon\Carbon::parse($r->fecha);
            @endphp
            <div class="card-dz reserva-card pasada">
                <div class="reserva-info">
                    <div class="reserva-dia">
                        <strong>{{ $f->day }}</strong>
                        <small>{{ $meses[$f->month - 1] }}</small>
                    </div>
                    <div class="reserva-datos">
                        <strong>{{ $formatoFecha($r->fecha) }} · {{ $formatoHora($r) }}</strong>
                        <small>{{ $r->sede }} · Lugar N.° {{ $r->ubicacion }} · {{ $r->codigo }}</small>
                        @if (!empty($r->instructor))
                            <small><i class="fa-solid fa-person-chalkboard"></i> Instructor: {{ $r->instructor }}</small>
                        @endif
                        @if ($r->grupo)
                            <span class="rango-tag"><i class="fa-regular fa-calendar-days"></i> Rango: {{ $r->rangoTexto }}</span>
                        @endif
                        <span class="estado {{ $estadoClase }}">{{ $estadoTexto }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

@endsection

@push('scripts')
<script>
$(function () {

    function cancelar($btn) {
        $btn.prop('disabled', true);

        $.post($btn.data('url'))
            .done(function (r) {
                if (r && r.ok) {
                    toastr.success(r.mensaje || 'Reserva cancelada');
                    $('#reserva-' + $btn.data('id')).slideUp(250, function () {
                        window.location.reload();
                    });
                } else {
                    toastr.error((r && r.mensaje) || 'No se pudo cancelar la reserva.');
                    $btn.prop('disabled', false);
                }
            })
            .fail(function (xhr) {
                var r = xhr.responseJSON;
                toastr.error((r && r.mensaje) || 'No se pudo cancelar la reserva.');
                $btn.prop('disabled', false);
            });
    }

    $('.btn-cancelar-reserva').on('click', function () {
        var $btn = $(this);
        var mensaje = $btn.data('mensaje') || '¿Seguro que deseas cancelar esta reserva? Tu lugar quedará libre para otra persona y se te devolverá 1 crédito.';

        if (window.bootbox) {
            bootbox.confirm({
                message: mensaje,
                buttons: {
                    confirm: { label: 'Sí, cancelar', className: 'btn-danger' },
                    cancel:  { label: 'No', className: 'btn-default' }
                },
                callback: function (ok) { if (ok) cancelar($btn); }
            });
        } else if (window.confirm(mensaje)) {
            cancelar($btn);
        }
    });

});
</script>
@endpush
