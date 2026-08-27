<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$user = App\Models\User::where('username', 'ray')->first();
if ($user) {
    echo $user->password, PHP_EOL;
    echo Illuminate\Support\Facades\Hash::check('password', $user->password) ? 'MATCH' : 'NO_MATCH';
} else {
    echo 'NO_USER';
}
