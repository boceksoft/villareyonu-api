<?php
SetHeader(200);
$response = [];

$query = $db->prepare("SELECT * FROM viisky_campaigns where aktif = 1 and favori = 1 order by siralama asc");
$query->execute([]);

$data = $query->fetchAll(PDO::FETCH_ASSOC);

if (empty($data)){
    echo json_encode('Not Found');
    SetHeader(404);
    exit;
}

$response["Campaigns"] = $data;
$response["ViiSky"]["Aktiviteler"]=Page::GetById(447,"/");
$response["ViiSky"]["AracKiralama"]=Page::GetById(448,"/");
$response["ViiSky"]["Transfer"]=Page::GetById(449,"/");

echo json_encode($response);
?>