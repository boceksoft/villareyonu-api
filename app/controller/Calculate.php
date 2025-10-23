<?php

SetHeader(200);
$json = [];

$start = get("start");
$end = get("end");
$EntityId = get("EntityId");

//Kiralama Takviminde kontrol Et
$query = $db->prepare("select * from KiralamaTakvimi.CalendarHomes where homesId=:homesId");
$query->execute(["homesId"=>$EntityId]);
$r = $query->fetch(PDO::FETCH_ASSOC);

if ($r){
    $result = KiralamaTakvimiReservation::Check($r["EstateId"],$start,$end);
    if($result["Status"]=="Available")
        if($r["BookableDirectly"]==true)
            $BookableDirectly=1;
    $json = Product::Calculate($start,$end,$EntityId,0,get("PromotionCode"),$result["IsRequestAllowed"]==true || $BookableDirectly);

    if($result["Status"]=="Available"){
        if($r["BookableDirectly"]==true){
            $json["BookableDirectly"]=$r["BookableDirectly"];
        }

    }else{
        $json["error"]=$result["StatusDescription"];
        if($result["Status"]=="AvailableHasWarningRule" || $result["Status"]=="AvailableHasRestrictRule" ){
            $json["ShowAvailableDates"]=true;
            unset($json["success"]);
            if($result["StatusLevel"]=="13"){
                $json["error"]="Konaklama süreniz minumum konaklama süresinin altındadır.";
            }
        }else{
            unset($json["success"]);
        }

    }

    //if($result["IsRequestAllowed"]){
    //    $json["error"]=$result["StatusDescription"];
    //    unset($json["success"]);
    //    $json["ShowAvailableDates"]=true;
    //}
    //if($r["BookableDirectly"]==true){
    //    if($result["Status"]!="Available"){
    //        $json["error"]=$result["StatusDescription"];
    //        unset($json["success"]);
    //        $json["ShowAvailableDates"]=true;
    //    }
    //}
    //$result["IsRequestAllowed"]=true;
    //if($result["Status"]=="UnavailableHasOptioned"){
    //    $result["Status"]="Available";
    //}

}else{
    $json = Product::Calculate($start,$end,$EntityId,0,get("PromotionCode"));
}

$json["apiResponse"]=$result;
if(get("sozlesme")=="1"){
    $query=$db->prepare("select icerik from sayfalar".UZANTI." where id = 38");
    $query->execute();
    $json["contracts"]["sozlesme"]=$query->fetch(PDO::FETCH_ASSOC);
    $query=$db->prepare("select icerik from sayfalar".UZANTI." where id = 188");
    $query->execute();
    $json["contracts"]["kvkk"]=$query->fetch(PDO::FETCH_ASSOC);
}


echo json_encode($json);