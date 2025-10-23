<?php
header("Content-Type: text/html; charset=utf-8");
$explode = explode("-", post("orderid"));
$r = Payment::GetReservation($explode[0]);
if($r["Durum"]=="1"){
    $query = $db->prepare("insert into Finance.VirtualPosResponses (VirtualPosId, Response, kayitlarId,conversationId) VALUES (:VirtualPosId, :Response, :kayitlarId,:conversationId)");
    $query->execute([
        "VirtualPosId" => 6,
        "Response" => json_encode($_POST),
        "kayitlarId" => $r["id"],
        "conversationId" => post("orderid"),
    ]);
    $PayByCreditCardPage = Page::GetById(22);

    if(post("procreturncode")=="00"){
        Payment::Success($r["id"],"GARANTI",6);
    }

    header("Location: " . SiteUrl($PayByCreditCardPage["url"] . "?_=" . idHash($r["id"]) . "&conversationId=" . post("orderid")));
}