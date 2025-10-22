<?php
// index.php - Ponto de entrada do sistema MVC

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Router.php';

use Core\Router;

session_start();

$router = new Router();
$router->dispatch();

define('MAX_HAMBURGUERES', 200); 