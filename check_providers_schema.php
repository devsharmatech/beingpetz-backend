<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$res = Illuminate\Support\Facades\DB::select('SHOW CREATE TABLE providers');
file_put_contents('providers_schema.txt', json_encode($res, JSON_PRETTY_PRINT));
