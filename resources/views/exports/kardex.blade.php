<?php 
use App\Movimiento;
use Illuminate\Support\Facades\DB;
?>
@if (count($lista1)>0)
    @foreach ($lista1 as $value1)
    <table>
        <thead>
        <tr>
            <th colspan="11" style="text-align: center"><b>REPORTE KARDEX DEL {{ $fechaini }} AL {{ $fechafin }}</b></th>
        </tr>
        <tr>
            <th style="width: 20px" ><b>PRODUCTO: {{ $value1->nombre }}</b></th>
        </tr>
        <tr>
            <th style="width: 20px" ><b>SUCURSAL: {{ $nombre_sucursal }}</b></th>
        </tr>
        <tr>
            <th style="width: 15px;text-align: center"><b>FECHA</b></th>
            <th style="width: 15px;text-align: center"><b>NUMERO</b></th>
            <th colspan="3" style="width: 36px;text-align: center"><b>ENTRADAS</b></th>
            <th colspan="3" style="width: 36px;text-align: center"><b>SALIDAS</b></th>
            <th colspan="3" style="width: 36px;text-align: center"><b>SALDO</b></th>
        </tr>
        <tr>
            <th><b></b></th>
            <th><b></b></th>
            <th style="width: 12px"><b>CANT.</b></th>
            <th style="width: 12px"><b>PRECIO</b></th>
            <th style="width: 12px"><b>SUBTOTAL</b></th>
            <th style="width: 12px"><b>CANT.</b></th>
            <th style="width: 12px"><b>PRECIO</b></th>
            <th style="width: 12px"><b>SUBTOTAL</b></th>
            <th style="width: 12px"><b>CANT.</b></th>
            <th style="width: 12px"><b>PRECIO</b></th>
            <th style="width: 12px"><b>SUBTOTAL</b></th>
        </tr>
        </thead>
        <tbody>
        @php
            $dat = Movimiento::join('detallemovimiento','detallemovimiento.movimiento_id','=','movimiento.id')
                        ->join('tipodocumento','tipodocumento.id','=','movimiento.tipodocumento_id')
                        ->where('detallemovimiento.producto_id','=',$value1->id)
                        ->where('movimiento.fecha','<',$fechaini)
                        ->where('movimiento.sucursal_id','=',$sucursal)
                        ->whereNotIn('movimiento.situacion',['A'])
                        ->select(DB::raw('sum(case when tipodocumento.stock=\'S\' then detallemovimiento.cantidad else detallemovimiento.cantidad*(-1) end) as stock'))
                        ->groupBy('detallemovimiento.producto_id')
                        ->first();
            if(!is_null($dat)){
                $inicial = $dat->stock;
            }else{
                $inicial = 0;
            }
            $dat = Movimiento::join('detallemovimiento','detallemovimiento.movimiento_id','=','movimiento.id')
                        ->join('tipodocumento','tipodocumento.id','=','movimiento.tipodocumento_id')
                        ->whereNotIn('movimiento.situacion',['A'])
                        ->where('tipodocumento.id','=','movimiento.tipodocumento_id')
                        ->where('detallemovimiento.producto_id','=',$value1->id)
                        ->where('movimiento.fecha','<',$fechaini)
                        ->where('movimiento.sucursal_id','=',$sucursal)
                        ->select('detallemovimiento.preciocompra')
                        ->orderBy('movimiento.fecha','desc')
                        ->first();
            if(!is_null($dat)){
                $preciocompra = $dat->preciocompra;
            }else{
                $preciocompra = 0;
            }
            $resultado        = Movimiento::join('detallemovimiento','detallemovimiento.movimiento_id','=','movimiento.id')
                                ->join('producto','producto.id','=','detallemovimiento.producto_id')
                                ->join('categoria','producto.categoria_id','=','categoria.id')
                                ->join('tipodocumento','tipodocumento.id','=','movimiento.tipodocumento_id')
                                ->join('marca','producto.marca_id','=','marca.id')
                                ->where('movimiento.sucursal_id','=',$sucursal)
                                ->where('movimiento.fecha','>=',$fechaini)
                                ->where('detallemovimiento.producto_id','=',$value1->id)
                                ->whereNotIn('movimiento.situacion',['A'])
                                ->where('movimiento.fecha','<=',$fechafin);
            $resultado        = $resultado->select('producto.nombre as producto','detallemovimiento.cantidad','categoria.nombre as categoria','marca.nombre as marca','movimiento.fecha','movimiento.numero','tipodocumento.nombre as tipodocumento','tipodocumento.stock','detallemovimiento.preciocompra')
                                ->orderBy('movimiento.fecha','asc')
                                ->orderBy('movimiento.numero','asc');
            $lista = $resultado->get();
        @endphp
        <tr>
            <td></td>
            <td>SALDO INICIAL</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>{{ $inicial }}</td>
            <td>{{ $preciocompra }}</td>
            <td>{{ $inicial*$preciocompra }}</td>
        </tr>
        @foreach($lista as $key => $value)
            <tr>
                <td>{{ $value->fecha }}</td>
                <td>{{ $value->numero }}</td>
                @if ($value->stock=="S")
                    <td>{{ $value->cantidad }}</td>
                    <td>{{ $value->preciocompra }}</td>
                    <td>{{ $value->antidad*$value->preciocompra }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    @php
                        $div=($value->cantidad+$inicial);
                        $preciocompra = round(($value->cantidad*$value->preciocompra + $inicial*$preciocompra)/($div==0?1:$div),2);
                        $inicial = $inicial + $value->cantidad;
                    @endphp
                @else
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>{{ $value->cantidad }}</td>
                    <td>{{ $value->preciocompra }}</td>
                    <td>{{ $value->antidad*$value->preciocompra }}</td>
                    @php
                        $inicial = $inicial - $value->cantidad;
                        if($preciocompra==0){
                            $preciocompra=$value->preciocompra;
                        }
                    @endphp
                @endif
                <td>{{ $inicial }}</td>
                <td>{{ $preciocompra }}</td>
                <td>{{ $inicial*$preciocompra }}</td>
            </tr>
        @endforeach
        {{-- <tr>
            <td style="background:red"><b>RESUMEN DE CAJA</b></td>
            <td><b>INGRESOS</b></td>
            <td><b>{{ $ingreso }}</b></td>
            <td><b>EFECTIVO</b></td>
            <td><b>{{ $efectivo }}</b></td>
            <td><b>VISA</b></td>
            <td><b>{{ $visa }}</b></td>
            <td><b>EGRESOS</b></td>
            <td><b>{{ $egreso }}</b></td>
            <td><b>SALDO</b></td>
            <td><b>{{ number_format($ingreso - $egreso,2,'.','') }}</b></td>
        </tr> --}}
        </tbody>
    </table>
@endforeach
@else
    <table>
        <tr>
            <td>
                SIN RESULTADOS
            </td>
        </tr>
    </table>
@endif


