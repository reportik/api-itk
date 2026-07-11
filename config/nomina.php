<?php

return [
    /*
    | Ruta absoluta en disco donde api-itk LEE los PDF subidos por Muliix.
    | Debe ser una carpeta compartida/montada entre servidores (NO URL pública).
    | Ej: /mnt/muliix-payrolls  o  \\192.168.0.19\payrolls
    */
    'storage_absolute_path' => env('NOMINA_STORAGE_ABSOLUTE_PATH', storage_path('app/payrolls')),

    'folio_format' => env('NOMINA_FOLIO_FORMAT', '00000'),
];
