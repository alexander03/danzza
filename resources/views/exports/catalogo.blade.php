@if (count($lista1)>0)
    <table>
        <thead>
        <tr>
            <th colspan="7" style="text-align: center"><b>CATALOGO DE PRODUCTOS</b></th>
        </tr>
        <tr>
            @if($categoria=='S')
            <th style="width: 20px"><b>CATEGORIA</b></th>
            @endif
            @if($subcategoria=='S')
            <th style="width: 20px"><b>SUBCATEGORIA</b></th>

            @endif
            @if($marca=='S')
            <th style="width: 20px"><b>MARCA</b></th>

            @endif
            @if($unidad=='S')
            <th style="width: 20px"><b>UNIDAD</b></th>

            @endif
            <th style="width: 20px"><b>PRODUCTO</b></th>
            @if($precioventa=='S')
            <th style="width: 20px"><b>P. VENTA</b></th>
            @endif
            @if($stock=='S')
            <th style="width: 20px"><b>STOCK</b></th>
            @endif
            
        </tr>
        </thead>
        <tbody>
        
        @foreach($lista1 as $key => $value)
            <tr>
                @if($categoria=='S')
                <td>{{ $value->categoria }}</td>
                @endif
                @if($subcategoria=='S')
                <td>{{ $value->subcategoria }}</td>
                @endif
                @if($marca=='S')
                <td>{{ $value->marca }}</td>
                @endif
                @if($unidad=='S')
                <td>{{ $value->unidad }}</td>
                @endif
                <td>{{$value->nombre}}</td>
                @if($precioventa=='S')
                <td>{{$value->precioventa}}</td>
                @endif
                @if($stock=='S')
                <td>{{ $value->stock }}</td>
                @endif
                
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
