<?php

SetHeader(200);
$json = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(html_entity_decode(post("data")),2);
    $query = $db->prepare("insert into teklifler (isim, email, telefon, kisi, parametreler, site, link) values (:isim, :email, :telefon, :kisi, :parametreler, :site, :link)");

    $data["start"]=$data["startDate"];
    $data["site"]=1;
    unset($data["startDate"]);

    $data["end"]=$data["endDate"];
    unset($data["endDate"]);

    $data["tip"]=$data["Categories"];
    unset($data["Categories"]);

    $data["tip"]=implode(",",$data["tip"]);

    $data["kisi"]=$data["person"];
    unset($data["person"]);

    $data["isim"]=$data["name"];
    unset($data["name"]);

    $data["telefon"]=$data["phone"];
    unset($data["phone"]);

    $mailicerik= MailTemplate::Index("default.txt",0);
    $tablex = "<table style='width:100%;box-sizing:border-box;'>";
    $tablex .= "<tr>";
    $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;'><b style='color:#797575;box-sizing:border-box;'>İsim</b></td>";
    $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;text-align:right'>{$data["isim"]}</td>";
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
    $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;'><b style='color:#797575;box-sizing:border-box;'>Gör</b></td>";
    $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;text-align:right'><a href='".str_replace("www.","web.",$qsql["domain"])."/boceksoft-vv/teklifler.asp'>Teklifleri Gör</a></td>";
    $tablex .= "</tr>";
    $tablex .= "</table>";
    $mailicerik = str_replace("{-mail-icerik-}",		$tablex,			$mailicerik);

    $Mail = new SendMail();
    $Mail->setEmail($config['smtp_username']);
    $Mail->setContent($mailicerik);
    $Mail->setReceiverName($config['smtp_sendFrom']);
    $Mail->setSubject("Teklif İsteği Gönderildi");
    $Mail->Send();

    $json["result"]=$query->execute([
        "isim"=>$data["isim"],
        "email"=>$data["email"],
        "telefon"=>$data["telefon"],
        "kisi"=>$data["kisi"],
        "parametreler"=>http_build_query($data),
        "site"=>1,
        "link"=>http_build_query($data)
    ]);
    echo json_encode($json);
}