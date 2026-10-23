<?php

namespace App\Exports;

use App\Producto;
use App\Sucursal;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;


class KardexExport implements FromView
{
    protected $fechaini;
    protected $fechafin;
    protected $categoria;
    protected $subcategoria;
    protected $producto;
    protected $sucursal;

    public function __construct($fechaini, $fechafin, $categoria, $subcategoria, $producto, $sucursal)
    {
        $this->fechaini = $fechaini;
        $this->fechafin = $fechafin;
        $this->categoria = $categoria;
        $this->subcategoria = $subcategoria;
        $this->producto = $producto;
        $this->sucursal = $sucursal;
    }

    public function view(): View
    {
        $resultado        = Producto::listar($this->categoria, $this->subcategoria, null, $this->producto);
        $lista1           = $resultado->get();
        $fechaini           = $this->fechaini;
        $fechafin           = $this->fechafin;
        $sucursal           = $this->sucursal;
        $nombre_sucursal    = Sucursal::find($this->sucursal)->nombre;
        return view('exports.kardex')->with(compact('lista1', 'fechaini', 'fechafin', 'sucursal', 'nombre_sucursal'));
    }
}
