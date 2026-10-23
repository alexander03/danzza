<?php

namespace App\Imports;

use App\Migracion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Librerias\Libreria;

class MigracionImport implements ToCollection
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
            $i = 1;

            if($row[$i]!=""){
                //$fecha = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[$i]);
                $aux = explode("/",$row[$i]);
                $fecha = $aux[2]."-".$aux[1]."-".$aux[0];
            }else{
                $fecha = null;
            }
            $i++;

            $trabajador = strtoupper(trim($row[$i]));
            $i++;

            $dia = strtoupper(trim($row[$i]));
            $i++;

            $empresa = strtoupper(trim($row[$i]));
            $i++;

            $centro = strtoupper(trim($row[$i]));
            $i++;

            $codigocentro = strtoupper(trim($row[$i]));
            $i++;

            $tipodocumento = strtoupper(trim($row[$i]));
            $i++;

            $numerodocumento = strtoupper(trim($row[$i]));
            $i++;

            if($row[$i]!=""){
                //$fechanacimiento = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[$i]);
                $fechanacimiento = $row[$i];
            }else{
                $fechanacimiento = null;
            }
            $i++;

            $correo = strtoupper(trim($row[$i]));
            $i++;

            $cargo = strtoupper(trim($row[$i]));
            $i++;

            $estado = strtoupper(trim($row[$i]));
            $i++;

            $turno = strtoupper(trim($row[$i]));
            $i++;

            $horaingreso = strtoupper(trim($row[$i]));
            $i++;

            $horasalida = strtoupper(trim($row[$i]));
            $i++;

            $tiempoalmuerzo = strtoupper(trim($row[$i]));
            $i++;

            $tiempojornada = strtoupper(trim($row[$i]));
            $i++;

            $estadodia = strtoupper(trim($row[$i]));
            $i++;

            $asistencia = strtoupper(trim($row[$i]));
            $i++;

            $marcaingreso = substr(strtoupper(trim($row[$i])),0,8);
            $i++;

            $marcaingresoalmuerzo = strtoupper(trim($row[$i]));
            $i++;

            $marcasalidaalmuerzo = strtoupper(trim($row[$i]));
            $i++;

            $marcasalida = strtoupper(trim($row[$i]));
            $i++;

            $lugarmarcaingreso = strtoupper(trim($row[$i]));
            $i++;

            $lugarmarcaingresoalmuerzo = strtoupper(trim($row[$i]));
            $i++;

            $lugarmarcasalidaalmuerzo = strtoupper(trim($row[$i]));
            $i++;

            $lugarmarcasalida = strtoupper(trim($row[$i]));
            $i++;

            $horasextra = strtoupper(trim($row[$i]));
            $i++; 

            $horastrabajadas1 = strtoupper(trim($row[$i]));
            $i++;

            $horastrabajadas2 = strtoupper(trim($row[$i]));
            $i++;

            $tiempoalmuerzoreal = strtoupper(trim($row[$i]));
            $i++;

            $tiempoadicionalentrada = strtoupper(trim($row[$i]));
            $i++;

            $tiempoadicionalsalida = strtoupper(trim($row[$i]));
            $i++;

            $tiempoadicionalalmuerzo = strtoupper(trim($row[$i]));
            $i++;

            $excesojornada = strtoupper(trim($row[$i]));
            $i++;

            $justificacion = strtoupper(trim($row[$i]));
            $i++;

            $observaciones = strtoupper(trim($row[$i]));
            $i++;

            $comentarios = strtoupper(trim($row[$i]));
            $i++;

            $anomalias = strtoupper(trim($row[$i]));
            $i++;

            $obj = new Migracion();
            $obj->fecha = $fecha;
            $obj->trabajador = $trabajador;
            $obj->dia = $dia;
            $obj->empresa = $empresa;
            $obj->centro = $centro;
            $obj->codigocentro = $codigocentro;
            $obj->tipodocumento = $tipodocumento;
            $obj->nrodocumento = $numerodocumento;
            $obj->fechanacimiento = $fechanacimiento;
            $obj->correo = $correo;
            $obj->cargo = $cargo;
            $obj->estadocontrato = $estado;
            $obj->turno = $turno;
            $obj->horaingreso = $horaingreso;
            $obj->horasalida = $horasalida;
            $obj->tiempoalmuerzo = $tiempoalmuerzo;
            $obj->tiempojornada = $tiempojornada;
            $obj->estadodia = $estadodia;
            $obj->asistencia = $asistencia;
            $obj->marcaingreso = $marcaingreso;
            $obj->marcaingresoalmuerzo = $marcaingresoalmuerzo;
            $obj->marcasalidaalmuerzo = $marcasalidaalmuerzo;
            $obj->marcasalida = $marcasalida;
            $obj->lugarmarcaingreso = $lugarmarcaingreso;
            $obj->lugarmarcaingresoalmuerzo = $lugarmarcaingresoalmuerzo;
            $obj->lugarmarcasalidaalmuerzo = $lugarmarcasalidaalmuerzo;
            $obj->lugarmarcasalida = $lugarmarcasalida;
            $obj->horasextra = $horasextra;
            $obj->horastrabajada1 = $horastrabajadas1;
            $obj->horastrabajada2 = $horastrabajadas2;
            $obj->tiempoalmuerzoreal = $tiempoalmuerzoreal;
            $obj->tiempoadicionalentrada = $tiempoadicionalentrada;
            $obj->tiempoadicionalsalida = $tiempoadicionalsalida;
            $obj->tiempoadicionalalmuerzo = $tiempoadicionalalmuerzo;
            $obj->excesojornada = $excesojornada;
            $obj->justificacion = $justificacion;
            $obj->observaciones = $observaciones;
            $obj->comentario = $comentarios;
            $obj->anomalias = $anomalias;
            $obj->save();
        }
        $x=$x+1;
        }
    }
}
