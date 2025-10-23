<?php
SetHeader(200);
$json = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = get("token");
    $id = get("_");
    if ($token) {
        $PhpUserTokens = Login::IsLogin($token);
        if ($PhpUserTokens) {
            $ReservationId = idHash($id,true);
            $query = $db->prepare("select email,musteri from kayitlar where id = :id");
            $query->execute(["id"=>$ReservationId]);
            $Reservation = $query->fetch(PDO::FETCH_ASSOC);


            $konu='Havale Bildirim Formu';
            $mailicerik= MailTemplate::Index("default.txt",0);
            $tablex = "<table style='width:100%;box-sizing:border-box;'>";
            $tablex .= "<tr>";
            $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;'><b style='color:#797575;box-sizing:border-box;'>İsim</b></td>";
            $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;text-align:right'>".post("ad")." ".post("soyad")."</td>";
            $tablex .= "</tr>";
            $tablex .= "<tr>";
            $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;'><b style='color:#797575;box-sizing:border-box;'>Miktar</b></td>";
            $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;text-align:right'>".post("miktar").post("currency")."</td>";
            $tablex .= "</tr>";
            $tablex .= "<tr>";
            $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;'><b style='color:#797575;box-sizing:border-box;'>Tarih</b></td>";
            $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;text-align:right'>".post("tarih")."</td>";
            $tablex .= "</tr>";
            $tablex .= "<tr>";
            $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;'><b style='color:#797575;box-sizing:border-box;'>Konu</b></td>";
            $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;text-align:right'>{$konu}</td>";
            $tablex .= "</tr>";
            $tablex .= "<tr>";
            $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;'><b style='color:#797575;box-sizing:border-box;'>Hesap</b></td>";
            $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;text-align:right'>".post("banka")."</td>";
            $tablex .= "</tr>";
            $tablex .= "<tr>";
            $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;'><b style='color:#797575;box-sizing:border-box;'>Mesaj</b></td>";
            $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;text-align:right'>".post("mesaj")."</td>";
            $tablex .= "</tr>";
            $tablex .= "</table>";
            $mailicerik = str_replace("{-mail-icerik-}",		$tablex,			$mailicerik);

            $Mail = new SendMail();
            $Mail->setEmail($config["smtp_username"]);
            $Mail->setContent($mailicerik);
            $Mail->setReceiverName($config["smtp_sendFrom"]);
            $Mail->setSubject($konu);
            $Mail->Send();

            $query = $db->prepare("insert into havale (kayitlarId, content) values(:kayitlarId,:content)");
            $query->execute([
                "kayitlarId"=>$ReservationId,
                "content"=>$mailicerik
            ]);

            $json["success"]="İşlem başarılı";


        }else{
            $json["error"]="Lütfen giriş yapınız.";
        }
    }else{
        $json["error"]="Geçersiz token bilgisi";
    }
}

echo json_encode($json);