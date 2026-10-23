<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// El error_reporting que estaba aquí se movió a App\Providers\AppServiceProvider::boot()

//Clear Cache facade value:
Route::get('/clear-cache', function() {
    $exitCode = Artisan::call('cache:clear');
    return '<h1>Cache facade value cleared</h1>';
});
//Reoptimized class loader:
Route::get('/optimize', function() {
    $exitCode = Artisan::call('optimize');
    return '<h1>Reoptimized class loader</h1>';
});
//Route cache:
Route::get('/route-cache', function() {
    $exitCode = Artisan::call('route:cache');
    return '<h1>Routes cached</h1>';
});
//Clear Route cache:
Route::get('/route-clear', function() {
    $exitCode = Artisan::call('route:clear');
    return '<h1>Route cache cleared</h1>';
});
//Clear View cache:
Route::get('/view-clear', function() {
    $exitCode = Artisan::call('view:clear');
    return '<h1>View cache cleared</h1>';
});
//Clear Config cache:
Route::get('/config-cache', function() {
    $exitCode = Artisan::call('config:cache');
    return '<h1>Clear Config cleared</h1>';
});

Route::get('/', function () {
    //return view('auth.login');
    return view('auth.login-home');
});

Route::get('/asd', function () {
    //return view('auth.login');
    return view('auth.login-home');
});

Route::get('/login-home', function () {
    return view('auth.login-home');
});

Route::get('/login', function () {
    //return view('auth.login');
    return view('auth.login');
});

Route::get('webservices/cargarMegaMenu', 'WebServicesController@cargarMegaMenu')->middleware('cors');
Route::get('webservices/principal', 'WebServicesController@principal')->middleware('cors');
Route::get('webservices/catalogo', 'WebServicesController@catalogo')->middleware('cors');
Route::get('webservices/buscarProducto', 'WebServicesController@buscarProducto')->middleware('cors');
Route::get('webservices/productoautocompletar', 'WebServicesController@productoautocompletar')->middleware('cors');
Route::get('webservices/producto', 'WebServicesController@producto')->middleware('cors');
Route::post('webservices/registrarPedido', 'WebServicesController@registrarPedido')->middleware('cors');
Route::get('webservices/modalCliente', 'WebServicesController@modalCliente')->middleware('cors');
Route::post('webservices/storeCliente', 'WebServicesController@storeCliente')->middleware('cors')->name('webservices.storeCliente');
Route::get('webservices/modalPlanes', 'WebServicesController@modalPlanes')->middleware('cors');
/* "Ver horarios" de la lista pública de planes: solo consulta, sin login */
Route::get('webservices/horarios', 'WebServicesController@horarios')->middleware('cors')->name('webservices.horarios');
Route::post('webservices/buscarDNI', 'WebServicesController@buscarDNI')->middleware('cors');

Auth::routes();

//Route::get('/home', 'HomeController@index')->name('home');

