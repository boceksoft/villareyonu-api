<?php

SetHeader(200);
$json = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $data = json_decode(html_entity_decode(post("data")),2);

    $query = $db->prepare("select k.id,k.musteri,k.email from kayitlar k where k.id=:id");
    $query->execute(["id"=>idHash($data["_"],true)]);
    $reservation = $query->fetch(PDO::FETCH_ASSOC);

    $query = $db->prepare("insert into defter (isim, eposta, tarih, onay, mesaj, islm_id, puan,  islm, site) values (:isim, :eposta, :tarih, :onay, :mesaj, :islm_id, :puan, :islm, :site)");
    $r = $query->execute([
        "isim"=>$reservation["musteri"],
        "eposta"=>$reservation["email"],
        "tarih"=>date("d.m.Y"),
        "onay"=>false,
        "mesaj"=>$data["mesaj"],
        "islm_id"=>$data["islm_id"],
        "puan"=>$data["puan"],
        "islm"=>"emlak",
        "site"=>SITE,
    ]);

    if (!$r)
        $json["error"]="Bir sorun oluştu";
    else
        $json["success"]="Yorumunuz başarıyla gönderildi.";



    echo json_encode($json);
}
