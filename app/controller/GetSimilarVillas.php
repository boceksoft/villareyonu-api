<?php
SetHeader(200);
$json = [];

//breadCrumb_kategori

$query = new Query();
$query->setTop(10);
$query->setQuery("Product");
$query->addParam("and h.emlak_bolgesi=:emlak_bolgesi ",[
    "emlak_bolgesi"=>get("destination")
]);
$query->addParam("and not h.id=:id ",[
    "id"=>get("id")
]);
$json = $query->run();


echo json_encode($json);

