<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Login | DANZZA</title>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link rel="icon" type="image/svg+xml"
            href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/svgs/solid/person-dress.svg">

    <link rel="stylesheet" href="css/app.css">
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <link rel="stylesheet" href="dist/css/typeaheadjs.css">
    <link rel="stylesheet" href="dist/css/select2.min.css">
    <link rel="stylesheet" href="dist/css/toastr.min.css">
    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        :root {
            --primary: #dfff00;
            --primary-dark: #a9c900;
            --bg: #050607;
            --card: rgba(10, 12, 13, .78);
            --border: rgba(223, 255, 0, .35);
            --text: #ffffff;
            --muted: #a7a7a7;
        }
        .modal-backdrop.in,
        .modal-backdrop.show{
            background-color: #1c1d1d;
            opacity: .55;
        }
        .modal-content{
            border: none;
            border-radius: 18px;
            overflow: hidden;
            background-color: #f1f0ed;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .35);
        }
        .modal-title{
            color: #dfff00;
            font-weight: 800;
            letter-spacing: 1px;
        }
        .modal-header{
            background-color: #2b2c2c;
            border-bottom: 3px solid #dfff00;
        }
        .modal-header .close{
            color: #ffffff;
            opacity: .8;
            text-shadow: none;
        }
        .modal-body{
            background-color: #f1f0ed;
            color: #2b2c2c;
        }
        .modal-body label{
            color: #2b2c2c;
            font-weight: 700;
        }
        .modal-body .form-control{
            height: 40px;
            background-color: #ffffff;
            border: 1px solid #d3d2cd;
            border-radius: 10px;
            color: #2b2c2c;
        }
        .modal-body .form-control:focus{
            border-color: #a9c900;
            box-shadow: 0 0 0 3px rgba(223, 255, 0, .35);
        }
        .modal-footer{
            background-color: #f1f0ed;
            border-top: 1px solid #d3d2cd;
        }
        .btn-primary{
            background: linear-gradient(90deg, #dfff00, #a9d000);
            border: none;
            border-radius: 10px;
            color: #161615;
            font-weight: 800;
            height: 40px;
            font-size: 17px;
        }
        .btn-primary:hover,
        .btn-primary:focus{
            color: #161615;
            box-shadow: 0 6px 20px rgba(169, 208, 0, .45);
        }
        .btn-default{
            background-color: #ffffff;
            border: none;
            border-radius: 10px;
            color: bla;
            height: 40px;
            font-size: 17px;
        }
        .btn-default:hover,
        .btn-default:focus{
            background-color: #1a0303;
            color: #dfff00;
        }
        #btnRegistrarCliente:hover {
            text-decoration: underline;
        }
        body {
            min-height: 100vh;

            color: var(--text);

            font-family:
                "Segoe UI",
                Arial,
                sans-serif;

            overflow-x: hidden;
        }


        .login-container {
            width: 100%;
            min-height: 100vh;

            position: relative;

            display: flex;
            align-items: center;
            justify-content: center;

            background-image: url('{{ asset("image/fondo-home.png") }}');

            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;

            overflow: hidden;
        }


        /*
        |--------------------------------------------------------------------------
        | Capa oscura SOLO para facilitar lectura del login
        |--------------------------------------------------------------------------
        */

        .login-container::before {
            content: "";

            position: absolute;
            inset: 0;

            background: linear-gradient(
                90deg,
                rgba(0, 0, 0, .65) 0%,
                rgba(0, 0, 0, .35) 38%,
                rgba(0, 0, 0, .05) 65%,
                rgba(0, 0, 0, .05) 100%
            );

            pointer-events: none;
        }


        /*
        |--------------------------------------------------------------------------
        | LOGIN A LA IZQUIERDA
        |--------------------------------------------------------------------------
        */

        .login-content {

            position: relative;

            z-index: 5;

            width: 50%;

            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 40px 60px;

        }


        /*
        |--------------------------------------------------------------------------
        | CONTENIDO DEL LOGIN
        |--------------------------------------------------------------------------
        */

        .login-box {

            width: 100%;

            max-width: 560px;

        }



        /* =========================================================
           LOGO
        ========================================================= */

        .brand {
            width: 100%;
            text-align: center;
            margin-bottom: 10px;
        }

        .brand-logo {
            display: block;
            width: 100%;
            max-width: 400px;
            height: 200px;
            margin: 0 auto;
        }


        /*
        Si todavía no tienes el logo como imagen,
        puedes utilizar este texto temporalmente.
        */

        .brand-text {

            font-size: 52px;

            font-weight: 800;

            font-style: italic;

            letter-spacing: 4px;

            color: white;

            line-height: 1;

        }


        .brand-text span {

            color: var(--primary);

        }


        .brand-slogan {

            margin-top: 12px;

            font-size: 13px;

            letter-spacing: 5px;

            color: #ddd;

            text-transform: uppercase;

        }


        .brand-slogan strong {

            color: var(--primary);

        }


        /* =========================================================
           BIENVENIDA
        ========================================================= */

        .welcome {

            text-align: center;

            margin-bottom: 20px;

        }


        .welcome h1 {

            font-size: 34px;

            font-weight: 700;

            margin-bottom: 10px;

        }


        .welcome p {

            color: white;

            font-size: 16px;

        }


        /* =========================================================
           FORMULARIO
        ========================================================= */

        .form-group {

            margin-bottom: 10px;

        }


        .input-wrapper {

            position: relative;

        }


        .input-wrapper > i {

            position: absolute;

            left: 25px;

            top: 50%;

            transform: translateY(-50%);

            color: white;

            font-size: 23px;

            z-index: 2;

        }


        .form-control {

            width: 100%;

            height: 50px;

            padding:
                0 60px;

            border-radius: 15px;

            border: 1px solid var(--border);

            background:
                rgba(5, 7, 8, .75);

            color: white;

            font-size: 17px;

            outline: none;

            transition: .25s;

            backdrop-filter: blur(10px);

        }


        .form-control::placeholder {

            color: #898989;

        }


        .form-control:focus {

            border-color: var(--primary);

            box-shadow:
                0 0 0 2px rgba(223,255,0,.08),
                0 0 25px rgba(223,255,0,.08);

        }


        /* =========================================================
           MOSTRAR PASSWORD
        ========================================================= */

        .password-toggle {

            position: absolute;

            right: 23px;

            top: 50%;

            transform: translateY(-50%);

            background: transparent;

            border: none;

            color: #aaa;

            font-size: 25px;

            cursor: pointer;

        }


        .password-toggle:hover {

            color: var(--primary);

        }


        /* =========================================================
           OPCIONES
        ========================================================= */

        .form-options {

            display: flex;

            align-items: center;

            justify-content: space-between;

            margin:
                22px 5px 30px;

        }


        .remember {

            display: flex;

            align-items: center;

            gap: 10px;

            color: #ddd;

            font-size: 16px;

            cursor: pointer;

        }


        .remember input {

            appearance: none;

            width: 29px;

            height: 29px;

            border:

                2px solid var(--primary);

            border-radius: 4px;

            cursor: pointer;

        }


        .remember input:checked {

            background: var(--primary);

            position: relative;

        }


        .remember input:checked::after {

            content: "✓";

            position: absolute;

            color: #000;

            font-weight: bold;

            left: 5px;

            top: 1px;

        }


        .forgot-password {

            color: black;

            text-decoration: none;

            font-size: 15px;

        }


        .forgot-password:hover {

            text-decoration: underline;

        }


        /* =========================================================
           BOTON LOGIN
        ========================================================= */

        .btn-login {

            width: 100%;

            height: 50px;

            border: none;

            border-radius: 15px;

            background:
                linear-gradient(
                    90deg,
                    #dfff00,
                    #a9d000
                );

            color: #070707;

            font-size: 19px;

            font-weight: 800;

            letter-spacing: 1px;

            cursor: pointer;

            display: flex;

            align-items: center;

            justify-content: center;

            gap: 25px;

            box-shadow:
                0 8px 30px
                rgba(223,255,0,.18);

            transition: .25s;

        }


        .btn-login i {

            font-size: 30px;

        }


        .btn-login:hover {

            transform: translateY(-2px);

            box-shadow:
                0 12px 40px
                rgba(223,255,0,.30);

        }


        /* =========================================================
           DIVISOR
        ========================================================= */

        .divider {

            display: flex;

            align-items: center;

            gap: 20px;

            margin: 35px 10px 25px;

            color: white;

            font-size: 17px;

        }

        .divider::before,
        .divider::after {

            content: "";

            flex: 1;

            height: 1px;

            background:
                rgba(255,255,255,.18);

        }


        /* =========================================================
           REDES SOCIALES
        ========================================================= */

        .social-login {

            display: flex;

            justify-content: center;

            gap: 45px;

            margin-bottom: 35px;

        }


        .social-button {

            width: 70px;

            height: 70px;

            border-radius: 50%;

            border: 1px solid
                rgba(223,255,0,.45);

            background:
                rgba(5,7,8,.75);

            display: flex;

            align-items: center;

            justify-content: center;

            text-decoration: none;

            font-size: 30px;

            transition: .25s;

        }


        .social-button:hover {

            transform: translateY(-4px);

            border-color: var(--primary);

            box-shadow:
                0 0 20px
                rgba(223,255,0,.15);

        }


        .instagram {

            color: #ff3c9d;

        }


        .facebook {

            color: #1877f2;

        }


        .whatsapp {

            color: #25d366;

        }


        /* =========================================================
           BENEFICIOS
        ========================================================= */

        .features {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            background:
                rgba(10,12,13,.80);

            border:
                1px solid
                rgba(255,255,255,.10);

            border-radius: 20px;

            padding: 25px 10px;

            backdrop-filter: blur(12px);

        }


        .feature {

            text-align: center;

            padding:
                5px 18px;

            border-right:
                1px solid
                rgba(255,255,255,.20);

        }


        .feature:last-child {

            border-right: none;

        }


        .feature i {

            color: var(--primary);

            font-size: 32px;

            margin-bottom: 15px;

        }


        .feature p {

            color: #ddd;

            font-size: 15px;

            line-height: 1.5;

        }


        /* =========================================================
           FOOTER
        ========================================================= */

        .system-description {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-top: 30px;
            margin-bottom: 20px;
            color: black;
            font-size: 14px;

        }


        .system-description i {
            color: black;
            font-size: 22px;
        }


        /* =========================================================
           ERRORES
        ========================================================= */

        .alert {

            padding: 15px 20px;

            margin-bottom: 20px;

            border-radius: 10px;

            background:
                rgba(255,50,50,.10);

            border:
                1px solid
                rgba(255,50,50,.30);

            color: #ff9999;

        }

        .invalid-feedback {

            display: block;

            margin-top: 6px;

            padding-left: 15px;

            color: #f30000;

            font-size: 14px;

        }

        .form-control.is-invalid {

            border-color: rgba(255,50,50,.60);

            background-image: none;

        }


        /* =========================================================
           RESPONSIVE
        ========================================================= */

        @media (max-width: 768px) {

            .login-container {

                background-position: 65% center;

            }

            .login-content {

                width: 100%;

                min-height: 100vh;

                padding: 30px 25px;

            }

            .login-box {

                max-width: 500px;

            }

            .features {
                grid-template-columns: repeat(2, 1fr);
                row-gap: 20px;
            }

            .feature {
                padding: 5px 8px;
                min-width: 0;
                overflow-wrap: anywhere;
            }

            .feature:nth-child(2n) {
                border-right: none;
            }

        }

    </style>

