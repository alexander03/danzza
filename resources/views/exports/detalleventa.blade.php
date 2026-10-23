@if (count($lista1)>0)
    <table>
        <thead>
        <tr>
            <th colspan="7" style="text-align: center"><b>REPORTE DETALLE DEL {{ $fechaini }} AL {{ $fechafin }}</b></th>
        </tr>
        <tr>
            <th style="width: 20px"><b>CATEGORIA</b></th>
            <th style="width: 20px"><b>SUBCATEGORIA</b></th>
            <th style="width: 20px"><b>PRODUCTO</b></th>
            <th style="width: 20px"><b>MARCA</b></th>
            <th style="width: 20px"><b>CANTIDAD</b></th>
            <th style="width: 20px"><b>P. VENTA</b></th>
            <th style="width: 20px"><b>SUBTOTAL</b></th>
        </tr>
        </thead>
        <tbody>
        @php
            $total=0;
        @endphp
        @foreach($lista1 as $key => $value)
            <tr>
                <td>{{ $value->categoriapadre }}</td>
                <td>{{ $value->categoria }}</td>
                <td>{{ $value->producto }}</td>
                <td>{{ $value->marca }}</td>
                <td>{{ $value->cantidad }}</td>
                <td>{{ $value->precioventa }}</td>
                <td>{{ $value->cantidad*$value->precioventa }}</td>
                @php
                    $total = $total + $value->cantidad*$value->precioventa;
                @endphp
            </tr>
        @endforeach
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><b>TOTAL</b></td>
            <td><b>{{ $total }}</b></td>
            <td></td>
        </tr>
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


