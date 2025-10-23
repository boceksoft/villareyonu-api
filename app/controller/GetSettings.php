<?php
SetHeader(200);

$query = "SELECT id,
       siteadi,
       sitemail,
       telefon,
       adres,
       twitter,
       youtube,
       facebook,
       instagram,
       rez_tel,
       viiskyIosVersion,
       viiskyAndroidVersion,
       fax
           FROM genel";

$stmt = $db->prepare($query);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($result);
?>