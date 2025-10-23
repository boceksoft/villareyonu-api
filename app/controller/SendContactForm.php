<?php

SetHeader(200);
$json = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (post("name") && post("email") && post("phone") && post("messageTitle")&& post("messageContent")){
        $data = json_decode(file_get_contents('php://input'),2);
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
        $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;text-align:right'>{$data["phone"]}</td>";
        $tablex .= "</tr>";
        $tablex .= "<tr>";
        $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;'><b style='color:#797575;box-sizing:border-box;'>Konu</b></td>";
        $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;text-align:right'>{$data["messageTitle"]}</td>";
        $tablex .= "</tr>";
        $tablex .= "<tr>";
        $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;'><b style='color:#797575;box-sizing:border-box;'>Mesaj</b></td>";
        $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;text-align:right'>{$data["messageContent"]}</td>";
        $tablex .= "</tr>";
        $tablex .= "</table>";
        $mailicerik = str_replace("{-mail-icerik-}",		$tablex,			$mailicerik);

        $Mail = new SendMail();
        $Mail->setEmail($config['smtp_username']);
        $Mail->setContent($mailicerik);
        $Mail->setReceiverName($config['smtp_sendFrom']);
        $Mail->setSubject("İletişim Formu");
        if ($Mail->Send())
            $json["success"] = "Yorumunuz başarıyla gönderildi."; //Mail gönderildi
        else
            $json["error"] = "Bir sorun oluştu.(".$Mail->getErr().")"; //Mail gönderilemedi
    }else
        $json["error"]="Lütfen tüm alanların geçerli olduğundan emin olununuz.";



    echo json_encode($json);
}
