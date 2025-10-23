<?php

SetHeader(200);
$json = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $mailicerik= MailTemplate::Index("default.txt",0);
    $tablex = "<table style='width:100%;box-sizing:border-box;'>";
    $tablex .= "<tr>";
    $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;'><b style='color:#797575;box-sizing:border-box;'>İsim</b></td>";
    $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;text-align:right'>".post("fullName")."</td>";
    $tablex .= "</tr>";
    $tablex .= "<tr>";
    $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;'><b style='color:#797575;box-sizing:border-box;'>Email</b></td>";
    $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;text-align:right'>".post("email")."</td>";
    $tablex .= "</tr>";
    $tablex .= "<tr>";
    $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;'><b style='color:#797575;box-sizing:border-box;'>Telefon</b></td>";
    $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;text-align:right'>".post("phone")."</td>";
    $tablex .= "</tr>";
    $tablex .= "<tr>";
    $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;'><b style='color:#797575;box-sizing:border-box;'>Bölge</b></td>";
    $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;text-align:right'>".post("region")."</td>";
    $tablex .= "</tr>";
    $tablex .= "<tr>";
    $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;'><b style='color:#797575;box-sizing:border-box;'>Havuz</b></td>";
    $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;text-align:right'>".post("pool")."</td>";
    $tablex .= "</tr>";
    $tablex .= "<tr>";
    $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;'><b style='color:#797575;box-sizing:border-box;'>Mesaj</b></td>";
    $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;text-align:right'>".post("message")."</td>";
    $tablex .= "</tr>";
    $tablex .= "</table>";
    $mailicerik = str_replace("{-mail-icerik-}",		$tablex,			$mailicerik);

    $Mail = new SendMail();
    $Mail->setEmail($config['smtp_username']);
    $Mail->setContent($mailicerik);
    $Mail->setReceiverName($config['smtp_sendFrom']);
    $Mail->setSubject("Villa Ekle Formu");

    if (!$Mail->Send())
        $json["error"]="Bir sorun oluştu";
    else
        $json["success"]="Formunuz alınmıştır. İlgili departmanımız en kısa sürede dönüş sağlayacaktır. İlginize teşekkür ederiz.";



    echo json_encode($json);
}
