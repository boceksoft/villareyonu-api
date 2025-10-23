<?php

$json = [];

if (get("table") == "tip") {
    SetHeader(200);
    $sql = "select id as value,baslik".UZANTI." as label from tip where aktif=1 and search".UZANTI."=1 order by siralama asc";
    $query = $db->prepare($sql);
    $query->execute();
    $json["data"] = $query->fetchAll(PDO::FETCH_ASSOC);
} elseif (get("table") == "destinations") {
    SetHeader(200);
    $sql = "select  cast(id as nvarchar(5)) as value,baslik".UZANTI." as baslik,cat,id as label from destinations where aktif=1 and search".UZANTI."=1 order by siralama";
    $query = $db->prepare($sql);
    $query->execute();
    $json["data"] = json_decode($query->fetch(PDO::FETCH_ASSOC)["destinations"]);
} elseif (get("table") == "ozellikler") {
    SetHeader(200);
    $sql = "select  cast(id as nvarchar(5)) as value,baslik".UZANTI." as baslik,cat,id as label from ozellikler where aktif=1  order by siralama";
    echo $sql;
    $query = $db->prepare($sql);
    $query->execute();
    $json["data"] = ($query->fetchAll(PDO::FETCH_ASSOC));
}else{
    SetHeader(204);
}
echo json_encode($json);
