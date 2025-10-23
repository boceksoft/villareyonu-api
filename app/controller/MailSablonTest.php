<?php

    header("Content-Type: text/html; charset=utf-8");


    $query = $db->prepare("select top 10 id,musteri,adi from dbo.kayitlar where id=10254 order by id desc");
    $query->execute();
    $kayitlar = $query->fetchAll(PDO::FETCH_ASSOC);
    foreach ($kayitlar as $kayit){

        $kayitId=$kayit["id"];

        $query = $db->prepare("select * from MailSablon where id=3");
        $query->execute();
        $mailsablon1 = $query->fetch(PDO::FETCH_ASSOC);

        $ReservationsPage=Page::GetById(3);

        $MailBaslik=str_replace("{ReservationId}",$kayitId,$Language["paymentSuccessMail"]["title"]);

        $mailicerik = MailTemplate::index("tamamlandi".UZANTI.".html",$kayitId);

        $mailicerik = str_replace("{-mail-baslik-}",				$MailBaslik,								$mailicerik);
        $mailicerik = str_replace("{-mail-icerik-}",				$mailsablon1["icerik".UZANTI],													$mailicerik);
        $mailicerik = str_replace("{musteri}",					$kayit["musteri"],													$mailicerik);
        $mailicerik = str_replace("{villaadi}",					$kayit["adi"],													$mailicerik);
        $mailicerik = str_replace("{-kisi-bilgileri-btn-}",			SiteUrl($ReservationsPage["url"]),		$mailicerik);
        $mailicerik = str_replace("{-rezervasyonlarim-btn-}",		SiteUrl($ReservationsPage["url"]),			$mailicerik);
        $mailicerik = str_replace("{-rez-tamamla-url-}",		SiteUrl($ReservationsPage["url"]),			$mailicerik);
        echo $mailicerik;


    }



