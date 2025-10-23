<?php
SetHeader(200);
$json = [];
$json["data"][0]=[
    "id"=>"1",
    "profile"=>"995_balayý logo.jpeg",
    "name"=>"Villacım Destek",
    "phone"=>str_replace(" ","",$qsql["telefon"]),
    "activeTime"=>"00:01 - 00:00"
];

$json["phone"]=$qsql["telefon"];

echo json_encode($json);