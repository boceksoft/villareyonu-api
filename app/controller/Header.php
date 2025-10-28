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

$query = $db->prepare("SELECT * FROM bildirimler ORDER BY id DESC");
$query->execute();
$notifications = $query->fetchAll(PDO::FETCH_ASSOC);

$favorites = array_filter($notifications, fn($n) => $n["favori"]);
$notifications = array_filter($notifications, fn($n) => !$n["favori"]);

if (!empty($favorites)) {
    $json["notifications"]["favorite"] = array_values($favorites)[0];
}

$json["notifications"]["data"] = array_values($notifications);

echo json_encode($json);