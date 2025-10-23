<?php

SetHeader(200);
$json=[];

$PromotionCode = post("PromotionCode");

if ($PromotionCode) {
    $query = $db->prepare("select * from promotionCodes where code=:code and GETDATE() between startDate and endDate and stock>0");
    $query->execute(["code" => $PromotionCode]);
    $Code = $query->fetch(PDO::FETCH_ASSOC);
    if ($Code) {
        $json["status"] = "success";
        $json["code"] = $Code;
    } else {
        $json["error"] = $Language["promotionCode"]["notFound"];
    }
}else{
    $json["error"] = $Language["promotionCode"]["invalidCode"];
}

echo json_encode($json);