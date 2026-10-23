<!DOCTYPE html>
{{--
|--------------------------------------------------------------------------
| Layout del CLIENTE (DANZZA)
|--------------------------------------------------------------------------
| Las vistas lo usan con:
|   @extends('layouts.master-cliente')
|   @section('content') ... @endsection
|   @push('styles') ... @endpush   (opcional)
|   @push('scripts') ... @endpush  (opcional)
|
| Rutas que usa este layout:
|   cliente.home  -> inicio del cliente (logo)
|   logout        -> POST, cerrar sesión (boton SALIR)
--}}
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'DANZZA')</title>

  <link rel="icon" type="image/svg+xml"
        href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/svgs/solid/person-dress.svg">
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
  <link rel="stylesheet" href="{{ asset('dist/css/typeaheadjs.css') }}">
  <link rel="stylesheet" href="{{ asset('dist/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('dist/css/toastr.min.css') }}">

  <style>
    :root {
      --primary: #dfff00;
      --primary-dark: #a9c900;
      --bg: #f1f0ed;
      --charcoal: #6e7378;
      --card: #6e7378;
      --border: rgba(223, 255, 0, .45);
      --line: rgba(255, 255, 255, .12);
      --line-light: #d3d2cd;
      --text: #ffffff;
      --muted: #ffffff;
      --muted-dark: #6b6b68;
      --link: #5f7700;
      --libre: #22c55e;
      --ocupado: #ef4444;
      --elegido: #facc15;
    }

    html, body {
      min-height: 100vh;
    }

    body {
      display: flex;
      flex-direction: column;
      margin: 0;
      background: var(--bg) !important;
      color: var(--charcoal);
      font-family: "Segoe UI", Arial, sans-serif !important;
      font-size: 15px !important;
      overflow-x: hidden;
    }

    a { color: var(--link); }
    a:hover { color: var(--charcoal); text-decoration: none; }

    .card-dz a { color: var(--primary); }


    /* =========================================================
       ENCABEZADO: LOGO + SALIR
    ========================================================= */

    .cliente-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      padding: 16px 30px;
      border-bottom: 3px solid var(--primary);
      background: var(--charcoal);
      box-shadow: 0 6px 20px rgba(0, 0, 0, .15);
      position: sticky;
      top: 0;
      z-index: 50;
    }

    .cliente-header .logo img {
      height: 48px;
      display: block;
    }

    .btn-salir {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 24px;
      border: 1px solid var(--primary);
      border-radius: 12px;
      background: transparent;
      color: var(--primary);
      font-size: 15px;
      font-weight: 800;
      letter-spacing: 1px;
      cursor: pointer;
      transition: .25s;
    }

    .btn-salir:hover {
      background: var(--primary);
      color: #070707;
    }


    /* =========================================================
       CONTENIDO
    ========================================================= */

    #app.cliente-main {
      flex: 1;
      width: 100%;
      max-width: 900px;
      margin: 0 auto;
      padding: 30px 20px 50px;
    }

    .card-dz {
      border: 1px solid var(--line);
      border-radius: 18px;
      background: var(--card);
      color: var(--text);
      padding: 22px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
    }

    .btn-dz {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      height: 48px;
      padding: 0 26px;
      border: none;
      border-radius: 12px;
      background: linear-gradient(90deg, #dfff00, #a9d000);
      color: #070707 !important;
      font-size: 15px;
      font-weight: 800;
      letter-spacing: 1px;
      cursor: pointer;
      transition: .25s;
    }

    .btn-dz:hover {
      box-shadow: 0 6px 20px rgba(169, 208, 0, .45);
    }

    .btn-dz:disabled {
      opacity: .4;
      cursor: not-allowed;
      box-shadow: none;
    }

    /* Fuera de una tarjeta el fondo es claro: borde y texto oscuros */
    .btn-dz-outline {
      background: transparent;
      border: 1px solid var(--charcoal);
      color: var(--charcoal) !important;
    }

    .btn-dz-outline:hover {
      background: var(--charcoal);
      color: var(--primary) !important;
    }

    .card-dz .btn-dz-outline {
      border-color: var(--primary);
      color: var(--primary) !important;
    }

    .card-dz .btn-dz-outline:hover {
      background: rgba(223, 255, 0, .10);
    }

    .link-home {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 18px;
      color: var(--charcoal);
      font-size: 15px;
      font-weight: 600;
    }

    .link-home:hover { color: var(--link); }


    /* =========================================================
       FOOTER: REDES SOCIALES + DIRECCION
    ========================================================= */

    .cliente-footer {
      border-top: 3px solid var(--primary);
      background: var(--charcoal);
      color: var(--text);
      padding: 26px 20px;
      text-align: center;
    }

    .cliente-footer .siguenos {
      font-weight: 700;
      margin-bottom: 14px;
    }

    .cliente-footer .redes {
      display: flex;
      justify-content: center;
      gap: 18px;
      margin-bottom: 14px;
    }

    .cliente-footer .redes a {
      width: 46px;
      height: 46px;
      border-radius: 50%;
      border: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--primary);
      font-size: 20px;
      transition: .25s;
    }

    .cliente-footer .redes a:hover {
      background: var(--primary);
      color: #070707;
    }

    .cliente-footer .direccion {
      color: var(--muted);
      font-size: 13px;
    }

    .cliente-footer .direccion i {
      color: var(--primary);
      margin-right: 6px;
    }


    @media (max-width: 768px) {
      .cliente-header {
        padding: 12px 16px;
      }

      .cliente-header .logo img {
        height: 38px;
      }

      .btn-salir {
        padding: 8px 16px;
        font-size: 13px;
      }

      #app.cliente-main {
        padding: 20px 16px 40px;
      }
    }
  </style>

  @stack('styles')
