<?php require 'vendor/autoload.php'; \ = require_once 'bootstrap/app.php'; \->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); print_r(DB::table('theme_settings')->get()->toArray());
error_reporting(E_ALL & ~E_DEPRECATED);
require 'vendor/autoload.php'; 
$app = require_once 'bootstrap/app.php'; 
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); 
print_r(DB::table('theme_settings')->get()->toArray());
