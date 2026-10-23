<?php

namespace App\Exports;

use App\Producto;
use App\Sucursal;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;


class CatalogoExport implements FromView
{
    protected $sucursal;
    protected $categoria;
    protected $subcategoria;
    protected $unidad;
    protected $marca;
    protected $precioventa;
    protected $stock;

    public function __construct($sucursal=null, $categoria, $subcategoria, $marca , $unidad ,$precioventa ,$stock)
    {
       
        $this->sucursal = $sucursal;
        $this->categoria = $categoria;
        $this->subcategoria = $subcategoria;
        $this->marca = $marca;
        $this->unidad = $unidad;
        $this->precioventa = $precioventa;
        $this->stock = $stock;
    }

    public function view(): View
    {
        $sucursal_id = $this->sucursal;
        $categoria = $this->categoria;
        $subcategoria = $this->subcategoria;
        $marca = $this->marca;
        $unidad = $this->unidad;
        $stock = $this->stock;
        $precioventa = $this->precioventa;
        $resultado        = Producto::join('marca','marca.id','=','producto.marca_id')
                                ->join('unidad','unidad.id','=','producto.unidad_id')
                                ->join('categoria','categoria.id','=','producto.categoria_id')
                                ->join('category','categoria.categoria_id','=','category.id')
                                ->leftjoin('stockproducto',function($subquery) use ($sucursal_id){
                                    $subquery->whereRaw('stockproducto.producto_id = producto.id')->where("stockproducto.sucursal_id", "=", $sucursal_id);
                                });
         
        $resultado = $resultado->orderBy('producto.nombre','asc')
                            ->select('producto.*','category.nombre as categoria','categoria.nombre as subcategoria','marca.nombre as marca','unidad.nombre as unidad','stockproducto.cantidad as stock');
         $lista1           = $resultado->get();
        return view('exports.catalogo')->with(compact('lista1','categoria','subcategoria','marca','unidad','stock','precioventa'));
    }
}
