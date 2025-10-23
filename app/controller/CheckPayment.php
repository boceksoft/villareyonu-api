<?php

SetHeader(200);
$json=[];

$conversationId = get("_");
if($conversationId){
    $query = $db->prepare("select * from iyzico_response where conversationId=:conversationId");
    $query->execute(["conversationId"=>$conversationId]);
    $iyzico_response= $query->fetch(PDO::FETCH_ASSOC);
    if ($iyzico_response){
        $callback=json_decode($iyzico_response["callback"],2);

        //Ödeme Başarılı olmuş.
        $json["status"]=$callback["status"];

    }else{
        $json["error"]="Bu siparişe ait herhangi bir ödeme bulunamadı.";
    }

}else{
    $json["error"]="Eksik veya hatalı parametre.";
}
echo json_encode($json);

