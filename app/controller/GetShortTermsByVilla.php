<?php

SetHeader(200);
$json = [];

//sql : select * from dbo.fn_BosTarihAraliklari(1913)

$explodeWhere = "";
if ($qsql["gavelExplodeOption"] == false){
    $explodeWhere = "where (isnull(ka.belgeSuresiTipi, 1) = 1 or (isnull(ka.belgeSuresiTipi, 1) = 2 and ('".$qsql["gavelExplodeDate"]."' >= convert(date, BosBaslangic, 103) )))";
}

$query = $db->prepare("select * from dbo.fn_BosTarihAraliklari(:EntityId) left join kanun7464 ka on ka.homeId=:id ".$explodeWhere);
$query->execute([
    "EntityId"=>get("EntityId"),
    "id"=>get("EntityId")
]);


$json = array_map(function ($item) {
    $item["price"] = Product::Calculate($item["BosBaslangic"], $item["BosBitis"], get("EntityId"), 0, get("PromotionCode"));

    // Eğer $item["price"]["error"] varsa null döndür
    if (isset($item["price"]["error"])) {
        return null;
    }
    return $item;
}, $query->fetchAll(PDO::FETCH_ASSOC));

// Sadece null olmayan elemanları filtrele
$json = array_filter($json);

echo json_encode($json);