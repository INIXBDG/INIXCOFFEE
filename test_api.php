<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $request = new \Illuminate\Http\Request();
    
    $controller = new \App\Http\Controllers\JurnalAkuntansiController();
    $response = $controller->getData($request);
    
    $content = $response->getContent();
    if (json_decode($content) === null) {
        echo "JSON Encode failed: " . json_last_error_msg() . "\n";
    } else {
        echo "JSON is valid! Length: " . strlen($content) . "\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
