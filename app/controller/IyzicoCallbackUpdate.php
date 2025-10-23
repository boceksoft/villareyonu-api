<?php
SetHeader(200);
$json = [];

$query = $db->prepare("update iyzico_response set callback=:callback where conversationId=:conversationId");
$u = $query->execute([
    "conversationId"=>post("conversationId"),
    "callback"=>html_entity_decode(post("data"))
]);
if ($u){
    $json["status"]="success";
}else
    $json["status"]="failure";
echo  json_encode([
    "conversationId"=>post("conversationId"),
    "callback"=>html_entity_decode(post("data"))
]);

