<?php

SetHeader(200);

$query = $db->prepare("Select Filename,aciklama from Upload inner join homes h on h.id=islm_id Where islm='emlak' and h.id=:id");
$query->execute([
    "id"=>get("id")
]);
$json["data"]=$query->fetchAll(PDO::FETCH_ASSOC);


echo json_encode($json);
