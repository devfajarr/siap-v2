<?php

use App\Models\Dosen;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo 'Total: '.Dosen::count()."\n";
echo 'Active: '.Dosen::where('status', 1)->count()."\n";
echo 'Active & PA: '.Dosen::where('pembimbing_akademik', 1)->where('status', 1)->count()."\n";
echo 'Active & non-PA: '.Dosen::where('pembimbing_akademik', 0)->where('status', 1)->count()."\n";
