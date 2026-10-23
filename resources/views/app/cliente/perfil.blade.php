{{--
|--------------------------------------------------------------------------
| Mi perfil del CLIENTE
|--------------------------------------------------------------------------
| Variables que envía ClienteController@perfil:
|   $cliente   cliente autenticado
|   $usuario   usuario autenticado (login)
|
| Rutas que usa:
|   cliente.perfil.store     -> guarda datos personales
|   cliente.perfil.password  -> cambia la contraseña
|   cliente.planes           -> Cambiar plan / Activar plan
--}}
@extends('layouts.master-cliente')

@section('title', 'Mi perfil | DANZZA')

@php
    $planActual = $cliente->plan;
    $errDatos = $errors->getBag('datos');
    $errPassword = $errors->getBag('password');
    $fechaNac = $cliente->fechanacimiento ? \Carbon\Carbon::parse($cliente->fechanacimiento)->format('Y-m-d') : '';
@endphp

@push('styles')
<style>
    .perfil-titulo {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 22px;
    }

    .perfil-titulo h1 {
        margin: 0;
        font-size: 28px;
        font-weight: 800;
        color: var(--charcoal);
    }

    .perfil-titulo span {
        color: var(--muted-dark);
    }

    .alerta {
        margin-bottom: 18px;
        padding: 12px 16px;
        border-radius: 12px;
        font-weight: 700;
    }

    .alerta-ok {
        background: linear-gradient(90deg, #dfff00, #a9d000);
        color: #070707;
    }

    .alerta-error {
        background: rgba(239, 68, 68, .15);
        color: #f87171;
    }

    .alerta-error ul {
        margin: 0;
        padding-left: 18px;
    }

    .perfil-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 18px;
        align-items: start;
    }

    .perfil-grid .card-dz {
        margin-bottom: 18px;
    }

    .perfil-grid h2 {
        margin: 0 0 16px;
        font-size: 17px;
        font-weight: 800;
    }

    .campos {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
    }

    .campo label {
        display: block;
        margin-bottom: 6px;
        color: var(--muted);
        font-size: 13px;
        font-weight: 700;
    }

    .campo input {
        width: 100%;
        height: 42px;
        padding: 0 12px;
        border: 1px solid var(--line);
        border-radius: 10px;
        background: transparent;
        color: #fff;
    }

    .campo input:focus {
        outline: none;
        border-color: var(--primary);
    }

    .campo input[readonly] {
        opacity: .6;
        cursor: not-allowed;
    }

    .campo-completo {
        grid-column: 1 / -1;
    }

    .acciones {
        margin-top: 18px;
        text-align: right;
    }

    .plan-resumen .etiqueta {
        color: var(--muted);
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 2px;
    }

    .plan-resumen .nombre {
        margin: 4px 0 10px;
        color: var(--primary);
        font-size: 24px;
        font-weight: 800;
    }

    .plan-resumen .dato {
        color: #ddd;
    }

    .plan-resumen .btn-dz {
        margin-top: 14px;
        width: 100%;
        justify-content: center;
    }

    @media (max-width: 992px) {
        .perfil-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 576px) {
        .campos {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')

    <div class="perfil-titulo">
        <div>
            <h1>Mi perfil</h1>
            <span>Revisa y actualiza tus datos.</span>
        </div>
        <a href="{{ route('cliente.home') }}" class="btn-dz btn-dz-outline">
            <i class="fa-solid fa-house"></i> Volver al inicio
        </a>
    </div>

    @if (session('mensaje'))
        <div class="alerta alerta-ok"><i class="fa-solid fa-circle-check"></i> {{ session('mensaje') }}</div>
    @endif

    <div class="perfil-grid">
        <div>
            <div class="card-dz">
                <h2>Datos personales</h2>
                @if ($errDatos->any())
                    <div class="alerta alerta-error">
                        <ul>
                            @foreach ($errDatos->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('cliente.perfil.store') }}">
                    @csrf
                    <div class="campos">
                        <div class="campo">
                            <label for="documento">DNI</label>
                            <input type="text" id="documento" value="{{ $cliente->documento }}" readonly>
                        </div>
                        <div class="campo">
                            <label for="usuario">Usuario</label>
                            <input type="text" id="usuario" value="{{ $usuario->login }}" readonly>
                        </div>
                        <div class="campo campo-completo">
                            <label for="nombres">Nombres</label>
                            <input type="text" name="nombres" id="nombres" value="{{ old('nombres', $cliente->nombres) }}">
                        </div>
                        <div class="campo">
                            <label for="apellidopaterno">Apellido paterno</label>
                            <input type="text" name="apellidopaterno" id="apellidopaterno" value="{{ old('apellidopaterno', $cliente->apellidopaterno) }}">
                        </div>
                        <div class="campo">
                            <label for="apellidomaterno">Apellido materno</label>
                            <input type="text" name="apellidomaterno" id="apellidomaterno" value="{{ old('apellidomaterno', $cliente->apellidomaterno) }}">
                        </div>
                        <div class="campo">
                            <label for="fechanacimiento">Fecha de nacimiento</label>
                            <input type="date" name="fechanacimiento" id="fechanacimiento" value="{{ old('fechanacimiento', $fechaNac) }}">
                        </div>
                        <div class="campo">
                            <label for="celular">Celular</label>
                            <input type="text" name="celular" id="celular" value="{{ old('celular', $cliente->celular) }}">
                        </div>
                        <div class="campo">
                            <label for="correo">Correo</label>
                            <input type="email" name="correo" id="correo" value="{{ old('correo', strtolower($cliente->correo)) }}">
                        </div>
                        <div class="campo">
                            <label for="direccion">Dirección</label>
                            <input type="text" name="direccion" id="direccion" value="{{ old('direccion', $cliente->direccion) }}">
                        </div>
                    </div>
                    <div class="acciones">
                        <button type="submit" class="btn-dz">
                            <i class="fa-solid fa-floppy-disk"></i> Guardar cambios
                        </button>
                    </div>
                </form>
            </div>

            <div class="card-dz">
                <h2>Cambiar contraseña</h2>
                @if ($errPassword->any())
                    <div class="alerta alerta-error">
                        <ul>
                            @foreach ($errPassword->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('cliente.perfil.password') }}" autocomplete="off">
                    @csrf
                    <div class="campos">
                        <div class="campo campo-completo">
                            <label for="password_actual">Contraseña actual</label>
                            <input type="password" name="password_actual" id="password_actual">
                        </div>
                        <div class="campo">
                            <label for="password">Nueva contraseña</label>
                            <input type="password" name="password" id="password">
                        </div>
                        <div class="campo">
                            <label for="password_confirmation">Confirmar contraseña</label>
                            <input type="password" name="password_confirmation" id="password_confirmation">
                        </div>
                    </div>
                    <div class="acciones">
                        <button type="submit" class="btn-dz btn-dz-outline">
                            <i class="fa-solid fa-key"></i> Cambiar contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card-dz plan-resumen">
            <div class="etiqueta">Mi plan</div>
            @if ($planActual)
                <div class="nombre">{{ $planActual->nombre }}</div>
                <!--div class="dato">Clases disponibles: <b>{{ $planActual->creditos }}</b></div-->
                <div class="dato">Clases disponibles: <b>{{ $cliente->creditos }}</b></div>
                <a href="{{ route('cliente.planes') }}" class="btn-dz btn-dz-outline">
                    <i class="fa-solid fa-arrows-rotate"></i> Cambiar plan
                </a>
            @else
                <div class="dato">Aún no tienes un plan activo.</div>
                <a href="{{ route('cliente.planes') }}" class="btn-dz">
                    <i class="fa-solid fa-bolt"></i> Activar plan
                </a>
            @endif
        </div>
    </div>

@endsection
