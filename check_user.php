<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$email = 'devsharma10.developer@gmail.com';
$user = User::where('email', $email)->first();

echo "USER_CHECK_START\n";
if ($user) {
    echo "ID: " . $user->id . "\n";
    echo "IS_COMPLETE: " . (int)$user->isComplete . "\n";
    echo "DELETED_AT: " . (int)$user->deleted_at . "\n";
} else {
    echo "NOT_FOUND\n";
}
echo "USER_CHECK_END\n";
