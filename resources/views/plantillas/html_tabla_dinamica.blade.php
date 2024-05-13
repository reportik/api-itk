<table>
    <tr>
        @foreach ($encabezados as $encabezado)
        @if (strpos($encabezado, 'rpt_') !== 0)
        <th> {{$encabezado}} </th>
        @endif
        @endforeach
    </tr>
    @foreach ($filas as $fila)
    <tr>
        @foreach ($fila as $clave => $dato)
        @if (strpos($clave, 'rpt_') !== 0)
        <td>{{$dato}}</td>
        @endif
        @endforeach
    </tr>
    @endforeach
</table>