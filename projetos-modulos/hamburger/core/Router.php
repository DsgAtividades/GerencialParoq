<?php
namespace Core;

class Router {
    public function dispatch() {
        $controller = isset($_GET['c']) ? $_GET['c'] : 'Ingresso';
        $action = isset($_GET['a']) ? $_GET['a'] : 'index';
        $controllerName = ucfirst($controller) . 'Controller';
        $controllerFile = __DIR__ . '/../controllers/' . $controllerName . '.php';
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            if (class_exists($controllerName)) {
                $obj = new $controllerName();
                if (method_exists($obj, $action)) {
                    $obj->$action();
                    return;
                }
            }
        }
        // 404 simples
        echo '<h1>Página não encontrada</h1>';
    }
} 