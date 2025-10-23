<?php
//STUFF((select ';/;/'+m.baslik"&uzanti&"+'##'+isnull(mv.aciklama"&uzanti&",'')+'##'+mv.deger+'##'+isnull(m.icon,'') from
// mesafelerValues mv inner join mesafeler m on m.id=mv.mesafelerId where mv.homesId=h.id
// and m.aktif=1 order by m.siralama asc for xml path('')),1,4,'')


SetHeader(200);
$json = [];


$EntityId = get("EntityId");
try {

if ($EntityId){

    $Product = Product::GetById($EntityId);

    $query = $db->prepare("select mv.id,m.baslik".UZANTI." as baslik,mv.aciklama".UZANTI." as aciklama, mv.deger as deger,m.icon".UZANTI." as icon from mesafelerValues mv
    inner join mesafeler m on m.id = mv.mesafelerId where mv.homesId=:EntityId and m.aktif".UZANTI."=1 order by m.siralama");
    $query->execute(["EntityId"=>$EntityId]);
    $r = $query->fetchAll(PDO::FETCH_ASSOC);

    if ($r)
        $json["data"]=$r;
    else{

        if ($Product["enlem"] && $Product["boylam"]){

        }

    }

}else
    $json["error"]="Eksik parametre";
}
catch (Exception $e) {
    $json["error"]=$e->getMessage();
}

echo json_encode($json);