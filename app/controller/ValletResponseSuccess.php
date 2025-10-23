<?php

if (post("orderId")){
    header("Content-Type: text/html; charset=utf-8");
    $explode = explode("-",post("orderId"));
    $r = Payment::GetReservation($explode[0]);
    $query = $db->prepare("insert into Finance.VirtualPosResponses (VirtualPosId, Response, kayitlarId,conversationId) VALUES (:VirtualPosId, :Response, :kayitlarId,:conversationId)");
    $query->execute([
        "VirtualPosId"=>2,
        "Response"=>json_encode($_POST),
        "kayitlarId"=>$r["id"],
        "conversationId"=>post("conversationId"),
    ]);

    Payment::Success($explode[0],"VALLET");
    $PayByCreditCardPage = Page::GetById(22);
    $qsql["domain"]=DOMAIN;
    header("Location: ".$qsql["domain"]."/".$PayByCreditCardPage["url"]."?_=".idHash($r["id"])."&conversationId=".post("conversationId"));
    echo "<h1>BAŞARILI!</h1><p>".post("bankMessage")."</p><p>Bu pencereyi kapatabilirsiniz.</p>";
}