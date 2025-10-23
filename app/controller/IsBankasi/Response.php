<?php
header("Content-Type: text/html; charset=utf-8");
$explode = explode("-", post("SID"));
$r = Payment::GetReservation($explode[0]);

if($r["Durum"]=="1"){
    $query = $db->prepare("insert into Finance.VirtualPosResponses (VirtualPosId, Response, kayitlarId,conversationId) VALUES (:VirtualPosId, :Response, :kayitlarId,:conversationId)");
    $query->execute([
        "VirtualPosId" => 5,
        "Response" => json_encode($_POST),
        "kayitlarId" => $r["id"],
        "conversationId" => post("SID"),
    ]);
    $PayByCreditCardPage = Page::GetById(22);

    if(post("ProcReturnCode")=="00"){
        Payment::Success($r["id"],"ISBANKASI",5);
    }

    header("Location: " . SiteUrl($PayByCreditCardPage["url"] . "?_=" . idHash($r["id"]) . "&conversationId=" . post("SID")));
}