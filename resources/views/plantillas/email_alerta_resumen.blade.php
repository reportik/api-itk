<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <style>
        table.paleBlueRows {
            font-family: Arial, Helvetica, sans-serif;
            text-align: center;
            border: 1px solid black;
            width: 94%;
            border-collapse: collapse;
        }

        table.paleBlueRows td,
        table.paleBlueRows th {
            border: 1px solid black;
        }

        table.paleBlueRows tbody td {
            font-size: 12px;
        }

        table.paleBlueRows tr:nth-child(even) {
            background: #D0E4F5;
        }

        table.paleBlueRows thead {
            background: #0B6FA4;
        }

        table.paleBlueRows thead th {
            font-size: 13px;
            font-weight: bold;
            color: #FFFFFF;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h3>Alerta: {{$ale_nombre}}</h3>
                <h3>{{$ale_asunto}} </h3>
                <p>{{ $ale_texto }}</p>
                @include('plantillas.html_tabla_dinamica.blade')
            </div>
        </div> <!-- /.row -->
        <br>
    </div>
</body>

</html>
