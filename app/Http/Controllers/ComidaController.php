<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Person;
use App\Dieta;
use App\Configuracion;
use App\Comida;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade as PDF;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class ComidaController extends Controller
{
    protected $folderview      = 'app.comida';
    protected $tituloAdmin     = 'Comida';
    protected $tituloRegistrar = 'Registrar comida';
    protected $tituloModificar = 'Modificar comida';
    protected $tituloEliminar  = 'Eliminar comida';
    protected $rutas           = array('create' => 'comida.create', 
            'edit'     => 'comida.edit', 
            'delete'   => 'comida.eliminar',
            'search'   => 'comida.buscar',
            'index'    => 'comida.index',
            'show'     => 'comida.show',
        );

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        define("DESAYUNO_INICIO", trim(Configuracion::where("nombre", "=", "DESAYUNO INICIO")->first()->valor));
        define("DESAYUNO_FIN", trim(Configuracion::where("nombre", "=", "DESAYUNO FIN")->first()->valor));
        define("ALMUERZO_INICIO", trim(Configuracion::where("nombre", "=", "ALMUERZO INICIO")->first()->valor));
        define("ALMUERZO_FIN", trim(Configuracion::where("nombre", "=", "ALMUERZO FIN")->first()->valor));
        define("CENA_INICIO", trim(Configuracion::where("nombre", "=", "CENA INICIO")->first()->valor));
        define("CENA_FIN", trim(Configuracion::where("nombre", "=", "CENA FIN")->first()->valor));
    }

    /**
     * Mostrar el resultado de búsquedas
     * 
     * @return Response 
     */
    public function buscar(Request $request)
    {
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'Modalidad';
        $name             = Libreria::getParam($request->input('name'));
        $resultado        = Modalidad::listar($name);
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Descripcion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '3');
        
        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
        $ruta             = $this->rutas;
        if (count($lista) > 0) {
            $clsLibreria     = new Libreria();
            $paramPaginacion = $clsLibreria->generarPaginacion($lista, $pagina, $filas, $entidad);
            $paginacion      = $paramPaginacion['cadenapaginacion'];
            $inicio          = $paramPaginacion['inicio'];
            $fin             = $paramPaginacion['fin'];
            $paginaactual    = $paramPaginacion['nuevapagina'];
            $lista           = $resultado->paginate($filas);
            $request->replace(array('page' => $paginaactual));
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta'));
        }
        return view($this->folderview.'.list')->with(compact('lista', 'entidad'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $entidad          = 'Comida';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar       = Libreria::getParam($request->input('listar'), 'NO');
        $entidad      = 'Modalidad';
        $modalidad  = null;
        $formData     = array('modalidad.store');
        $formData     = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton        = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('modalidad', 'formData', 'entidad', 'boton', 'listar'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $listar     = Libreria::getParam($request->input('listar'), 'NO');
        $validacion = Validator::make($request->all(),
            array(
                'descripcion' => 'required|max:60'
                )
            );
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request){
            $modalidad       = new Modalidad();
            $modalidad->descripcion = $request->input('descripcion');
            $modalidad->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $listar       = Libreria::getParam($request->input('listar'), 'NO');
        $dni = $request->input('name');
        $otros = $request->input('otros');
        if($dni!=""){
            $person = Person::where('dni','=',$request->input('name'))->first();
        }else{
            $person = Person::where('ruc','=',$request->input('otros'))->first();
            $dni = $otros;
        }
        $comida  = null;
        if(is_null($person)){
            $mensaje = "DNI no está registrado en la base de datos";
            $persona = '';
            $tipo = '';
            $fecha = '';
            $color = "warning";
            $esdieta = "NO";
        }else{
            $persona = $person->apellidopaterno.' '.$person->apellidomaterno.' '.$person->nombres;
            $now = strtotime('now');
            $fecha = date("d/m/Y H:i:s");
            $fecha2 = date("Y-m-d H:i:s");
            $desayunoinicio = strtotime(date("Y-m-d ").DESAYUNO_INICIO.":00");
            $desayunofin = strtotime(date("Y-m-d ").DESAYUNO_FIN.":00");
            $almuerzoinicio = strtotime(date("Y-m-d ").ALMUERZO_INICIO.":00");
            $almuerzofin = strtotime(date("Y-m-d ").ALMUERZO_FIN.":00");
            $cenainicio = strtotime(date("Y-m-d ").CENA_INICIO.":00");
            $cenafin = strtotime(date("Y-m-d ").CENA_FIN.":00");
            $color = "info";
            if($now>=$desayunoinicio && $now<=$desayunofin){
                $tipo="DESAYUNO";
            }elseif($now>=$almuerzoinicio && $now<=$almuerzofin){
                $tipo="ALMUERZO";
            }elseif($now>=$cenainicio && $now<=$cenafin){
                $tipo="CENA";
            }else{
                $tipo='-';
                $mensaje='NO EXISTE COMIDA PARA ESTA HORA';
            }
            if($person->sexo=="S"){
                $esdieta="SI";
            }else{
                $dieta = Dieta::where('persona_id','=',$person->id)->where('fechainicio','<=',date("Y-m-d"))->where('fechafin','>=',date("Y-m-d"))->first();
                if(!is_null($dieta)){
                    $esdieta="SI";
                }else{
                    $esdieta="NO";
                }
            }
            if($tipo!="-"){
                $comida = Comida::where(DB::raw('cast(fecha as date)'),'=',date("Y-m-d"))->where('persona_id','=',$person->id)->where('tipo','like',$tipo)->first();
                if(is_null($comida)){
                    $comida = new Comida();
                    $comida->persona_id = $person->id;
                    $comida->fecha = $fecha2;
                    $comida->tipo = $tipo;
                    $comida->dieta = $esdieta;
                    $comida->save();

                    if($esdieta=="SI" && $person->sexo=="S"){
                        $tipo.= " DIETA PERMANENTE";
                    }elseif($esdieta=="SI"){
                        $tipo.=" DIETA TEMPORAL";
                    }
                    $mensaje = "ENTREGAR ".$tipo;
                    $color = "success";
                }else{
                    $mensaje = "YA SE ENTREGO ".$tipo;
                    $color = "danger";
                }
            }
        }

        $entidad      = 'Comida';
        $formData     = array('comida.show');
        $formData     = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton        = 'Registrar'; 
        return view($this->folderview.'.mantView')->with(compact('comida', 'formData', 'entidad', 'boton', 'listar','persona','tipo','fecha','mensaje','color','esdieta','dni'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request)
    {
        $existe = Libreria::verificarExistencia($id, 'modalidad');
        if ($existe !== true) {
            return $existe;
        }
        $listar       = Libreria::getParam($request->input('listar'), 'NO');
        $modalidad  = Modalidad::find($id);
        $entidad      = 'Modalidad';
        $formData     = array('modalidad.update', $id);
        $formData     = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton        = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('modalidad', 'formData', 'entidad', 'boton', 'listar'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $existe = Libreria::verificarExistencia($id, 'modalidad');
        if ($existe !== true) {
            return $existe;
        }
        $validacion = Validator::make($request->all(),
            array(
                'descripcion' => 'required|max:60'
                )
            );
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        } 
        $error = DB::transaction(function() use($request, $id){
            $modalidad       = Modalidad::find($id);
            $modalidad->descripcion = $request->input('descripcion');
            $modalidad->save();

        });
        return is_null($error) ? "OK" : $error;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $existe = Libreria::verificarExistencia($id, 'modalidad');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $modalidad = Modalidad::find($id);
            $modalidad->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    /**
     * Función para confirmar la eliminación de un registrlo
     * @param  integer $id          id del registro a intentar eliminar
     * @param  string $listarLuego consultar si luego de eliminar se listará
     * @return html              se retorna html, con la ventana de confirmar eliminar
     */
    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'modalidad');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Modalidad::find($id);
        $entidad  = 'Modalidad';
        $formData = array('route' => array('modalidad.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function pdfTicket($id){
        $comida = Comida::where('id', $id)->first();
        $pdf = PDF::loadView('app.comida.verpdf',compact('comida'))->setPaper(array(0,0,220,200));  
        return $pdf->stream('ticket.pdf');
    
    }

    public function imprimirComida(Request $request){
        $comida = Comida::find($request->input('id'));
        $connector = new WindowsPrintConnector("CAJA");
        $printer = new Printer($connector);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        //$printer -> bitImage($tux,Printer::IMG_DOUBLE_WIDTH | Printer::IMG_DOUBLE_HEIGHT);
        $printer->text("MARAKOS");
        $printer->feed();
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("FECHA: ".date("d/m/Y H:i:s",strtotime($comida->fecha)));
        $printer->feed();
        $printer->text("DNI: ".$comida->persona->dni);
        $printer->feed();
        $printer->text("NOMBRE: ".$comida->persona->apellidopaterno." ".$comida->persona->apellidomaterno." ".$comida->persona->nombres);
        $printer->feed();
        $printer->text("TIPO: ".$comida->tipo.($comida->dieta=="SI"?" DIETA ":"").($comida->dieta=="SI"?($comida->persona->sexo=="S"?"PERMANENTE":"TEMPORAL"):""));
        $printer->feed();
        $printer->cut();
        $printer->pulse();

        /* Close printer */
        $printer->close();
    }

}
