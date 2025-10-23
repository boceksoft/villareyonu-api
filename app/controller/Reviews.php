<?php

$json = [];
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $page = (int)get("page");
    $pagesize = (int)get("pageSize")?:5;

    $offset = ($page - 1) * $pagesize;

    $sql = "SELECT id,puan,mesaj,sehir,tarih,CASE 
        WHEN CHARINDEX(' ', isim) > 0 THEN
            LEFT(isim, CHARINDEX(' ', isim)) + 
            LEFT(LTRIM(SUBSTRING(isim, CHARINDEX(' ', isim) + 1, LEN(isim))), 1) + 
            '****'
        ELSE
            isim
    END AS isim FROM defter 
            WHERE islm='emlak' and islm_id=:islm_id AND onay=1 
            ORDER BY CONVERT(date, tarih, 104) DESC 
            OFFSET :offset ROWS 
            FETCH NEXT :pagesize ROWS ONLY";

    $query = $db->prepare($sql);
    $query->bindValue(":islm_id", get("EntityId"), PDO::PARAM_STR);
    $query->bindValue(":offset", $offset, PDO::PARAM_INT);
    $query->bindValue(":pagesize", $pagesize, PDO::PARAM_INT);

    $query->execute();
    $reviews = $query->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($reviews);
}else if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    $query = $db->prepare("INSERT INTO defter (isim, puan, mesaj, sehir, tarih, islm, islm_id, onay,eposta) VALUES (:isim, :puan, :mesaj, :sehir, :tarih, :islm, :islm_id, 0, :eposta)");
    $query->execute([
        "isim" => post("name"),
        "puan" => post("rating"),
        "mesaj" => post("message"),
        "sehir" => post("city"),
        "tarih" => date("Y-m-d H:i:s"),
        "islm" => "emlak",
        "islm_id" => post("id"),
        "eposta" => post("email")
    ]);
    $json["success"] = true;
    $json["message"] = "Yorumunuz gönderildi";


    $mailicerik= MailTemplate::Index("default.txt",0);
    $tablex = "<table style='width:100%;box-sizing:border-box;'>";
    $tablex .= "<tr>";
    $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;'><b style='color:#797575;box-sizing:border-box;'>İsim</b></td>";
    $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;text-align:right'>".post("name")."</td>";
    $tablex .= "</tr>";
    $tablex .= "<tr>";
    $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;'><b style='color:#797575;box-sizing:border-box;'>Email</b></td>";
    $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;text-align:right'>".post("email")."</td>";
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
    $Mail->setSubject("Yorum Formu");
    $Mail->Send();

    echo json_encode($json);
}
