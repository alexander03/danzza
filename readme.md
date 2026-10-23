# DANZZA

Sistema de gestión (clientes, planes, horarios, reservas, instructores) sobre **Laravel 13** y **PHP 8.5**.

## Entorno de pruebas con Docker

Requisitos: Docker Desktop (Compose v2). El archivo `danzza (1).sql` (dump de la base actual) debe estar en la raíz del proyecto
y `danzza.zip` (web root del hosting) en la carpeta superior.

```bash
docker compose up -d --build
```

| Servicio   | URL / Puerto            | Credenciales               |
|------------|-------------------------|----------------------------|
| Aplicación | http://localhost:8080   | usuarios de la base        |
| phpMyAdmin | http://localhost:8081   | root / root                |
| MySQL 8.0  | localhost:3307          | danzza / danzza (BD `danzza`) |

- El dump se importa **solo la primera vez** (volumen vacío). Para reimportarlo desde cero:
  `docker compose down -v && docker compose up -d`
- `APP_KEY` y `JWT_SECRET` se generan automáticamente al iniciar el contenedor si no se definen.
- Logs de Laravel: `docker exec danzza-app tail -f storage/logs/laravel.log`
- Pruebas: `docker run --rm -v "$PWD:/var/www/html" -w /var/www/html danzza-base:php8.5 php vendor/bin/phpunit`
  (requiere `composer install` con dependencias de desarrollo; la imagen base se construye con
  `docker build --target base -t danzza-base:php8.5 .`)

## Migración desde PHP 7.1 / Laravel 5.7

| Antes                        | Ahora                                   |
|------------------------------|-----------------------------------------|
| PHP 7.1.3 / Laravel 5.7      | PHP 8.5.12 / Laravel 13                 |
| laravelcollective/html       | rdx/laravelcollective-html (fork compatible, mismo `Form::`) |
| tymon/jwt-auth 1.0           | php-open-source-saver/jwt-auth 2.9 (fork mantenido, misma API `auth('api')`) |
| maatwebsite/excel 3.1        | maatwebsite/excel 4.0 (PhpSpreadsheet 5) |
| barryvdh/laravel-dompdf 0.8  | 3.1 (`Barryvdh\DomPDF\Facade\Pdf`)      |
| Auth::routes() integrado     | laravel/ui 4                            |
| fideloper/proxy, laraveles/spanish | eliminados (incluidos en Laravel / `resources/lang/es`) |

Estructura: se adoptó el esqueleto de Laravel 11+ (`bootstrap/app.php` configura rutas, middleware y
excepciones; ya no existen `app/Http/Kernel.php`, `app/Console/Kernel.php` ni los `RouteServiceProvider`/
`EventServiceProvider`). Las rutas siguen usando `'Controlador@metodo'` con el namespace `App\Http\Controllers`.

La carpeta `public/` no está en el repositorio: se despliega aparte como `danzza/` en el hosting.
Para Docker, la imagen la extrae de `../danzza.zip` (contexto de build `webroot` en `docker-compose.yml`)
y usa un `index.php` de Laravel 13; el `index.php` del hosting sigue apuntando a `../../sisdanzza/`.
Sin Compose: `docker build --build-context webroot=.. -t danzza-app:php8.5 .`