</head>


<body>

<div class="login-container">

    <div class="login-content">

        <div class="login-box">

            <div class="brand">
                <img src="{{ asset('image/logo.png') }}" alt="DANZZA" class="brand-logo">
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">

                    <div class="input-wrapper">

                        <i class="fa-regular fa-user"></i>
                        <input type="text" name="login" class="form-control{{ $errors->has('login') ? ' is-invalid' : '' }}" placeholder="Usuario" value="{{ old('login') }}" required >
                    </div>
                    @if ($errors->has('login'))
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $errors->first('login') }}</strong>
                    </span>
                    @endif

                </div>
                <div class="form-group">
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" id="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" placeholder="Contraseña" required >
                        <button type="button" class="password-toggle" onclick="togglePassword()" >
                            <i id="passwordIcon" class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    @if ($errors->has('password'))
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $errors->first('password') }}</strong>
                    </span>
                    @endif

                </div>
                <button type="submit" class="btn-login" >
                    <span>INICIAR SESIÓN</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
                <div class="form-options" style="justify-content: flex-end;">
                    <a href="{{ Route::has('password.request') ? route('password.request') : '#' }}" class="forgot-password">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>
            </form>
            <div class="system-description" onclick="modal('webservices/modalCliente', 'Registrar Cliente', this);" style="cursor: pointer;" id="btnRegistrarCliente">
                ¿Aún no tienes una cuenta?<span style="font-weight: bold; margin-left: 0px;">Registrate aquí</span>
            </div>
            <div class="system-description" style="font-weight: bold;">
                Síguenos y sé parte de Danzza.
            </div>
            <div class="features">
                <div class="feature" onclick="window.open('https://www.instagram.com/danzza_club/', '_blank')" style="cursor: pointer;">
                    <i class="fa-brands fa-instagram"></i>
                    @danza_club
                </div>

                <div class="feature" onclick="window.open('https://www.facebook.com/danzzaclub.pe', '_blank')" style="cursor: pointer;">
                    <i class="fa-brands fa-facebook"></i>
                    @Danzza Club 
                </div>

                <div class="feature" onclick="window.open('https://www.tiktok.com/@danzza_club?_r=1&_t=ZS-99sibVt3qlg', '_blank')" style="cursor: pointer;">
                    <i class="fa-brands fa-tiktok"></i>
                    @danza_club
                </div>

                <div class="feature" onclick="window.open('https://wa.me/+51944199037', '_blank')" style="cursor: pointer;">
                    <i class="fa-brands fa-whatsapp"></i>
                    @danza_club
                </div>
            </div>
            <div class="system-description">
                <i class="fa-solid fa-map-location"></i>
                <span>
                    Ca. Ollantay Mz. H Lt. 38 La Victoria – Chiclayo <br /> (A una cuadra del Paseo Yortuque)<br />
                    Correo: danzzaclub@icloud.com <br />
                    Teléfono: 944 199 037
                </span>
            </div>
        </div>

    </div>

</div>
<script>

function togglePassword()
{
    const password =
        document.getElementById('password');

    const icon =
        document.getElementById('passwordIcon');


    if (password.type === 'password') {

        password.type = 'text';

        icon.classList.remove(
            'fa-eye'
        );

        icon.classList.add(
            'fa-eye-slash'
        );

    } else {

        password.type = 'password';

        icon.classList.remove(
            'fa-eye-slash'
        );

        icon.classList.add(
            'fa-eye'
        );

    }
}

</script>
<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<script src="js/app.js"></script>
<script src="dist/js/funciones.js"></script>
<script src="dist/js/bootbox.min.js"></script>
<script src="dist/js/toastr.min.js"></script>
</body>

</html>