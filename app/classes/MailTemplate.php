<?php

class MailTemplate
{

    public static function Index($islem ="yeni_talep_site.txt",$kayitid=44349){
        global $qsql;
        global $db;

        $dosyaAdi = 'app/bngmail/'.$islem;

        $icerik = file_get_contents($dosyaAdi, null, null, 0, filesize($dosyaAdi));

        $query = $db->prepare("select ".
            " h.baslik".UZANTI." as evBaslik, ".
            " dbo.FnRandomSplit(h.resim,',') as evResim, ".
            " concat(d2.baslik".UZANTI.",' / ',d3.baslik".UZANTI.") as evBolge, ".
            " h.giris_saat as giris_saat, ".
            " h.cikis_saat as cikis_saat, ".
            " h.enlem as evEnlem, ".
            " h.boylam as evBoylam, ".
            " h.url".UZANTI." as evUrl, ".
            " h.id as evKodu, ".
            " k.id as siparis_id, ".
            " k.tur as odeme_turu, ".
            " datediff(day,convert(date,GETDATE(),103),convert(date,k.rez_tarihi,103)) as kalanGun, ".
            " k.musteri, ".
            " convert(varchar(20),k.rez_tarihi,104) as siparis_giris, ".
            " convert(varchar(20),k.gelecek_tarih,104) as siparis_cikis, ".
            " DATEDIFF(day,convert(date,k.rez_tarihi,103),convert(date,k.gelecek_tarih,103)) as siparis_gece, ".
            " k.toplam_tutar as siparis_tutar, ".
            " k.on_odeme as siparis_on_odeme, ".
            " k.kalan as siparis_giriste_kalan, ".
            " k.doviz as siparis_doviz, ".
            " k.hasar_dep as siparis_hasar_dep, ".
            " k.temizlik as siparis_temizlik, ".
            " k.elektrik as siparis_elektrik, ".
            " k.yetiskin as siparis_yetiskin, ".
            " k.cocuk as siparis_cocuk, ".
            " C.Symbol , ".
            " k.site , ".
            " '' ".
            " from kayitlar k ".
            " inner join homes h on h.id=k.evid ".
            " inner join destinations d3 on d3.id=h.emlak_bolgesi ".
            " inner join destinations d2 on d2.id=d3.cat ".
            " inner join Finance.Currency C on C.CurrencyName=k.doviz ".
            " where k.id=".$kayitid);
        $query->execute();
        $gh = $query->fetch(PDO::FETCH_ASSOC);
        if($gh){

            $Sartlar = Page::GetById(24);
            $query= $db->prepare("select * from Reservation.vw_ReservationExtraPayments where ReservationId=".$gh["siparis_id"]);
            $query->execute();
            $ReservationExtraPayments = $query->fetchAll(PDO::FETCH_ASSOC);

            $icerik=str_replace("{-urun-adi-}",				$gh["evBaslik"], 										$icerik);
            $icerik=str_replace("{-kalan-gun-}",				$gh["kalanGun"], 										$icerik);
            $icerik=str_replace("{-musteri-}",				$gh["musteri"], 										$icerik);
            $icerik=str_replace("{musteri}",				$gh["musteri"], 										$icerik);
            $icerik=str_replace("{-urun-bolge-}",			$gh["evBolge"], 										$icerik);
            $icerik=str_replace("{-urun-no-}",				$gh["evKodu"], 											$icerik);
            $icerik=str_replace("{-urun-resim-}",			"/uploads/400x320/".$gh["evResim"], 	$icerik);
            $icerik=str_replace("{-urun-url-}",				$qsql["domain"]."/".$gh["evUrl"],						$icerik);
            $icerik=str_replace("{-enlem-}",				$gh["evEnlem"], 										$icerik);
            $icerik=str_replace("{-boylam-}",				$gh["evBoylam"], 										$icerik);
            $icerik=str_replace("{-yetiskin-}",				$gh["siparis_yetiskin"], 								$icerik);
            $icerik=str_replace("{-cocuk-}",				$gh["siparis_cocuk"], 									$icerik);
            $date1=date("d.m.Y",strtotime($gh["siparis_giris"]));
            $icerik=str_replace("{-giris-tarih-}",			$date1,							$icerik);
            $date2=date("d.m.Y",strtotime($gh["siparis_cikis"]));
            $icerik=str_replace("{-cikis-tarih-}",			$date2,							$icerik);
            $icerik=str_replace("{-giris-tarih-saat-}",		str_replace("-",".",$gh["siparis_giris"])." ".$gh["giris_saat"],	$icerik);
            $icerik=str_replace("{-cikis-tarih-saat-}",		str_replace("-",".",$gh["siparis_cikis"])." ".$gh["cikis_saat"],	$icerik);
            $icerik=str_replace("{-gece-}",					$gh["siparis_gece"], 									$icerik);
            $icerik=str_replace("{-konaklama-tutari-}",		$gh["siparis_tutar"], 									$icerik);
            $msh_toplam_tutar=MailTemplate::dovizTemizle($gh["siparis_tutar"]);
            $msh_on_odeme_tutar=MailTemplate::dovizTemizle($gh["siparis_tutar"]);
            if ($gh["odeme_turu"]=="2") {
                $icerik = str_replace("{-on-odeme-}", $gh["siparis_tutar"], $icerik);
            }else{
                $icerik = str_replace("{-on-odeme-}", $gh["siparis_on_odeme"], $icerik);
                $msh_on_odeme_tutar=MailTemplate::dovizTemizle($gh["siparis_on_odeme"]);
            }
            $icerik=str_replace("{-giriste-odenecek}",		$gh["siparis_giriste_kalan"], 							$icerik);


            $MailOnOdeme = "Ön Ödeme Tutarı";
            $MailGirisKalan = "Girişte Kalan Tutar";
            $MailKisaSureli = "Kısa Süreli K. Ödemesi";
            $MailElektrik = "Elektrik Ücreti";
            $MailGenelToplam = "Genel Toplam";
            $MailOdenen = "Ödenen";
            $MailKalan = "Kalan Tutar";
            if($gh["site"]=="2"){
                $MailOnOdeme = "Pre Payment Amount";
                $MailGirisKalan = "Remaining Amount";
            }

            if ($gh["odeme_turu"]=="1"){
                $icerik=str_replace("{-on-odeme-html-}",				MailTemplate::htmlSablonItem($MailOnOdeme,$gh["siparis_on_odeme"]) , 								$icerik);
                $icerik=str_replace("{-giriste-odenecek-html-}",				MailTemplate::htmlSablonItem($MailGirisKalan,$gh["siparis_giriste_kalan"]) , 								$icerik);
            }
            $icerik=str_replace("{-rezervasyon-no-}",		$gh["siparis_id"], 										$icerik);
            //$icerik=str_replace("{-toplam-tutar-}",		$gh["siparis_id"], 										$icerik);
            $icerik=str_replace("{-hasar-dep-}",			$gh["siparis_hasar_dep"], 								$icerik);

            $toplamEkstra = 0;
            if ($gh["siparis_temizlik"]!="" && $gh["siparis_temizlik"]!="0"){
                $msh_temizlik = MailTemplate::dovizTemizle($gh["siparis_temizlik"]);
                if (is_numeric($msh_temizlik)){
                    $toplamEkstra+=$msh_temizlik;
                    $icerik=str_replace("{-temizlik-ucreti-html-}",				MailTemplate::htmlSablonItem($MailKisaSureli,$gh["siparis_temizlik"]) , 								$icerik);
                    $icerik=str_replace("{-temizlik-ucreti-html-new-}",				MailTemplate::htmlSablonItem3($MailKisaSureli,$gh["siparis_temizlik"]) , 								$icerik);

                }

            }
            if ($gh["siparis_elektrik"]!="" && $gh["siparis_elektrik"]!="0"){
                $msh_elektrik = MailTemplate::dovizTemizle($gh["siparis_elektrik"]);
                if (is_numeric($msh_elektrik)){
                    $toplamEkstra+=$msh_elektrik;
                    $icerik=str_replace("{-elektrik-ucreti-html-}",				MailTemplate::htmlSablonItem($MailElektrik,$gh["siparis_elektrik"]) , 								$icerik);
                }
            }

            $ReservationExtraPaymentsHtml="";
            foreach ($ReservationExtraPayments as $row){
                $toplamEkstra+=$row["TotalAmount"];
                $ReservationExtraPaymentsHtml.=MailTemplate::htmlSablonItem3($row["Title"],$row["Symbol"].$row["TotalAmount"]);
            }
            $icerik=str_replace("{-ReservationExtraPaymentsHtml-}",					$ReservationExtraPaymentsHtml,										$icerik);
            if($toplamEkstra>0) {
                $icerik = str_replace("{-TotalPaymentsHtml-}", MailTemplate::htmlSablonItem3($MailGenelToplam,($gh["Symbol"]).($msh_toplam_tutar+$toplamEkstra)), $icerik);
            }

            $icerik = str_replace("{-odenen-html-}", MailTemplate::htmlSablonItem3($MailOdenen,($gh["Symbol"]).($msh_on_odeme_tutar)), $icerik);
            $icerik = str_replace("{-KalanHtml-}", MailTemplate::htmlSablonItem3($MailKalan,($gh["Symbol"]).(($msh_toplam_tutar+$toplamEkstra)-$msh_on_odeme_tutar)), $icerik);
            $icerik=str_replace("{-toplam-tutar-}",			($gh["Symbol"]).($msh_toplam_tutar), 									$icerik);


            //if ($toplamEkstra>0)
            //    $icerik=str_replace("{-giriste-toplam-odenecek-html-}",				MailTemplate::htmlSablonItem2("Girişte Ödenecek Toplam Tutar","--") , 								$icerik);

        }
        $icerik=str_replace("{-on-odeme-html-}",					"",										$icerik);
        $icerik=str_replace("{-giriste-odenecek-html-}",					"",										$icerik);
        $icerik=str_replace("{-giriste-toplam-odenecek-html-}",					"",										$icerik);
        $icerik=str_replace("{-hizmet-bedeli-html-}",					"",										$icerik);
        $icerik=str_replace("{-temizlik-ucreti-html-}",					"",										$icerik);
        $icerik=str_replace("{-temizlik-ucreti-html-new-}",					"",										$icerik);
        $icerik=str_replace("{-elektrik-ucreti-html-}",					"",										$icerik);
        $icerik=str_replace("{-isitma-ucreti-html-}",					"",										$icerik);
        $icerik=str_replace("{-ReservationExtraPaymentsHtml-}",					"",										$icerik);
        $icerik=str_replace("{-odenen-html-}",					"",										$icerik);
        $icerik=str_replace("{-TotalPaymentsHtml-}",					"",										$icerik);
        $icerik=str_replace("{-KalanHtml-}",					"",										$icerik);


        $icerik=str_replace("{-cdn-}",						str_replace("www.","cdn.",$qsql["domain"])."/",			$icerik);
        $icerik=str_replace("{-iptal-sartlari-url-}",						SiteUrl($Sartlar["url"]),			$icerik);
        $icerik=str_replace("{-now-year-}",					date("Y"),												$icerik);
        $icerik=str_replace("{-site-adi-}",					$qsql["siteadi"],										$icerik);
        $icerik=str_replace("{-domain-}",					str_replace("web.","web.",$qsql["domain"]),				$icerik);
        $icerik=str_replace("{-adres-}",					$qsql["adres"],											$icerik);
        $icerik=str_replace("{-unvan-}",					$qsql["unvan"],											$icerik);
        $icerik=str_replace("{-site-mail-}",				$qsql["sitemail"],										$icerik);
        $icerik=str_replace("{-site-telefon-}",				$qsql["telefon"],										$icerik);
        $icerik=str_replace("{-site-fax-}",					$qsql["fax"],											$icerik);
        $icerik=str_replace("{-site-gsm-}",					$qsql["gsm"],											$icerik);
        $icerik=str_replace("{-site-instagram-}",			$qsql["instagram"],										$icerik);
        $icerik=str_replace("{-site-facebook-}",			$qsql["facebook"],										$icerik);
        $icerik=str_replace("{-site-twitter-}",				$qsql["twitter"],										$icerik);
        $icerik=str_replace("{-site-youtube-}",				$qsql["youtube"],										$icerik);
        if($kayitid==44349){
            echo ($icerik);
        }
        return $icerik;
    }
    public static function dovizTemizle($deger) {
        if ($deger == "") {
            return "";
        }

        $deger = str_ireplace(["TL", "Euro", "Pound", "Dolar", "EUR", "GBP", "USD", "tl", "euro", "pound", "dolar", "$", "€", "£", "₺", "&#8378;"], "", $deger);
        $deger = str_replace([",", "."], "", $deger);

        return trim($deger);
    }
    public static function htmlSablonItem($html_title, $html_value) {
        $htmlSablonItemStr = <<<HTML
<tr>
    <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;"><span style="font-size: 10px; color: #4e4e4e; font-family: 'Poppins', sans-serif; font-weight: 700; font-style: normal;">{-title-}</span></td>
    <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;"><span style="font-size: 10px; color: #4e4e4e; font-family: 'Poppins', sans-serif; font-weight: 700; font-style: normal;">:</span></td>
    <td style="font-family: sans-serif; font-size: 14px; vertical-align: top; width: 17px;">&nbsp;</td>
    <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;"><span style="font-size: 10px; color: #4e4e4e; font-family: 'Poppins', sans-serif; font-weight: 700; font-style: normal;">{-value-}</span></td>
</tr>
HTML;


        return str_replace(["{-title-}", "{-value-}"], [$html_title, $html_value], $htmlSablonItemStr);
    }