</head>

<body>

<header class="cliente-header">
  <a class="logo" href="{{ Route::has('cliente.home') ? route('cliente.home') : url('/') }}">
    <img src="{{ asset('image/logo-franja.png') }}" alt="DANZZA">
  </a>

  <form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" class="btn-salir">
      <i class="fa-solid fa-right-from-bracket"></i>
      <span>SALIR</span>
    </button>
  </form>
</header>

<main class="wrapper cliente-main" id="app">
  @yield('content')
</main>

<footer class="cliente-footer">
  <div class="siguenos">Síguenos y sé parte de Danzza.</div>
  <div class="redes">
    <a href="https://www.instagram.com/danzza_club/" target="_blank" title="Instagram"><i class="fa-brands fa-instagram"></i></a>
    <a href="https://www.facebook.com/DanzzaClub" target="_blank" title="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
    <a href="https://www.tiktok.com/@danzza_club?_r=1&_t=ZS-99sibVt3qlg" target="_blank" title="TikTok"><i class="fa-brands fa-tiktok"></i></a>
    <a href="https://wa.me/51944199037" target="_blank" title="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
  </div>
  <div class="direccion">
    <i class="fa-solid fa-location-dot"></i>
    Ca. Ollantay Mz. H Lt. 38 La Victoria – Chiclayo (A una cuadra del Paseo Yortuque)<br />
    Correo: danzzaclub@icloud.com <br />
    Teléfono: 944 199 037
  </div>
</footer>

<!-- REQUIRED SCRIPTS -->

<!-- jQuery -->
<script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

<!-- AdminLTE App -->
<script src="{{ asset('dist/js/adminlte.min.js') }}"></script>

<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('plugins/datatables/jquery.dataTables.js') }}"></script>
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('dist/js/funciones.js') }}"></script>
<script src="{{ asset('dist/js/bootbox.min.js') }}"></script>
{{-- typeahead.js-bootstrap: para autocompletar --}}
<script src="{{ asset('dist/js/bootstrap3-typeahead.min.js') }}"></script>
<script src="{{ asset('dist/js/bootstrap3-typeahead.js') }}"></script>
<script src="{{ asset('dist/js/typeahead.bundle.min.js') }}"></script>
<script src="{{ asset('dist/js/bloodhound.min.js') }}"></script>
<script src="{{ asset('dist/js/select2.min.js') }}"></script>
<script src="{{ asset('dist/js/select2.js') }}"></script>

<script src="{{ asset('dist/js/toastr.min.js') }}"></script>

{{-- jquery.inputmask: para mascaras en cajas de texto --}}
<script src="{{ asset('plugins/input-mask/jquery.inputmask.js') }}"></script>
<script src="{{ asset('plugins/input-mask/jquery.inputmask.extensions.js') }}"></script>
<script src="{{ asset('plugins/input-mask/jquery.inputmask.date.extensions.js') }}"></script>
<script src="{{ asset('plugins/input-mask/jquery.inputmask.numeric.extensions.js') }}"></script>
<script src="{{ asset('plugins/input-mask/jquery.inputmask.phone.extensions.js') }}"></script>
<script src="{{ asset('plugins/input-mask/jquery.inputmask.regex.extensions.js') }}"></script>

<script>
  $.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
  });

  @if (session('success'))
    $(function () { toastr.success({!! json_encode(session('success')) !!}); });
  @endif
  @if (session('error'))
    $(function () { toastr.error({!! json_encode(session('error')) !!}); });
  @endif
</script>

@stack('scripts')

</body>
</html>
