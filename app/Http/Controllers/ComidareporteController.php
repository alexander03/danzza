<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Dieta;
use App\Person;
use App\Comida;
use App\Modalidad;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Jenssegers\Date\Date;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Support\Facades\Auth;
use Excel;
use App\Exports\ComidaExport;
use App\Sucursal;

class ComidareporteController extends Controller
{
    protected $folderview      = 'app.comidareporte';
    protected $tituloAdmin     = 'Comida Reporte';
    protected $tituloRegistrar = 'Reporte de Comida';
    protected $tituloModificar = 'Modificar Caja';
    protected $tituloEliminar  = 'Eliminar Caja';
    protected $rutas           = array('create' => 'comidareporte.create', 
            'index'  => 'comidareporte.index',
        );

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $entidad          = 'Comidareporte';
        $title            = $this->tituloAdmin;
        $ruta             = $this->rutas;
        $sucursales = Sucursal::all();
        $cboModalidad = array('0' => 'TODOS');
        $modalidad = Modalidad::orderBy('descripcion', 'asc')->get();
        foreach ($modalidad as $k => $v) {
            $cboModalidad = $cboModalidad + array($v->id => $v->descripcion);
        }
        $user = Auth::user();
        return view($this->folderview.'.admin')->with(compact('sucursales','entidad', 'title', 'ruta', 'user','cboModalidad'));
    }


    public function show($id)
    {
        //
    }


    public function excelDetalle(Request $request){
        setlocale(LC_TIME, 'spanish');
        return Excel::download(new ComidaExport($request->input('fechainicio'),$request->input('fechafin'),$request->input('modalidad')), 'ReporteComida.xlsx');
    }

}