<?php



SetHeader(200);
$json = [];

$start = post("start");
$end = post("end");
$EntityId = post("EntityId");

//Kiralama Takviminde kontrol Et
$query = $db->prepare("select * from KiralamaTakvimi.CalendarHomes where homesId=:homesId");
$query->execute(["homesId"=>$EntityId]);
$r = $query->fetch(PDO::FETCH_ASSOC);


echo json_encode(KiralamaTakvimiReservation::AvailableReservations($r["EstateId"],$start,$end));