<?php

$json = [];

function getDes($id){
    global $db;
    $sql = "select  cast(id as nvarchar(5)) as value,baslik".UZANTI." as label from destinations where aktif=1 and search".UZANTI."=1 and cat={$id} order by siralama";
    $query = $db->prepare($sql);
    $query->execute();
    return array_map(function($item){
        if(getDes($item["value"]))
            $item["children"]=getDes($item["value"]);
        return $item;
    },$query->fetchAll(PDO::FETCH_ASSOC));
}

if (get("table") == "tip_0") {
    SetHeader(200);
    $sql = "select id as value,baslik" . UZANTI . " as label,0 as cat,id,cast('false' as bit)  as checked,icon from tip where aktif=1 and cat=0 order by siralama  ";
    $query = $db->prepare($sql);
    $query->execute();
    $json["data"] = $query->fetchAll(PDO::FETCH_ASSOC);
}elseif (get("table") == "tip") {
    SetHeader(200);
    $sql = "select id as value,baslik".UZANTI." as label,0 as cat,id,cast('false' as bit)  as checked,icon from tip where aktif=1 and search".UZANTI."=1 and cat <> 40  order by siralama  ";
    $query = $db->prepare($sql);
    $query->execute();
    $json["data"] = $query->fetchAll(PDO::FETCH_ASSOC);
}elseif (get("table") == "apart") {
    SetHeader(200);
    $sql = "select id as value,baslik".UZANTI." as label,0 as cat,id,cast('false' as bit)  as checked,icon from tip where aktif=1 and search".UZANTI."=1 and cat = 40  order by siralama  ";
    $query = $db->prepare($sql);
    $query->execute();
    $json["data"] = $query->fetchAll(PDO::FETCH_ASSOC);
} elseif (get("table") == "destinations") {
    SetHeader(200);
    $sql = "select  cast(id as nvarchar(5)) as value,baslik" . UZANTI . " as label from destinations where aktif=1 and cat=0 and search" . UZANTI . "=1 order by siralama";
    $query = $db->prepare($sql);
    $query->execute();

    $json["data"] = array_map(function ($item) {
        $item["children"] = getDes($item["value"]);
        return $item;
    }, $query->fetchAll(PDO::FETCH_ASSOC));
}elseif (get("table") == "ozellikler") {
        SetHeader(200);
        $sql = "select id as value,baslik".UZANTI." as label,0 as cat,id,cast('false' as bit)  as checked from ozellikler where aktif=1  order by siralama  ";
        $query = $db->prepare($sql);
        $query->execute();
        $json["data"] = $query->fetchAll(PDO::FETCH_ASSOC);

}else{
    SetHeader(204);
}
echo json_encode($json,);