<table>
    <tr>
        @foreach ($encabezados as $encabezado) 
            <th> {{$encabezado}} </th>
        @endforeach
    </tr>
    @foreach ($filas as $fila) 
        <tr>
        @foreach ($fila as $dato) 
            <td>{{$dato}}</td>';
        @endforeach
        </tr>
    @endforeach
</table>