    public static function htmlSablonItem2($html_title, $html_value) {
        $htmlSablonItemStr = "<tr>" .
            "  <td style='padding: 5px 12px;'>" .
            "    <strong style='padding-inline-end: 8px; font-size: 14px; color:#ed7823;font-weight:700'>{-title-}</strong>" .
            "  </td>" .
            "  <td style='float: right; padding: 5px 12px; text-align: end; font-size: 14px; font-weight: 600; color: #4A4B54;'>" .
            "    <strong>{-value-}</strong>" .
            "  </td>" .
            "</tr>";

        return str_replace(["{-title-}", "{-value-}"], [$html_title, $html_value], $htmlSablonItemStr);
    }
    public static function htmlSablonItem3($html_title, $html_value) {
        $htmlSablonItemStr = <<<HTML
            <tr>
              <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">
                <span style="font-size: 12px; color: #4e4e4e; font-family: 'Poppins', sans-serif; font-weight: 500; font-style: normal;">{-title-}</span>
              </td>
              <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;"">
                <span style="font-size: 12px; color: #4e4e4e; font-family: 'Poppins', sans-serif; font-weight: 500; font-style: normal;">{-value-}</span>
              </td>
            </tr>
HTML;

        return str_replace(["{-title-}", "{-value-}"], [$html_title, $html_value], $htmlSablonItemStr);
    }
}

