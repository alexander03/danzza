<?php

namespace App\Imports;

use App\Movimiento;
use App\Producto;
use App\Detallemovimiento;
use App\Detalleproducto;
use App\Stockproducto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Librerias\Libreria;
use Illuminate\Support\Facades\Auth;


class DocAlmacenImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows): void {
    	date_default_timezone_set("America/Lima");
    	$x=0;
		$user        = Auth::User();
        $Venta       = new Movimiento();
        $Venta->fecha = date("Y-m-d");
        $Venta->numero = "001-".Movimiento::NumeroSigue(3, 8);
        $Venta->subtotal = 0;
        $Venta->igv = 0;
        $Venta->total = 0;
        $Venta->tipomovimiento_id = 3; //MOV ALMACEN
        $Venta->tipodocumento_id = 8;
        $Venta->persona_id = 1;
        $Venta->situacion = 'C'; //Pendiente => P / Cobrado => C / Boleteado => B
        $Venta->comentario = "";
        $Venta->responsable_id = $user->person_id;
        $Venta->voucher = '';
        $Venta->totalpagado = 0;
        $Venta->tarjeta = '';
        $Venta->sucursal_id = session("import_docalmacen_sucursal_id");
        $Venta->sucursal_envio_id = null;
        $Venta->motivo_id = 2;
        $Venta->save();
        $total = 0;
        foreach ($rows as $row) {
	        if($x>0 && trim($row[0])!=""){
                
            	$i = 0;
                $Detalle = new Detallemovimiento();
                $Detalle->movimiento_id = $Venta->id;
                $producto = Producto::where("codigobarra","=",$row[$i])->first();$i++;
                if($producto == null){
                    throw new \Exception(json_encode("EL PRODUCTO NO EXISTE"));
                }
                $Detalle->producto_id = $producto->id;
                $Detalle->cantidad = $row[$i];$i++;
                $Detalle->precioventa = $producto->precioventa;
                $Detalle->preciocompra = $row[$i];$i++;
                $Detalle->save();
                $total = $total + $Detalle->preciocompra*$Detalle->cantidad;
                $Producto = Producto::find($Detalle->producto_id);
                $Producto->preciocompra = $Detalle->preciocompra;
                $Producto->save();
                $detalleproducto = Detalleproducto::where('producto_id', '=', $Detalle->producto_id)->get();
                if (count($detalleproducto) > 0) {
                    foreach ($detalleproducto as $key => $value) {
                        $stock = Stockproducto::where('producto_id', '=', $value->presentacion_id)->where("sucursal_id", "=", $Venta->sucursal_id)->first();
                        if ($stock != null) {
                            if ($Venta->tipodocumento_id == 8) { //INGRESO
                                $stock->cantidad = $stock->cantidad + $Detalle->cantidad * $value->cantidad;
                            } else {
                                if (($stock->cantidad - $Detalle->cantidad * $value->cantidad) < 0 && STOCK_NEGATIVO == "N") {
                                    throw new \Exception(json_encode("ERROR EN EL SISTEMA"));
                                } else {
                                    $stock->cantidad = $stock->cantidad - $Detalle->cantidad * $value->cantidad;
                                }
                            }
                            $stock->save();
                        } else {
                            $stock = new Stockproducto();
                            $stock->sucursal_id = $Venta->sucursal_id;
                            $stock->producto_id = $value->presentacion_id;
                            if ($Venta->tipodocumento_id == 8) { //INGRESO
                                $stock->cantidad = $Detalle->cantidad * $value->cantidad;
                            } else {
                                if (($Detalle->cantidad * (-1) * $value->cantidad) < 0 && STOCK_NEGATIVO == "N") {  
                                    throw new \Exception(json_encode("ERROR EN EL SISTEMA"));
                                } else {
                                    $stock->cantidad = $Detalle->cantidad * (-1) * $value->cantidad;
                                }
                            }
                            $stock->save();
                        }
                    }
                } else {
                    $stock = Stockproducto::where('producto_id', '=', $Detalle->producto_id)->where("sucursal_id", "=", $Venta->sucursal_id)->first();
                    if ($stock != null) {
                        if ($Venta->tipodocumento_id == 8) { //INGRESO
                            $stock->cantidad = $stock->cantidad + $Detalle->cantidad;
                        } else {
                            if (($stock->cantidad - $Detalle->cantidad) < 0 && STOCK_NEGATIVO == "N") {
                                throw new \Exception(json_encode("ERROR EN EL SISTEMA"));
                            } else {
                                $stock->cantidad = $stock->cantidad - $Detalle->cantidad;
                            }
                        }
                        $stock->save();
                    } else {
                        $stock = new Stockproducto();
                        $stock->sucursal_id = $Venta->sucursal_id;
                        $stock->producto_id = $Detalle->producto_id;
                        if ($Venta->tipodocumento_id == 8) { //INGRESO
                            $stock->cantidad = $Detalle->cantidad;
                        } else {
                            if (($Detalle->cantidad * (-1)) < 0 && STOCK_NEGATIVO == "N") {
                                throw new \Exception(json_encode("ERROR EN EL SISTEMA"));
                            } else {
                                $stock->cantidad = $Detalle->cantidad * (-1);
                            }
                        }
                        $stock->save();
                    }
                }
                
		    }
		    $x=$x+1;
        }
        $Venta->subtotal = $total;
        $Venta->total = $total;
        $Venta->save();
    }
}
