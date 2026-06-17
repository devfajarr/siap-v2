<?php

use App\Services\FeederTokenService;
use Illuminate\Contracts\Console\Kernel;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$serv = app(FeederTokenService::class);

echo "=== Total Wilayah in Neo Feeder ===\n";
$resAll = $serv->feederRequest('GetWilayah', ['limit' => 1]);
if ($resAll['success']) {
    echo 'Jumlah Semua Wilayah: '.($resAll['data']['jumlah'] ?? 'N/A')."\n";
}

echo "\n=== Querying dynamic filters ===\n";
// Let's check filter with single quotes for level 3
$resQuotes = $serv->feederRequest('GetWilayah', [
    'filter' => "id_level_wilayah = '3'",
    'limit' => 5,
]);
if ($resQuotes['success']) {
    echo 'Jumlah level 3 dengan quotes: '.($resQuotes['data']['jumlah'] ?? 'N/A')."\n";
}

// Let's pull some records of GetWilayah to see what level values actually look like
$resList = $serv->feederRequest('GetWilayah', ['limit' => 10]);
if ($resList['success'] && ! empty($resList['data']['data'])) {
    foreach ($resList['data']['data'] as $w) {
        echo 'Nama: '.$w['nama_wilayah'].' - Level: '.$w['id_level_wilayah'].' - ID: '.$w['id_wilayah']."\n";
    }
}
