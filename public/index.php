<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// Define o tempo de início do Laravel
define('LARAVEL_START', microtime(true));

// Determina se a aplicação está em modo de manutenção...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Registra o autoloader do Composer
require __DIR__.'/../vendor/autoload.php';


// Bootstrap Laravel e processa a requisição
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

// Processa a requisição
$app->handleRequest(Request::capture());
