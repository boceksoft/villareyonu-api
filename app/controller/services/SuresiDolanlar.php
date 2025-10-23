<?php

    header('Content-Type: text/html; charset=UTF-8');

    $query = "Select dolu.durum as ddurum,
    dolu.id as did,kayitlar.id as kid,kayitlar.* ,s.DBTable
    from kayitlar 
    inner join rate on rate.CurrencyName=kayitlar.doviz
    inner join homes on kayitlar.evid = homes.id 
    inner join dolu on dolu.kayitid=kayitlar.id
    inner join sites s on s.id=kayitlar.site
    where isnull(dolu.ReservationId,0)=0 and dolu.durum=1  ";

    $result = $db->query($query);
    $data = $result->fetchAll(PDO::FETCH_ASSOC);

    foreach ($data as $row){
        //14.07.2025 18:51:00 bunun gibi tarih alanı şuan ki zamandan küçük mü

        if(strtotime($row["saat"])<strtotime(date("Y-m-d H:i:s"))){
            $query = $db->prepare("update dolu set durum=2 where id=:did");
            $query->execute([
                "did" => $row["did"]
            ]);

            $query = $db->prepare("select * from MailSablon where id=10");
            $query->execute();
            $mailsablon1 = $query->fetch(PDO::FETCH_ASSOC);

            $mailicerik= MailTemplate::Index("suresi_doldu".$row["DBTable"].".html",$row["id"]);
            $mailicerik = str_replace("{-mail-icerik-}",		$mailsablon1["icerik".$row["DBTable"]],			$mailicerik);

            $Mail = new SendMail();
            $Mail->setEmail($row["email"]);
            $Mail->setContent($mailicerik);
            $Mail->setReceiverName($config['smtp_sendFrom']);
            if ($row["site"]=="2"){
                $Mail->setSubject(" Payment Due! (".$row["id"].")");
            }else{
                $Mail->setSubject("Ödeme Süresi Doldu! (".$row["id"].")");
            }
            $Mail->Send();
        }
    }