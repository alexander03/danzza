<?php

namespace App\Exports;

use App\Movimiento;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class DetalleVentaExport implements FromView
{
    protected $fechaini;
    protected $fechafin;
    protected $categoria;
    protected $subcategoria;
    protected $producto;
    protected $marca;
    protected $sucursal;

    public function __construct($fechaini, $fechafin, $categoria, $subcategoria, $producto, $marca, $sucursal)
    {
        $this->fechaini = $fechaini;
        $this->fechafin = $fechafin;
        $this->categoria = $categoria;
        $this->subcategoria = $subcategoria;
        $this->producto = $producto;
        $this->marca = $marca;
        $this->sucursal = $sucursal;
    }

    public function view(): View
    {
        $resultado        = Movimiento::where('movimiento.tipomovimiento_id', '=', 2)
            ->join('detallemovimiento', 'detallemovimiento.movimiento_id', '=', 'movimiento.id')
            ->join('producto', 'producto.id', '=', 'detallemovimiento.producto_id')
            ->leftJoin('categoria', 'producto.categoria_id', '=', 'categoria.id')
            ->leftJoin('category', 'categoria.categoria_id', '=', 'category.id')
            ->leftJoin('marca', 'producto.marca_id', '=', 'marca.id')
            ->where('movimiento.sucursal_id', '=', $this->sucursal)
            ->where('movimiento.fecha', '>=', $this->fechaini)
            ->whereNotIn('movimiento.situacion', ['A'])
            ->where('movimiento.fecha', '<=', $this->fechafin);
        if ($this->marca != "") {
            $resultado = $resultado->where('producto.marca_id', '=', $this->marca);
        }
        if ($this->subcategoria != "") {
            $resultado = $resultado->where('producto.categoria_id', '=', $this->subcategoria);
        }
        if ($this->categoria != "") {
            $resultado = $resultado->where('categoria.categoria_id', '=', $this->categoria);
        }
        if ($this->producto != "") {
            $resultado = $resultado->where('producto.id', '=', $this->producto);
        }
        $resultado        = $resultado->select('producto.nombre as producto', DB::raw('sum(detallemovimiento.cantidad) as cantidad'), 'category.nombre as categoriapadre', 'categoria.nombre as categoria', 'marca.nombre as marca', 'detallemovimiento.precioventa')
            ->groupBy('producto.nombre')
            ->groupBy('categoria.nombre')
            ->groupBy('category.nombre')
            ->groupBy('marca.nombre')
            ->groupBy('detallemovimiento.precioventa');
        $lista1            = $resultado->get();
        //echo json_encode($lista1);exit();
        $fechaini           = $this->fechaini;
        $fechafin           = $this->fechafin;
        return view('exports.detalleventa')->with(compact('lista1', 'fechaini', 'fechafin'));
    }
}
