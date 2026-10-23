<?php
use App\Person;
?>
<!DOCTYPE html>
<html>

<head>
    <title>DOCUMENTO DE DIETA</title>
    <style type="text/css">
        * {
            font-family: Verdana, Arial, sans-serif;
        }

        table {
            font-size: x-small;
        }

        @page {
            margin: 0cm 0cm;
         /*   font-family: Arial; */

        }

        body {
            margin: 0.5cm;

        }

        main table tr td{
            font-family:Georgia, 'Times New Roman', Times, serif; 
        }
        p {
            font-family:Georgia, 'Times New Roman', Times, serif; 
        }

        .gray {
            background-color: lightgray
        }
    </style>
</head>

<body>
    <main>
        @php
            $nombre_doc = $comida->tipo." DIETA";
        @endphp
	    <h3 style=" padding-top:15px; font-family:Georgia, 'Times New Roman', Times, serif;  margin:0px; text-align: center; ">MARAKOS</h3>
        <p style=" margin:5px;  text-align:center; font-size:14px; font-weight:bold;">{{$nombre_doc}}</p>
        <table width='100%' style="  margin-top:10px; border-collapse: collapse;  ">
            <tr style="">
                <td style="  height:2px;  border-bottom : 0.5px black solid ">
                   </td>
            </tr>
            <tr style="">
                <td style="  padding:15px 5px 5px ; font-size:12px; ">
                   <b style="width: 60px !important; display:inline-block;"> FECHA</b><p style="display: inline-block; margin:none;">: {{date('d/m/Y H:i:s',strtotime($comida->fecha))}}</p>
                </td>
            </tr>
            <tr style="">
                <td style="  padding:0px 5px 5px ; font-size:12px; text-align:left;">
                <b style=" width: 60px !important; display:inline-block;">DNI</b><p style="display: inline-block; margin:none;">: {{($comida->persona->dni)?($comida->persona->dni):($comida->persona->ruc)}}</p>
                </td>
            </tr>
            <tr style=" ">
                <td style="  padding:0px 5px 5px; font-size:12px; text-align:left; ">
                    <b style="width: 60px !important; display:inline-block;">NOMBRE</b><p style="display: inline-block; margin:none;">: {{$comida->persona->nombres.' '.$comida->persona->apellidopaterno.' '.$comida->persona->apellidomaterno}}</p>
                </td>
            </tr>
            <tr style=" ">
                <td style="  padding:0px 5px 5px; font-size:12px; text-align:left; ">
                    <b style="width: 60px !important; display:inline-block;">TIPO</b><p style="display: inline-block; margin:none;">: {{$comida->tipo.($comida->dieta=="SI"?" DIETA ":"").($comida->dieta=="SI"?($comida->persona->sexo=="S"?"PERMANENTE":"TEMPORAL"):"")}}</p>
                </td>
            </tr>
        </table>
    </main>

</body>

</html>