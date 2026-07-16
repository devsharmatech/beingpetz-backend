<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$tables = Illuminate\Support\Facades\DB::select('SHOW TABLES');
foreach($tables as $table) {
  $prop = array_keys(get_object_vars($table))[0];
  if (strpos($table->$prop, 'provider') !== false) {
    echo $table->$prop . "\n";
  }
}
