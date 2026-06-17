<?php

use App\Services\FeederTokenService;
use Illuminate\Contracts\Console\Kernel;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$serv = app(FeederTokenService::class);

echo "=== Testing GetAgama ===\n";
$resAgama = $serv->feederRequest('GetAgama', ['limit' => 10]);
if ($resAgama['success'] && ! empty($resAgama['data']['data'])) {
    echo 'Found '.count($resAgama['data']['data'])." religions.\n";
    print_r($resAgama['data']['data'][0]); // Print first record structure
} else {
    echo 'Failed GetAgama: '.($resAgama['message'] ?? 'Unknown error')."\n";
}

echo "\n=== Testing GetWilayah ===\n";
// Let's filter for Kecamatan Purworejo or similar to see structure
$resWilayah = $serv->feederRequest('GetWilayah', [
    'filter' => "nama_wilayah like '%Purworejo%'",
    'limit' => 5,
]);
if ($resWilayah['success'] && ! empty($resWilayah['data']['data'])) {
    echo 'Found '.count($resWilayah['data']['data'])." regions.\n";
    print_r($resWilayah['data']['data']);
} else {
    echo 'Failed GetWilayah: '.($resWilayah['message'] ?? 'Unknown error')."\n";
}
