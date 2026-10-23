<?php 
use App\Comida;
use Illuminate\Support\Facades\DB;
?>
@if (count($lista1)>0)
    <table>
        <thead>
        <tr>
            <th colspan="11" style="text-align: center"><b>REPORTE DE ATENCIONES DEL {{ date("d/m/Y",strtotime($fechainicio)) }} AL {{ date("d/m/Y",strtotime($fechafin)) }}</b></th>
        </tr>
        <tr>
            <th style="width: 15px"><b>SERVICIO</b></th>
            <th style="width: 25px"><b>DIETA</b></th>
            <th style="width: 10px"><b>DNI / OTROS</b></th>
            <th style="width: 12px"><b>FECHA</b></th>
            <th style="width: 10px"><b>HORA</b></th>
            <th style="width: 60px"><b>PERSONA</b></th>
            <th style="width: 30px"><b>MODALIDAD</b></th>
            <th style="width: 50px"><b>AUTORIZADO POR</b></th>
            <th style="width: 50px"><b>SOLICITADO POR</b></th>
            <th style="width: 10px"><b>DSCTO</b></th>
            <th style="width: 10px"><b>REST</b></th>
            <th style="width: 80px"><b>ANOTACIONES</b></th>
        </tr>
        </thead>
        <tbody>
        @foreach($lista1 as $key => $value)
            <tr>
                <td>{{ $value->tipo }}</td>
                <td>{{ ($value->dieta=="SI"?"DIETA ":"NO").($value->dieta=="SI"?($value->persona->sexo=="S"?"PERMANENTE":"TEMPORAL"):"") }}</td>
                @if($value->dni=="")
                    <td>{{ $value->persona->ruc }}</td>
                @else
                    <td>{{ $value->persona->dni }}</td>
                @endif
                <td>{{ date("d/m/Y",strtotime($value->fecha)) }}</td>
                <td>{{ date("H:i:s",strtotime($value->fecha)) }}</td>
                <td>{{ $value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres }}</td>
                <td>{{ $value->persona->modalidad->descripcion }}</td>
                <td>{{ $value->persona->autorizado }}</td>
                <td>{{ $value->persona->solicitado }}</td>
                <td>{{ $value->persona->dscto }}</td>
                <td>{{ $value->persona->rest }}</td>
                <td>{{ $value->persona->anotaciones }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <table>
        <tr>
            <td>
                SIN RESULTADOS
            </td>
        </tr>
    </table>
@endif


