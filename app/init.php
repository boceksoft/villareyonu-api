<?php
session_start();
ob_start('ob_gzhandler');
setlocale(LC_ALL, "");
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
date_default_timezone_set("Europe/Istanbul");


$config = require __DIR__ . '/config.php';

try {
    $db = new PDO('sqlsrv:server='.$config['db']['host'].';database='.$config['db']['name'],$config['db']['user'],$config['db']['pass']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch (PDOException $e){
    die($e->getMessage());
}
function loadClasses($className){
    require __DIR__ . '/classes/' .strtolower($className).'.php';
}

spl_autoload_register("loadClasses");

foreach (glob(__DIR__.'/helper/*.php') as $helperFile){
    require $helperFile;
}

//if ($_GET['test'] == "33"){
//    ini_set('display_errors', 1);
//    ini_set('display_startup_errors', 1);
//    error_reporting(E_ALL);
//    function dev_logs()
//    {
//        global $db;
//
//        $actual_link = URL . $_SERVER['REQUEST_URI'];
//        $routeExplode = explode('?', $_SERVER['REQUEST_URI']);
//        $route = array_filter(explode('/', $routeExplode[0]));
//        $route = array_values($route);
//
//        $request_endpoint = $actual_link;
//        $request_type = $_SERVER['REQUEST_METHOD'];
//        $request_headers = json_encode(getallheaders(), JSON_UNESCAPED_UNICODE);
//
//        $sql = "INSERT INTO villareyonu.dev_request_logs (request_endpoint, request_type, request_headers, created_date) VALUES (:request_endpoint, :request_type, :request_headers, :created_date)";
//        $params = [
//            "request_endpoint" => $request_endpoint,
//            "request_type" => $request_type,
//            "request_headers" => $request_headers,
//            "created_date" => date("Y-m-d H:i:s")
//        ];
//
//        $stmt = $db->prepare($sql);
//        $stmt->execute($params);
//    }
//
//
//    $headers = array_change_key_case(getallheaders(), CASE_LOWER);
//    $language = !empty($headers['language']) ? $headers['language'] : 'tr';
//
//    if ($language == "en"){
//        dev_logs();
//    }
//
//}



$sql = "select * from genel".UZANTI;
$query = $db->prepare($sql);
$query->execute();
$qsql = $query->fetch(PDO::FETCH_ASSOC);
$qsql["domain"]=str_replace("web.","www.",$qsql["domain"]);
$qsql['adres']=strip_tags($qsql["adres"]);
$config['smtp_host']=$qsql["smtp"];
$config['smtp_secure']=$qsql["smtp_secure"];
$config['smtp_username']=$qsql["sitemail"];
$config['smtp_password']=$qsql["sitemailsifre"];
$config['smtp_port']=$qsql["port"];
$config['smtp_sendFrom']=$qsql["siteadi"];
$config['EstablishmentId']=$qsql["establishmentId"];
$config['apiKey']=$qsql["apiKey"];
$config['BaseUrl'] = $qsql["domain"];
if(1==2){
    $config['smtp_host']="smtp.boceksoft.com";
    $config['smtp_username']="tolga@boceksoft.com";
    $config['smtp_password']="tolga190";
}
$Language = new Language();
$Language = $Language->lang;