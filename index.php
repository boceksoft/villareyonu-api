<?php
header("Content-type: application/json; charset=utf-8");
define('URL', "http://api.villareyonu.com");
require_once 'vendor/autoload.php';
$actual_link = URL . $_SERVER['REQUEST_URI'];
$routeExplode = explode('?', $_SERVER['REQUEST_URI']);
$route = array_filter(explode('/', $routeExplode[0]));
$route = array_values($route);

require __DIR__ . '/app/init.php';

//if ($language === "en")
//    dev_logs();

if (!route(0)) {
    $route[0] = "index";
}

if (!file_exists(controller($route[0]))) {
    $route[0] = "404";
}

// Çıktıyı tamponlamaya başla
//ob_start();
try {
    require controller(route(0));
}catch (Exception $e){
    echo json_encode(array("status" => "error", "error" => $e->getMessage()));
}






?>
