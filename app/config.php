<?php
define('PATH', realpath('.'));
const USER_EXPIRE = 86400;


define("CDN", "https://cdn.villareyonu.com");
define("DOMAIN", "https://www.villareyonu.com");
//define("DOMAIN", "http://localhost:3000");
define("BASEAPI", "https://api.villareyonu.com");
define("PanelUrl","https://www.villareyonu.com/boceksoft-vr-v2");

//if ($_GET['test'] == "33"){

//    ini_set('display_errors', 1);
//    ini_set('display_startup_errors', 1);
//    error_reporting(E_ALL);

    $headers = array_change_key_case(getallheaders(), CASE_LOWER);
    $language = !empty($headers['language']) ? $headers['language'] : (!empty($_GET["language"]) ? $_GET["language"] : 'tr');
    $currency = !empty($headers['currency']) ? $headers['currency'] : (!empty($_GET["currency"]) ? $_GET["currency"] : 'tl');

    $consts = [
        "tr" => [
            "SITE" => 1,
            "UZANTI" => '',
            "DILUZANTI" => '',
            "DILURL" => '/tr',
            "PRICE_SITE" => 1,
            "SQL_LANGUAGE" => 'Turkish',
            "DefaultCurrencyId"=>1
        ],
        "en" => [
            "SITE" => 2,
            "UZANTI" => '_s2',
            "DILUZANTI" => 'en',
            "DILURL" => '/en',
            "PRICE_SITE" => 1,
            "SQL_LANGUAGE" => 'English',
            "DefaultCurrencyId"=>3
        ]
    ];

    if($currency=="tl"){
        define("DefaultCurrencyId",1);
    }else if ($currency=="euro"){
        define("DefaultCurrencyId",3);
    }

    define("SITE", $consts[$language]['SITE']);
    define("UZANTI", $consts[$language]['UZANTI']);
    define("DILUZANTI", $consts[$language]['DILUZANTI']);
    define("DILURL", $consts[$language]['DILURL']);
    define("PRICE_SITE", $consts[$language]['PRICE_SITE']);
    define("CURRENT_LANGUAGE", $language);
    define("CURRENT_CURRENCY", $currency);
    define("SQLLANG","SET LANGUAGE ". $consts[$language]['SQL_LANGUAGE'].";");

    if(SITE == 2){
        setlocale(LC_TIME,"English");
        setlocale (LC_ALL, 'en_US.UTF-8', 'en_US', 'en', 'english');
    }else{
        setlocale(LC_TIME,"Turkish");
        setlocale (LC_ALL, 'tr_TR.UTF-8', 'tr_TR', 'tr', 'turkish');
    }


//}
//else{
//    define("SITE", 1);
//    define("UZANTI", '');
//    define("DILUZANTI", '');
//    define("DILURL", '/tr');
//    define("PRICE_SITE", 1);
//    define("SQLLANG","SET LANGUAGE Turkish;");
//}

return [
    'db' => [
        'name' => 'villareyonu',
        'host' => 'localhost',
        'user' => 'villareyonu',
        'pass' => 'villareyonu*-123'
    ],
    "lang" => "tr",
];

