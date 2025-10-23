<?php

class Product
{
    public static function Calculate($start,$end,$EntityId,$isReservation=0,$PromotionCode=null,$DisableRules=0){

        global $db;
        global $config;
        global $qsql;
        global $Language;
        $json=[];
        if ($start && $end){
            if ($EntityId){
                $date1=date_create($start);
                $date2=date_create($end);
                $fyt_=0;
                $toplam_gun=date_diff($date2,$date1)->days;

                $query = $db->prepare("select cast(FiyatTablosu.ToplamTutar*Rd.Buy as decimal(10,0))  as fyt,cast(FiyatTablosu.IndirimTutari*Rd.Buy as decimal(10,0)) as indirimTutari, Rd.Buy, 
        (select top 1 
            (case when isnull(temizlikgece,0)=0 then gece 
                else 
            (case when gece>temizlikgece then temizlikgece else gece end) 
            end) as gece 
            from sezonlar 
            where site=".PRICE_SITE."
            and islem_id=".$EntityId." 
            and LEN('".$start."')>=8 
            and islem='emlak' and islem_id=".$EntityId." 
            and ('".$start."' between convert(date,tarih1,104) and convert(date,tarih2,104)) order by id desc
        ) as mingece,
        isnull(
            (select top 1 temizlikgece 
                from sezonlar 
                where site=".PRICE_SITE." and islem_id=".$EntityId."
                and LEN('".$start."')>=8 
                and islem='emlak' 
                and islem_id=".$EntityId." 
                and (
                    '".$start."' between convert(date,tarih1,104) and convert(date,tarih2,104)
                    ) 
                order by id desc)
        ,0) as temizlikGece,
        isnull(
            (select top 1 temizlikFiyat from sezonlar 
                where site=".PRICE_SITE."
                and islem_id=".$EntityId." 
                and LEN('".$start."')>=8 
                and islem='emlak' 
                and islem_id=".$EntityId."
                and ('".$start."' between convert(date,tarih1,104) and convert(date,tarih2,104)) order by id desc)
        ,0)*RD2.Buy as temizlikFiyat,
        case when h.kur".UZANTI.">0 then h.kur".UZANTI." else 0 end as kur ,
        h.depozito as depozito,
        dbo.FnRandomSplit(h.resim,',') as resim,
        h.baslik".UZANTI." as baslik,
        h.hasar,  
        ToC.CurrencyName,
        ToC.CurrencyId,
        h.id,
        1 as sitemap,
        h.FirstPaymentTypeId,
        h.aktif".UZANTI." as aktif,
        ToC.Symbol
            from homes h 
            inner join Finance.Currency FromC on FromC.CurrencyName=h.doviz
            cross apply(select * from dbo.Fn_yenifiyathesapla_tablo('".$start."','".$end."',".$EntityId.",".PRICE_SITE.")) as FiyatTablosu
            inner join Finance.Currency ToC on ToC.CurrencyId=:DefaultCurrencyId
                        inner join Finance.Rate FR on FR.RateId=:RateId
            inner join Finance.RateDetail RD on RD.ToCurrencyId=ToC.CurrencyId 
            and RD.FromCurrencyId=FromC.CurrencyId and RD.RateId=FR.RateId
            inner join Finance.RateDetail RD2 on RD2.ToCurrencyId=ToC.CurrencyId 
            and RD2.FromCurrencyId=FromC.CurrencyId and RD2.RateId=FR.RateId
            where h.id=".$EntityId." and h.aktif".UZANTI."=1 order by RD2.RateDetailId  ");

                $query->execute([
                    "RateId"=>Rate::GetLastRate(),
                    "DefaultCurrencyId"=>DefaultCurrencyId
                ]);
                $gh = $query->fetch(PDO::FETCH_ASSOC);
                if(get("currentRoutingTypeId")=="Reservation" && $gh["sitemap"]=="0"){
                    $gh["aktif"]=0;
                }
                if($gh["aktif"]=="1"){


                    for ($i=0;$i<=$toplam_gun;$i++){
                        $x_day = date_create($date1->format("Y-m-d"));
                        if ($i>0)
                            $x_day->modify("+".$i." day");

                        $x_day=$x_day->format("Y-m-d");
                        $query = $db->prepare("select * from dolu where emlak=".$EntityId." and (durum=3 ) and '".$x_day."' between DATEADD(day, +1, tarih) and DATEADD(day, -1, tarih2) ");
                        $query->execute();
                        $kontrol = $query->fetch(PDO::FETCH_ASSOC);
                        if ($kontrol["Durum"]=="3" || $kontrol["Durum"]=="5"){
                            $doluluk=true;
                        }elseif($kontrol["Durum"]=="1"){
                            $odemebekleme=true;
                        }

                        if($end!=$x_day){
                            $query = $db->prepare("select EP.*,EP.title".UZANTI." as title,cast(EP.amount*RD.Buy as decimal(18,0)) as amount from dbo.HomesExtraPayments EP 
            inner join Finance.Currency FromC on FromC.CurrencyId=EP.CurrencyId
            inner join Finance.Currency ToC on ToC.CurrencyId=:DefaultCurrencyId
            inner join Finance.Rate FR on FR.RateId=:RateId
             inner join Finance.RateDetail RD on RD.ToCurrencyId=ToC.CurrencyId 
                and RD.FromCurrencyId=FromC.CurrencyId and RD.RateId=FR.RateId
            where EP.homesId=:homesId and '$x_day' between EP.start_date and EP.end_date");
                            $query->execute([
                                "homesId" => $EntityId,
                                "DefaultCurrencyId" => DefaultCurrencyId,
                                "RateId"=>Rate::GetLastRate(),
                            ]);
                            $ExtraPayments = $query->fetchAll(PDO::FETCH_ASSOC);
                            if ($ExtraPayments) {
                                $json["AvailableExtraServices"][]=[
                                    'date'=>$x_day,
                                    'extraServices'=>$ExtraPayments
                                ];
                            }
                        }

                    }

                    //Bugünden sonra gelen ilk rezi al



                    if ($toplam_gun<$gh["mingece"] && $DisableRules==0){
                        $query = $db->prepare("select * from kisasureli3('".$date1->format('Y-m-d')."','".$date2->format('Y-m-d')."',$EntityId, '1,2,3,4,5,6') as kisasureli where tarih='".$date1->format('Y-m-d')."' and tarih2='".$date2->format('Y-m-d')."' ");
                        $query->execute();
                        $kontrol = $query->fetchAll(PDO::FETCH_ASSOC);
                        if (!$kontrol){
                            $gunkisa=true;
                        }
                    }

                    if($gunkisa){
                        $query = $db->prepare("select top 1 * from dolu where emlak=:emlak and Durum in (1,3) and convert(date,tarih,104)>convert(date,getdate(),104) order by convert(date,tarih,104)");
                        $query->execute(["emlak"=>$EntityId]);
                        $Fr = $query->fetch(PDO::FETCH_ASSOC);
                        if ($Fr){
                            $a = date_create($Fr["tarih"]);
                            $df = $a->diff($date1);
                            if(($df->days)<$gh["mingece"] )
                                $gunkisa=false;
                        }
                    }


                    $fyt_=(int)$gh["fyt"];

                    if ($PromotionCode){
                        $query = $db->prepare("select * from promotionCodes where code=:code and GETDATE() between startDate and endDate and stock>0");
                        $query->execute(["code" => $PromotionCode]);
                        $Code = $query->fetch(PDO::FETCH_ASSOC);
                        if ($Code){
                            $OldPrice = $fyt_;
                            $fyt_=$fyt_-($Code["isPrice"] ? $Code["value"] : (int)($fyt_ / 100 * $Code["value"]));
                        }else{
                            $json["PromotionError"] = "Promosyon kodunuz hatalı veya kullanılmış. Tekrar deneyin.";
                        }
                    }
                    if($gh["indirimTutari"]>0){
                        $OldPrice = $fyt_+$gh["indirimTutari"];
                    }

                    //$DailyPromotion = DailyVillaPromotion::GetByDate($EntityId);
                    //if($DailyPromotion){
                    //    $OldPrice = $fyt_;
                    //    $fyt_=$fyt_-($DailyPromotion["discount_type"]=="amount" ? $DailyPromotion["discount_value"] : (int)($fyt_ / 100 * $DailyPromotion["discount_value"]));
                    //}





                    if ($gh["depozito"]){
                        $depozito = ($fyt_)/100*$gh["depozito"];
                    }else{
                        $depozito = ($fyt_)/100*0;
                    }
                    $kalan = ($fyt_)-$depozito;
                    $kalan2 = ($fyt_)-$depozito;


                    if ($qsql["StopSell"]==1) {
                        $json["error"] = $qsql["StopSellMessage"];
                        $json["StopSell"] = 1;
                    }elseif ($doluluk){
                        $json["error"]=$Language["errors"]["notAvailable"];
                    }elseif ($gunkisa){
                        $json["error"]=$Language["errors"]["minNight"];
                        $json["kuralaTakildi"]=1;
                    }elseif ($fyt_=="0"){
                        $json["error"]=$Language["errors"]["notPrice"];
                    }else{
                        if ($odemebekleme){
                            $json["error"]=$Language["errors"]["hasOption"];
                        }

                        $json["success"]=200;
                        if($OldPrice) {
                            $json["result"]["old_price"] = $OldPrice;
                            if ($isReservation==1)
                                $json["result"]["code"] = $Code;
                        }
                        $json["result"]["accommodation_fee"]=$fyt_;
                        $json["result"]["total_price"]=$fyt_;

                        if ($toplam_gun<$gh["temizlikGece"] && $gh["temizlikFiyat"]!="0"){
                            $json["result"]["cleaning_fee"]=(int)$gh["temizlikFiyat"];
                            $json["result"]["total_price"]+=(int)$gh["temizlikFiyat"];
                            $kalan2+=$gh["temizlikFiyat"];
                        }

                        $json["result"]["remaining_price"]=(int)$kalan2;
                        $json["result"]["remaining_price2"]=(int)$kalan;
                        $json["result"]["deposit_price"]=(int)$depozito;
                        $json["result"]["symbol"]=$gh["Symbol"];
                        $json["result"]["daily_price"]= (int)($fyt_/$toplam_gun);
                        $json["result"]["night"]=$toplam_gun;
                        if ($isReservation)
                            $json["Product"]=$gh;




                        //Ekstra servis hesaplamaları
                        $extraArr=[];
                        foreach ($json["AvailableExtraServices"] as $AvailableExtraService) {
                            foreach ($AvailableExtraService["extraServices"] as $extraService) {
                                if($extraService["IsOptional"]=="0"){
                                    // Zorunlu ise fiyatı hesapla
                                    if($extraService["Type"]=="0"){
                                        //Gecelik
                                        $json["result"]["total_price"]+=(int)$extraService["amount"];
                                        $json["result"]["remaining_price"]+=(int)$extraService["amount"];
                                    }else if($extraService["Type"]=="1"){
                                        //Konaklama Boyu
                                        $extraArr[$extraService["id"]]=(int)$extraService["amount"];
                                    }
                                }else{
                                    if(in_array($extraService["id"],explode(",",get("selectedServices")))){
                                        if($extraService["Type"]=="0"){
                                            //Gecelik
                                            $json["result"]["total_price"]+=(int)$extraService["amount"];
                                            $json["result"]["remaining_price"]+=(int)$extraService["amount"];
                                        }else if($extraService["Type"]=="1"){
                                            //Konaklama Boyu
                                            $extraArr[$extraService["id"]]=(int)$extraService["amount"];
                                        }
                                    }
                                }
                            }
                        }
                        //Zorunlu ve konaklama boyu ekstra servisleri
                        $json["result"]["total_price"]+=(int)array_sum(array_values($extraArr));
                        $json["result"]["remaining_price"]+=(int)array_sum(array_values($extraArr));


                    }
                }else{
                    $json["error"]=$Language["errors"]["notActive"];
                }




            }else $json["error"]=$Language["errors"]["invalidParameter"];
        }else{
            $json["error"]=$Language["errors"]["invalidDates"];
        }
        return $json;
    }
    public static function GetById($EntityId){
        global $db;
        $query = $db->prepare("select h.id,h.enlem,h.boylam,h.baslik from homes h where id=:id");
        $query->execute(["id"=>$EntityId]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }
    public static function PriceList($id, $DefaultCurrencyId)
    {
        global $db;
        global $qsql;
        global $Language;


        $explodeWhere = "";
        if ($qsql["gavelExplodeOption"] == false) {
            $explodeWhere = "AND (ISNULL(ka.belgeSuresiTipi, 1) = 1 OR 
                            (ISNULL(ka.belgeSuresiTipi, 1) = 2 
                            AND ('".$qsql["gavelExplodeDate"]."' >= CONVERT(DATE, s.tarih1, 103) 
                            AND CONVERT(DATE, GETDATE(), 103) <= '".$qsql["gavelExplodeDate"]."')))";
        }

        if ($qsql["StopSell"] == 1)
            return ["error" => $qsql["StopSellMessage"]];

        // Sezonları çek
        $query = $db->prepare("
        SELECT s.sezon, CAST(s.fiyat * RD.Buy AS INT) AS fiyat,
               CONVERT(DATE, s.tarih1, 104) AS tarih1,
               CONVERT(DATE, s.tarih2, 104) AS tarih2,
               s.gece, s.temizlikgece,
               ToC.Symbol,
               CAST(s.temizlikfiyat AS INT) AS temizlikfiyat
        FROM sezonlar s
        INNER JOIN homes h ON h.id = s.islem_id
        LEFT JOIN kanun7464 ka ON ka.homeId = h.id
        INNER JOIN Finance.Currency FromC ON FromC.CurrencyName = h.doviz
        INNER JOIN Finance.Currency ToC ON ToC.CurrencyName = :CurrencyName
        INNER JOIN Finance.RateDetail RD ON RD.ToCurrencyId = ToC.CurrencyId 
                                          AND RD.FromCurrencyId = FromC.CurrencyId 
                                          AND RD.RateId = :RateId
        WHERE NOT s.fiyat = 0 $explodeWhere 
              AND s.islem = 'emlak' 
              AND s.islem_id = :id 
              AND s.site = " . PRICE_SITE . "
              AND CONVERT(DATE, s.tarih2, 104) >= CONVERT(DATE, GETDATE(), 104) 
        ORDER BY CONVERT(DATE, s.tarih1, 104)
    ");

        $query->execute([
            "id" => $id,
            "CurrencyName" => $DefaultCurrencyId,
            "RateId" => Rate::GetLastRate()
        ]);
        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        // İndirimleri çek
        $discountQuery = $db->prepare("
        SELECT * FROM indirimler 
        WHERE emlak = :id 
        AND site = " . PRICE_SITE . " 
        AND CONVERT(DATE, GETDATE(), 104) BETWEEN showDate1 AND showDate2
    ");
        $discountQuery->execute(["id" => $id]);
        $discounts = $discountQuery->fetchAll(PDO::FETCH_ASSOC);

        $json["data"] = [];

        foreach ($result as $item) {
            $seasonStart = $item["tarih1"];
            $seasonEnd = $item["tarih2"];
            $originalPrice = $item["fiyat"];

            $seasonParts = [];

            foreach ($discounts as $discount) {
                $discountStart = $discount["tarih1"];
                $discountEnd = $discount["tarih2"];
                $discountRate = $discount["oran"];

                // Eğer sezon ve indirim tarihleri çakışıyorsa
                if ($discountStart <= $seasonEnd && $discountEnd >= $seasonStart) {

                    // **1. Parça: İndirim Öncesi Dönem**
                    if ($seasonStart < $discountStart) {
                        $seasonParts[] = array_merge($item, [
                            "tarih1" => $seasonStart,
                            "tarih2" => date('Y-m-d', strtotime('-1 day', strtotime($discountStart))),
                            "fiyat" => $originalPrice,
                            "title" => iconv('latin5', 'utf-8', strftime('%e %B', strtotime($seasonStart))) . " - " . iconv('latin5', 'utf-8', strftime('%e %B %Y', strtotime('-1 day', strtotime($discountStart)))),
                            "isDiscounted" => false
                        ]);
                    }

                    // **2. Parça: İndirimli Dönem**
                    $discountedPrice = (int) ($originalPrice * (1 - $discountRate / 100));
                    $seasonParts[] = array_merge($item, [
                        "tarih1" => $discountStart,
                        "tarih2" => $discountEnd,
                        "fiyat" => $discountedPrice,
                        "fiyatOld" => $originalPrice,
                        "dailyPriceOld" => (int)($originalPrice / 7),
                        "discountRate" => $discountRate,
                        "title" => iconv('latin5', 'utf-8', strftime('%e %B', strtotime($discountStart))) . " - " . iconv('latin5', 'utf-8', strftime('%e %B %Y', strtotime($discountEnd))),
                        "isDiscounted" => true
                    ]);

                    // **3. Parça: İndirim Sonrası Dönem**
                    if ($seasonEnd > $discountEnd) {
                        $seasonParts[] = array_merge($item, [
                            "tarih1" => date('Y-m-d', strtotime('+1 day', strtotime($discountEnd))),
                            "tarih2" => $seasonEnd,
                            "fiyat" => $originalPrice,
                            "title" => iconv('latin5', 'utf-8', strftime('%e %B', strtotime('+1 day', strtotime($discountEnd)))) . " - " . iconv('latin5', 'utf-8', strftime('%e %B %Y', strtotime($seasonEnd))),
                            "isDiscounted" => false
                        ]);
                    }

                    break; // Birden fazla indirimle çakışmasını önlemek için çık
                }
            }

            // Eğer sezon herhangi bir indirimle çakışmadıysa olduğu gibi ekleyelim
            if (empty($seasonParts)) {
                $seasonParts[] = array_merge($item, [
                    "title" => iconv('latin5', 'utf-8', strftime('%e %B', strtotime($seasonStart))) . " - " . iconv('latin5', 'utf-8', strftime('%e %B %Y', strtotime($seasonEnd))),
                    "isDiscounted" => false
                ]);
            }

            // JSON çıktısını oluştur
            foreach ($seasonParts as $part) {
                $json["data"][] = array_merge($part, [
                    "dailyPrice" => (int)($part["fiyat"] / 7),
                    "subTitle" => str_replace("{night}", $part["gece"], $Language["priceList"]["subTitle"]),
                    "info" => ($part["temizlikgece"] > 0) ? str_replace(["{night}", "{price}"], [$part["temizlikgece"], $part["temizlikfiyat"] . $part["Symbol"]], $Language["priceList"]["cleaningFeeInfo"]) : null
                ]);
            }
        }

        if (empty($json["data"])) {
            $json["error"] = $Language["priceList"]["notFound"];
        }

        return $json;
    }


    public static function PriceListNew($id, $DefaultCurrencyId)
    {
        global $db;
        global $qsql;
        global $Language;

        $explodeWhere = "";
        if ($qsql["gavelExplodeOption"] == false) {
            $explodeWhere = "AND (ISNULL(ka.belgeSuresiTipi, 1) = 1 OR 
                        (ISNULL(ka.belgeSuresiTipi, 1) = 2 
                        AND ('".$qsql["gavelExplodeDate"]."' >= CONVERT(DATE, s.tarih1, 103) 
                        AND CONVERT(DATE, GETDATE(), 103) <= '".$qsql["gavelExplodeDate"]."')))";
        }

        if ($qsql["StopSell"] == 1)
            return ["error" => $qsql["StopSellMessage"]];

        // 1. SEZONLARI ÇEK
        $query = $db->prepare("
        SELECT s.sezon, CAST(s.fiyat * RD.Buy AS INT) AS fiyat,
               CONVERT(DATE, s.tarih1, 104) AS tarih1,
               CONVERT(DATE, s.tarih2, 104) AS tarih2,
               s.gece, s.temizlikgece,
               ToC.Symbol,
               CAST(s.temizlikfiyat AS INT) AS temizlikfiyat
        FROM sezonlar s
        INNER JOIN homes h ON h.id = s.islem_id
        LEFT JOIN kanun7464 ka ON ka.homeId = h.id
        INNER JOIN Finance.Currency FromC ON FromC.CurrencyName = h.doviz
        INNER JOIN Finance.Currency ToC ON ToC.CurrencyName = :CurrencyName
        INNER JOIN Finance.RateDetail RD ON RD.ToCurrencyId = ToC.CurrencyId 
                                          AND RD.FromCurrencyId = FromC.CurrencyId 
                                          AND RD.RateId = :RateId
        WHERE NOT s.fiyat = 0 $explodeWhere 
              AND s.islem = 'emlak' 
              AND s.islem_id = :id 
              AND s.site = " . PRICE_SITE . "
              AND CONVERT(DATE, s.tarih2, 104) >= CONVERT(DATE, GETDATE(), 104) 
        ORDER BY CONVERT(DATE, s.tarih1, 104)
    ");
        $query->execute([
            "id" => $id,
            "CurrencyName" => $DefaultCurrencyId,
            "RateId" => Rate::GetLastRate()
        ]);
        $seasons = $query->fetchAll(PDO::FETCH_ASSOC);

        // 2. İNDİRİMLERİ ÇEK
        $discountQuery = $db->prepare("
        SELECT * FROM indirimler 
        WHERE emlak = :id 
        AND site = " . PRICE_SITE . " 
        AND CONVERT(DATE, GETDATE(), 104) BETWEEN showDate1 AND showDate2
    ");
        $discountQuery->execute(["id" => $id]);
        $discounts = $discountQuery->fetchAll(PDO::FETCH_ASSOC);

        // 3. GÜNLÜK BAZDA SEZONLARI HAZIRLA
        $seasonDays = [];

        foreach ($seasons as $item) {
            $start = strtotime($item["tarih1"]);
            $end = strtotime($item["tarih2"]);
            $dailyPrice = (int)($item["fiyat"] / 7);

            for ($day = $start; $day <= $end; $day = strtotime("+1 day", $day)) {
                $date = date('Y-m-d', $day);
                $seasonDays[$date] = [
                    "date" => $date,
                    "originalPrice" => $dailyPrice,
                    "price" => $dailyPrice,
                    "season" => $item["sezon"],
                    "symbol" => $item["Symbol"],
                    "isDiscounted" => false,
                    "temizlikgece" => $item["temizlikgece"],
                    "temizlikfiyat" => $item["temizlikfiyat"],
                    "gece" => $item["gece"]
                ];
            }
        }

        // 4. İNDİRİMLERİ GÜNLERE UYGULA
        foreach ($discounts as $discount) {
            $discountStart = strtotime($discount["tarih1"]);
            $discountEnd = strtotime($discount["tarih2"]);
            $rate = $discount["oran"];

            for ($day = $discountStart; $day <= $discountEnd; $day = strtotime("+1 day", $day)) {
                $date = date('Y-m-d', $day);

                if (isset($seasonDays[$date])) {
                    $original = $seasonDays[$date]["originalPrice"];
                    $seasonDays[$date]["price"] = ($original * (1 - $rate / 100));
                    $seasonDays[$date]["fiyatOld"] = $original;
                    $seasonDays[$date]["discountRate"] = $rate;
                    $seasonDays[$date]["isDiscounted"] = true;
                }
            }
        }

        $json["data"] = [];
        $days = array_values($seasonDays);
        $group = [];

        for ($i = 0; $i < count($days); $i++) {
            $day = $days[$i];

            if (empty($group)) {
                $group[] = $day;
                continue;
            }

            $last = end($group);

            $isSameGroup =
                $day["price"] === $last["price"] &&
                $day["isDiscounted"] === $last["isDiscounted"] &&
                $day["gece"] === $last["gece"] &&
                $day["temizlikgece"] === $last["temizlikgece"] &&
                $day["temizlikfiyat"] === $last["temizlikfiyat"];

            $isConsecutive = strtotime($day["date"]) === strtotime("+1 day", strtotime($last["date"]));

            if ($isSameGroup && $isConsecutive) {
                $group[] = $day;
            } else {
                $json["data"][] = self::BuildGroupData($group, $Language);
                $group = [$day];
            }
        }

        if (!empty($group)) {
            $json["data"][] = self::BuildGroupData($group, $Language);
        }


        if (empty($json["data"])) {
            $json["error"] = $Language["priceList"]["notFound"];
        }

        return $json;
    }

    private static function BuildGroupData($group, $Language)
    {
        $first = $group[0];
        $last = end($group);

        $nightCount = count($group);
        $totalPrice = array_sum(array_column($group, "price"));
        $dailyPrice = $group[0]["price"]; // Aynı zaten
        $symbol = $first["symbol"];

        // Temizlik info hesapla (gece bazlı temizlik uygulanıyorsa göster)
        $temizlikInfo = null;
        if ($first["temizlikgece"] > 0) {
            $temizlikInfo = str_replace(
                ["{night}", "{price}"],
                [$first["temizlikgece"], $first["temizlikfiyat"] . $symbol],
                $Language["priceList"]["cleaningFeeInfo"]
            );
        }

        return [
            "tarih1" => $first["date"],
            "tarih2" => $last["date"],
            "fiyat" => $totalPrice,
            "dailyPrice" => $dailyPrice,
            "fiyatOld" => $first["fiyatOld"] ?? null,
            "dailyPriceOld" => $first["fiyatOld"] ?? null,
            "discountRate" => $first["discountRate"] ?? null,
            "isDiscounted" => $first["isDiscounted"],
            "title" => iconv('latin5', 'utf-8', strftime('%e %B', strtotime($first["date"]))) .
                " - " .
                iconv('latin5', 'utf-8', strftime('%e %B %Y', strtotime($last["date"]))),
            "subTitle" => str_replace("{night}", $first["gece"], $Language["priceList"]["subTitle"]),
            "info" => $temizlikInfo,
            "Symbol" => $symbol
        ];
    }




}