Route::group(['middleware' => 'auth'], function () {
    Route::get('/home', function () {
        if(Auth::user()->usertype_id!=5){
            return View::make('layouts.master');
        }else{
            return View::make('app.cliente.home');
        }
    });
    Route::get('/master-cliente', function () {
        // Si el cliente aún no tiene plan (ni un pago de plan en validación), al ingresar
        // se le envía a elegir uno (una vez por sesión)
        $cliente = \App\Cliente::find(Auth::user()->cliente_id);
        $pagoEnValidacion = $cliente && \App\Planpago::where('cliente_id', '=', $cliente->id)->where('situacion', '=', 'PENDIENTE')->exists();
        if ($cliente && !$cliente->plan && !$pagoEnValidacion && !session('planes_sugeridos')) {
            session(['planes_sugeridos' => true]);
            return redirect()->route('cliente.planes');
        }
        return View::make('app.cliente.home');
    })->name('cliente.home');
    Route::post('categoriaopcionmenu/buscar', 'CategoriaopcionmenuController@buscar')->name('categoriaopcionmenu.buscar');
    Route::get('categoriaopcionmenu/eliminar/{id}/{listarluego}', 'CategoriaopcionmenuController@eliminar')->name('categoriaopcionmenu.eliminar');
    Route::resource('categoriaopcionmenu', 'CategoriaopcionmenuController', array('except' => array('show')));

    Route::post('opcionmenu/buscar', 'OpcionmenuController@buscar')->name('opcionmenu.buscar');
    Route::get('opcionmenu/eliminar/{id}/{listarluego}', 'OpcionmenuController@eliminar')->name('opcionmenu.eliminar');
    Route::resource('opcionmenu', 'OpcionmenuController', array('except' => array('show')));

    Route::post('tipousuario/buscar', 'TipousuarioController@buscar')->name('tipousuario.buscar');
    Route::get('tipousuario/obtenerpermisos/{listar}/{id}', 'TipousuarioController@obtenerpermisos')->name('tipousuario.obtenerpermisos');
    Route::post('tipousuario/guardarpermisos/{id}', 'TipousuarioController@guardarpermisos')->name('tipousuario.guardarpermisos');
    Route::get('tipousuario/eliminar/{id}/{listarluego}', 'TipousuarioController@eliminar')->name('tipousuario.eliminar');
    Route::resource('tipousuario', 'TipousuarioController', array('except' => array('show')));

    Route::post('usuario/buscar', 'UsuarioController@buscar')->name('usuario.buscar');
    Route::get('usuario/eliminar/{id}/{listarluego}', 'UsuarioController@eliminar')->name('usuario.eliminar');
    Route::resource('usuario', 'UsuarioController', array('except' => array('show')));
    Route::get('usuario/personautocompletar/{searching}', 'UsuarioController@personautocompletar')->name('usuario.personautocompletar');
    Route::get('usuario/cambiarcaja', 'UsuarioController@cambiarcaja')->name('usuario.cambiarcaja');

    /* Zona -> Modalidad */
    Route::post('modalidad/buscar', 'ModalidadController@buscar')->name('modalidad.buscar');
    Route::get('modalidad/eliminar/{id}/{listarluego}', 'ModalidadController@eliminar')->name('modalidad.eliminar');
    Route::resource('modalidad', 'ModalidadController', array('except' => array('show')));

    /* Plan */
    Route::post('plan/buscar', 'PlanController@buscar')->name('plan.buscar');
    Route::get('plan/eliminar/{id}/{listarluego}', 'PlanController@eliminar')->name('plan.eliminar');
    Route::resource('plan', 'PlanController', array('except' => array('show')));

    /* Clientes */
    Route::post('cliente/buscar', 'ClienteController@buscar')->name('cliente.buscar');
    Route::get('cliente/eliminar/{id}/{listarluego}', 'ClienteController@eliminar')->name('cliente.eliminar');
    Route::get('cliente/reservar',function () {
        return View::make('app.cliente.reservar');
    })->name('cliente.reservar');
    Route::post('cliente/reservar/store', 'ClienteController@storeReserva')->name('cliente.reservar.store');
    Route::get('cliente/horarios', 'ClienteController@horarios')->name('cliente.horarios');
    Route::get('cliente/lugares', 'ClienteController@lugares')->name('cliente.lugares');
    Route::get('cliente/reservas', 'ClienteController@reservas')->name('cliente.reservas');
    Route::post('cliente/reservas/{id}/cancelar', 'ClienteController@cancelarReserva')->name('cliente.reservas.cancelar');
    /* Reserva por rango de fechas */
    Route::post('cliente/reservar/rango', 'ClienteController@storeReservaRango')->name('cliente.reservar.rango');
    Route::post('cliente/reservas/rango/{id}/cancelar', 'ClienteController@cancelarRango')->name('cliente.reservas.cancelarrango');
    Route::get('cliente/planes', 'ClienteController@planes')->name('cliente.planes');
    Route::post('cliente/planes', 'ClienteController@planesStore')->name('cliente.planes.store');
    /* Pago del plan (primera vez o cambio) y validación por el administrador */
    Route::get('cliente/planes/pagar/{plan}', 'ClienteController@planesPagar')->name('cliente.planes.pagar');
    Route::post('cliente/planes/pagar/{plan}', 'ClienteController@planesPagarStore')->name('cliente.planes.pagar.store');
    Route::get('cliente/validarpago/{id}/{listarluego}', 'ClienteController@validarPagoPlan')->name('cliente.validarpago');
    Route::post('cliente/validarpago/{id}', 'ClienteController@validarPagoPlanStore')->name('cliente.validarpago.store');
    /* Asignar plan a un cliente sin plan (administrador): registra el pago PENDIENTE */
    Route::get('cliente/asignarplan/{id}/{listarluego}', 'ClienteController@asignarPlan')->name('cliente.asignarplan');
    Route::post('cliente/asignarplan/{id}', 'ClienteController@asignarPlanStore')->name('cliente.asignarplan.store');
    Route::get('cliente/perfil', 'ClienteController@perfil')->name('cliente.perfil');
    Route::post('cliente/perfil', 'ClienteController@perfilStore')->name('cliente.perfil.store');
    Route::post('cliente/perfil/password', 'ClienteController@perfilPassword')->name('cliente.perfil.password');
    Route::get('cliente/confirmar/{id}/{listarluego}', 'ClienteController@confirmar')->name('cliente.confirmar');
    Route::get('cliente/confirmarAsistencia/{id}', 'ClienteController@confirmarAsistencia')->name('cliente.confirmarAsistencia');
    Route::get('cliente/confirmarAsistenciaModal/{id}', 'ClienteController@confirmarAsistenciaModal')->name('cliente.confirmarAsistenciaModal');
    Route::post('cliente/confirmarAsistenciaPost/{id}', 'ClienteController@confirmarAsistenciaPost')->name('cliente.confirmarAsistenciaPost');
    Route::resource('cliente', 'ClienteController', array('except' => array('show')));

    /* SUCURSAL */
    Route::post('sucursal/buscar', 'sucursalController@buscar')->name('sucursal.buscar');
    Route::get('sucursal/eliminar/{id}/{listarluego}', 'sucursalController@eliminar')->name('sucursal.eliminar');
    Route::resource('sucursal', 'sucursalController', array('except' => array('show')));

    /* Clase */
    Route::post('clase/buscar', 'ClaseController@buscar')->name('clase.buscar');
    Route::get('clase/eliminar/{id}/{listarluego}', 'ClaseController@eliminar')->name('clase.eliminar');
    Route::resource('clase', 'ClaseController', array('except' => array('show')));

    /* Instructor */
    Route::post('instructor/buscar', 'InstructorController@buscar')->name('instructor.buscar');
    Route::get('instructor/eliminar/{id}/{listarluego}', 'InstructorController@eliminar')->name('instructor.eliminar');
    Route::resource('instructor', 'InstructorController', array('except' => array('show')));

    /* PERSONA */
    Route::post('persona/buscar', 'PersonaController@buscar')->name('persona.buscar');
    Route::get('persona/eliminar/{id}/{listarluego}', 'PersonaController@eliminar')->name('persona.eliminar');
    Route::get('persona/import', 'PersonaController@import')->name('persona.import');
    Route::post('persona/saveimport', 'PersonaController@saveimport')->name('persona.saveimport');
    Route::resource('persona', 'PersonaController', array('except' => array('show')));
    Route::post('persona/buscarDNI', 'PersonaController@buscarDNI')->name('persona.buscarDNI');
    Route::post('persona/buscarRUC', 'PersonaController@buscarRUC')->name('persona.buscarRUC');

    /* Horario */
    Route::post('horario/buscar', 'HorarioController@buscar')->name('horario.buscar');
    Route::get('horario/eliminar/{id}/{listarluego}', 'HorarioController@eliminar')->name('horario.eliminar');
    Route::resource('horario', 'HorarioController', array('except' => array('show')));

    /* Reserva */
    Route::post('reserva/buscar', 'ReservaController@buscar')->name('reserva.buscar');
    Route::get('reserva/eliminar/{id}/{listarluego}', 'ReservaController@eliminar')->name('reserva.eliminar');
    Route::resource('reserva', 'ReservaController', array('except' => array('show')));
    Route::get('reserva/cargarhorarios', 'ReservaController@cargarHorarios')->name('reserva.cargarhorarios');
    Route::get('reserva/horariosfiltro', 'ReservaController@horariosFiltro')->name('reserva.horariosfiltro');
    Route::get('reserva/lugareslibres', 'ReservaController@lugaresLibres')->name('reserva.lugareslibres');
    Route::get('reserva/clienteautocompletar/{searching}', 'ReservaController@clienteautocompletar')->name('reserva.clienteautocompletar');
    Route::get('reserva/confirmar/{id}/{listarluego}', 'ReservaController@confirmar')->name('reserva.confirmar');
    Route::post('reserva/confirmarAsistencia/{id}', 'ReservaController@confirmarAsistencia')->name('reserva.confirmarAsistencia');
    /* Validar ingreso en grupo (checkbox en el listado) */
    Route::get('reserva/confirmargrupo', 'ReservaController@confirmarGrupo')->name('reserva.confirmargrupo');
    Route::post('reserva/confirmarasistenciagrupo', 'ReservaController@confirmarAsistenciaGrupo')->name('reserva.confirmarAsistenciaGrupo');
    Route::get('reserva/validarinstructor/{id}/{listarluego}', 'ReservaController@validarInstructor')->name('reserva.validarinstructor');
    Route::post('reserva/validarinstructor/{id}', 'ReservaController@validarInstructorStore')->name('reserva.validarinstructor.store');

    /* Validar horario: confirma el instructor y después el administrador (por horario + fecha) */
    Route::get('validarhorario', 'ValidarHorarioController@index')->name('validarhorario.index');
    Route::post('validarhorario/buscar', 'ValidarHorarioController@buscar')->name('validarhorario.buscar');
    Route::get('validarhorario/confirmar/{horario}/{fecha}', 'ValidarHorarioController@confirmar')->name('validarhorario.confirmar');
    Route::post('validarhorario/confirmar', 'ValidarHorarioController@confirmarStore')->name('validarhorario.confirmarstore');

    /* CONFIGURACION */
    Route::post('configuracion/buscar', 'ConfiguracionController@buscar')->name('configuracion.buscar');
    Route::get('configuracion/eliminar/{id}/{listarluego}', 'ConfiguracionController@eliminar')->name('configuracion.eliminar');
    Route::resource('configuracion', 'ConfiguracionController', array('except' => array('show')));

    // Se quitaron las rutas de Concepto, Caja, Venta y los reportes de Caja/Catálogo/Detalle/Kardex:
    // apuntaban a controladores que no existen en este proyecto y en Laravel 13 rompen "route:list".

    /* REPORTE COMIDA*/
    Route::get('comidareporte/excelDetalle', 'ComidareporteController@excelDetalle')->name('comidareporte.excelDetalle');
    Route::resource('comidareporte', 'ComidareporteController', array('except' => array('show')));
});
