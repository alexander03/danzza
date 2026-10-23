<?php

namespace App\Exports;

use App\Comida;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ComidaExport implements FromView
{
	protected $fechaini;
	protected $fechafin;
    protected $modalidad;

    public function __construct($fechaini, $fechafin,$modalidad)
    {
        $this->fechaini = $fechaini;
        $this->fechafin = $fechafin;
        $this->modalidad = $modalidad;
    }
    
    public function view(): View
    {
    	$resultado        = Comida::join('person','person.id','=','comida.persona_id')
                            ->where('comida.fecha','>=',$this->fechaini." 00:00:00")
                            ->where('comida.fecha','<=',$this->fechafin." 23:59:59");
        if($this->modalidad!="0"){
            $resultado = $resultado->where('person.modalidad_id','=',$this->modalidad);
        }
        $lista1            = $resultado->get();
        $fechainicio = $this->fechaini;
        $fechafin = $this->fechafin;
        return view('exports.comida')->with(compact('lista1','fechainicio','fechafin'));
    }

   
}
