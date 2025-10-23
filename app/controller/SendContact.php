<?php

SetHeader(200);
$json = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $data = json_decode(html_entity_decode(post("data")),2);

    $r=Product::GetById($data["islm_id"]);

    $mailicerik= MailTemplate::Index("default.txt",0);
    $tablex = "<table style='width:100%;box-sizing:border-box;'>";
    $tablex .= "<tr>";
    $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;'><b style='color:#797575;box-sizing:border-box;'>İsim</b></td>";
    $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;text-align:right'>{$data["name"]}</td>";
    $tablex .= "</tr>";
    $tablex .= "<tr>";
    $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;'><b style='color:#797575;box-sizing:border-box;'>Email</b></td>";
    $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;text-align:right'>{$data["email"]}</td>";
    $tablex .= "</tr>";
    $tablex .= "<tr>";
    $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;'><b style='color:#797575;box-sizing:border-box;'>Telefon</b></td>";
    $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;text-align:right'>{$data["telefon"]}</td>";
    $tablex .= "</tr>";
    $tablex .= "<tr>";
    $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;'><b style='color:#797575;box-sizing:border-box;'>Villa</b></td>";
    $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;text-align:right'>{$r["baslik"]}</td>";
    $tablex .= "</tr>";
    $tablex .= "</table>";
    $mailicerik = str_replace("{-mail-icerik-}",		$tablex,			$mailicerik);

    $Mail = new SendMail();
    $Mail->setEmail($config['smtp_username']);
    $Mail->setContent($mailicerik);
    $Mail->setReceiverName($config['smtp_sendFrom']);
    $Mail->setSubject("İletişim Formu");
    $Mail->Send();

    if (!$r)
        $json["error"]="Bir sorun oluştu";
    else
        $json["error"]="Yorumunuz başarıyla gönderildi.";



    echo json_encode($json);
}
