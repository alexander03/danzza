<?php

namespace App\Imports;

use App\Person;
use App\Rolpersona;
use App\Modalidad;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Librerias\Libreria;

class PersonaImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows): void
    {
    $x=0;
        foreach ($rows as $row) 
        {
        if($x>0 && trim($row[0])!=""){
            $i = 0;

            $dni = substr(strtoupper(trim($row[$i])),0,8);
            $i++;

            $data = strtoupper(trim($row[$i]));
            $nombres = explode(",",$data);
            $apellidos = explode(" ",$nombres[0]);
            $i++;

            $modalidad = Modalidad::where(DB::raw("upper(descripcion)"), "=",strtoupper(trim($row[$i])))->first();
            if($modalidad == null){
                $modalidad = new Modalidad();
                $modalidad->descripcion = trim($row[$i]);
                $modalidad->save();
            }
            $i++;
            
            $autorizado = strtoupper(trim($row[$i]));
            $i++;
            
            $solicitado = strtoupper(trim($row[$i]));
            $i++;

            $dscto = strtoupper(trim($row[$i]));
            $i++;

            $rest = strtoupper(trim($row[$i]));
            $i++;

            $celular = strtoupper(trim($row[$i]));
            $i++;

            $anotaciones = strtoupper(trim($row[$i]));
            $i++;

	        $persona = Person::where('dni','like',$dni)->first();
            $band=false;
            if(is_null($persona)){
                $persona = new Person();
                $band=true;
	        }
            $persona->dni = $dni;
            $persona->apellidopaterno = strtoupper(trim($apellidos[0]));
            if(isset($apellidos[1])){
                $persona->apellidomaterno = strtoupper(trim($apellidos[1]));
            }else{
                $persona->apellidopaterno = '';
            }
            if(isset($nombres[1])){
                $persona->nombres = strtoupper(trim($nombres[1]));
            }else{
                $persona->nombres = "";
            }
            $persona->ruc = strtoupper('-');
            $persona->direccion = strtoupper('-');
            $persona->email = strtoupper('-');
            $persona->telefono = strtoupper($celular);
            $persona->modalidad_id = strtoupper($modalidad->id);
            $persona->autorizado = strtoupper($autorizado);
            $persona->solicitado = strtoupper($solicitado);
            $persona->dscto = strtoupper($dscto);
            $persona->anotaciones = strtoupper($anotaciones);
            $persona->save();
            if($band){
                $rolpersona = new Rolpersona();
                $rolpersona->person_id = $persona->id;
                $rolpersona->rol_id = 1;
                $rolpersona->save();
            }
        }
        $x=$x+1;
        }
    }
}
