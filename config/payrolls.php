<?php

return [
    'storage_path' => env('PAYROLLS_STORAGE_PATH', storage_path('app/public/payrolls')),
    'url_base' => rtrim(env('PAYROLLS_URL_BASE', env('APP_URL', 'http://localhost') . '/storage/payrolls'), '/'),
    'folio_format' => env('PAYROLLS_FOLIO_FORMAT', '00000'),
    'retention_dates' => (int) env('PAYROLLS_RETENTION_DATES', 9),
];
