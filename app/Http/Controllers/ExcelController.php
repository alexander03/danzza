<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Input;
use Excel;
use App\Curso;
use App\Person;
use App\Profesor;
use App\Matricula;
use App\Alumno;
use App\Seccion;
use App\Grado;
use App\Especialidad;
use App\Anio;
use App\Promocion;
use App\Detallepromocion;
use App\Producto;

use Illuminate\Support\Facades\DB;

class ExcelController extends Controller
{

	public function importHistoria()
    {
		return view('importHistoria');
	}

	public function downloadExcel($type)
	{
		$data = Item::get()->toArray();
		return Excel::create('itsolutionstuff_example', function($excel) use ($data) {
			$excel->sheet('mySheet', function($sheet) use ($data)
	        {
				$sheet->fromArray($data);

	        });
		})->download($type);
	}

	public function importMatricula2()
	{
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 0);
		if(Input::hasFile('import_file')){
			$path = Input::file('import_file')->getRealPath();
			$data = Excel::load($path, function($reader) {

			})->get();
			if(!empty($data) && $data->count()){
			    $dat=array();
				foreach ($data as $key => $value) {
                    $error = DB::transaction(function() use($value,&$dat){
                        $anio = Anio::where('situacion','like','A')->first();
                        $nombres = "";
                        $nom = explode(" ",$value->nombres);
                        for($c=2;$c<count($nom);$c++){
                            $nombres.=$nom[$c]." ";
                        }
                        $alumno = new Alumno();
                        $person = new Person();
                        $person->apellidopaterno = strtoupper($nom[0]);
                        $person->apellidomaterno = strtoupper($nom[1]);
                        $person->nombres = strtoupper(trim($nombres));
                        $person->dni = strtoupper($value->dni);
                        $person->direccion = strtoupper($value->direccion);
                        $person->email = strtoupper($value->correo);
                        $person->telefono = strtoupper($value->telefono);
                        $person->save();
                        $alumno->person_id = $person->id;
                        $alumno->save();
                        
                        $matricula = new Matricula();
                        $matricula->alumno_id = $alumno->id;
                        $matricula->anio_id=$anio->id;
                        $matricula->seccion_id = $seccion->id;
                        $dat[]=array("respuesta"=>"OK","id"=>$matricula->id);            
                    });
                    if(!is_null($error)){
                        print_r($error);die();
                    }
				}
                print_r($dat);
			}
		}
		return view('importHistoria');;

	}

    public function importMatricula()
    {
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 0);
        if(Input::hasFile('import_file')){
            $path = Input::file('import_file')->getRealPath();
            $data = Excel::load($path, function($reader) {

            })->get();
            if(!empty($data) && $data->count()){
                $dat=array();
                foreach ($data as $key => $value) {
                    $error = DB::transaction(function() use($value,&$dat){
                        $anio = Anio::where('situacion','like','A')->first();
                        $grado = Grado::where('nombre','like',trim($value->ciclo))->first();
                        $seccion = Seccion::where('grado_id','=',$grado->id)->where('especialidad_id','=',$value->idespecialidad)->where('anio_id','=',$anio->id)->first();
                        $person = Person::where('dni','like',$value->dni)->first();
                        $nombres = "";
                        $nom = explode(" ",$value->nombres);
                        for($c=2;$c<count($nom);$c++){
                            $nombres.=$nom[$c]." ";
                        }
                        if(is_null($person)){
                            $person = new Person();
                            $person->apellidopaterno = strtoupper($nom[0]);
                            $person->apellidomaterno = strtoupper($nom[1]);
                            $person->nombres = strtoupper(trim($nombres));
                            $person->dni = strtoupper($value->dni);
                            $person->direccion = strtoupper($value->direccion);
                            $person->email = strtoupper($value->correo);
                            $person->telefono = strtoupper($value->telefono);
                            $person->save();
                        }else{
                            $person->apellidopaterno = strtoupper($nom[0]);
                            $person->apellidomaterno = strtoupper($nom[1]);
                            $person->nombres = strtoupper(trim($nombres));
                            $person->dni = strtoupper($value->dni);
                            $person->direccion = strtoupper($value->direccion);
                            $person->email = strtoupper($value->correo);
                            $person->telefono = strtoupper($value->telefono);
                            $person->save();
                        }
                        $alumno = new Alumno();
                        $alumno->person_id = $person->id;
                        $alumno->codigo = trim($value->codigo);
                        $alumno->save();
                        
                        $matricula = new Matricula();
                        $matricula->alumno_id = $alumno->id;
                        $matricula->anio_id = $anio->id;
                        $matricula->seccion_id = $seccion->id;
                        $matricula->save();
                        $dat[]=array("respuesta"=>"NUEVO","id"=>$alumno->id);
                    });
                    if(!is_null($error)){
                        print_r($error);die();
                    }
                }
                print_r($dat);
            }
        }
        return view('importHistoria');;

    }

    public function importPromocion()
    {
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 0);
        if(Input::hasFile('import_file')){
            $path = Input::file('import_file')->getRealPath();
            $data = Excel::load($path, function($reader) {

            })->get();
            if(!empty($data) && $data->count()){
                $dat=array();
                foreach ($data as $key => $value) {
                    $error = DB::transaction(function() use($value,&$dat){
                        for($c=0;$c<$value->count();$c++){
                            $promocion = new Promocion();
                            $promocion->nombre = $value[$c]['nombre'];
                            $promocion->categoria_id = 17;
                            $promocion->unidad_id = 2;
                            $promocion->precioventa = $value[$c]['venta'];
                            $promocion->save();
                            if($value[$c]['producto1']!=""){
                                $producto = Producto::where(DB::raw('trim(nombre)'),'like','%'.trim($value[$c]['producto1']).'%')->first();
                                if(!is_null($producto)){
                                    $detalle = new Detallepromocion();
                                    $detalle->promocion_id=$promocion->id;
                                    $detalle->producto_id=$producto->id;
                                    $detalle->cantidad=$value[$c]['cant1'];
                                    $detalle->save();
                                }
                            }
                            if($value[$c]['producto2']!=""){
                                $producto = Producto::where(DB::raw('trim(nombre)'),'like','%'.trim($value[$c]['producto2']).'%')->first();
                                if(!is_null($producto)){
                                    $detalle1 = new Detallepromocion();
                                    $detalle1->promocion_id=$promocion->id;
                                    
                                    $detalle1->producto_id=$producto->id;
                                    $detalle1->cantidad=$value[$c]['cant2'];
                                    $detalle1->save();
                                }
                            }
                            if($value[$c]['producto3']!=""){
                                $producto = Producto::where(DB::raw('trim(nombre)'),'like','%'.trim($value[$c]['producto3']).'%')->first();
                                if(!is_null($producto)){
                                    $detalle2 = new Detallepromocion();
                                    $detalle2->promocion_id=$promocion->id;
                                    $detalle2->producto_id=$producto->id;
                                    $detalle2->cantidad=$value[$c]['cant3'];
                                    $detalle2->save();
                                }
                            }
                            $dat[]=array("respuesta"=>"NUEVO","descripcion"=>$value[$c]['nombre']);
                        }
                    });
                    if(!is_null($error)){
                        print_r($error);die();
                    }
                }
                print_r($dat);
            }
        }
        return view('importHistoria');;

    }


}