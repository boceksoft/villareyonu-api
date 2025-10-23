<?php
SetHeader(200);
$json = [];

if($_SERVER["REQUEST_METHOD"] == "GET") {

    $query = $db->prepare("select * from iyzico_response where conversationId=:conversationId");
    $query->execute([
        "conversationId"=>get("_")
    ]);
    $r = $query->fetch(PDO::FETCH_ASSOC);

    if ($r){
        header("Content-Type: text/html; charset=utf-8");
        echo (base64_decode($r["threeDSHtmlContent"]));
    }else{
        echo "Bulunamadı";
    }

}
