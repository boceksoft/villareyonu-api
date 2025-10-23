<?php
SetHeader(200);
$json = [];

$json["social"]["facebook"] = $qsql["facebook"];
$json["social"]["youtube"] = $qsql["youtube"];
$json["social"]["twitter"] = $qsql["twitter"];
$json["social"]["instagram"] = $qsql["instagram"];

$json["static"]["telefon"] = $qsql["telefon"];
$json["static"]["calisma"] = $qsql["calisma"];
$json["static"]["email"] = $qsql["sitemail"];
$json["static"]["whatsapp"] = $qsql["whatsapp"];
$json["static"]["tursabAcentaAdi"] = $qsql["tursabAcentaAdi"];
$json["static"]["tursabNumber"] = Settings::Get("tursabNumber");

$json["staticPages"]["Home"] = Page::GetById(1, "/");
$json["staticPages"]["Reservation"] = Page::GetById(3, "/");

$json["navMenu"] = Menu::GetItemsByMenuId(1,0);
$json["mobileMenu"] = Menu::GetItemsByMenuId(2,0);

echo json_encode($json);