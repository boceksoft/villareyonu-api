<?php
SetHeader(200);
$json = [];
$perPage=get("limit")?:9;
if(get("showall")=="1")
    $perPage=99;
$page = get("page") ?: 1;
$start = (($perPage * $page) - $perPage) + 1;
$ExecuteArray=[];


$WithBase = "With FilteredRouting AS (
    SELECT RoutingId, EntityId
    FROM Routing
    WHERE RoutingTypeId = 'ProductDetail' 
    AND Site = 1
) ";

if(get("CurrentRoutingTypeId")=="ShortTerms" && get("ay")=="0" && get("gece")=="0"){
    $json["result"]=false;
    echo json_encode($json);
    exit;
}

if (get("addDays")!="" ){
    $addDays = get("addDays");
}

$min = get("min");
$max = get("max");

if(get("searchdate1")!=""){
    $date1=get("searchdate1");
}
if (get("searchdate1")!="" && get("searchdate2")!=""){
    $date1=get("searchdate1");
    $date2=get("searchdate2");

    if($addDays==-1){
        $date1Obj=date_create($date1);
        $date2Obj=date_create($date2);

        $date1Obj->modify('-1 day');
        $date2Obj->modify('-1 day');

        $date1=$date1Obj->format('Y-m-d');
        $date2=$date2Obj->format('Y-m-d');
    }else if($addDays==1){
        $date1Obj=date_create($date1);
        $date2Obj=date_create($date2);

        $date1Obj->modify('1 day');
        $date2Obj->modify('1 day');

        $date1=$date1Obj->format('Y-m-d');
        $date2=$date2Obj->format('Y-m-d');
    }

}

if(get("kisasureli")=="1"){
    $kisaSureli=true;
}
if(get("sondakika")=="1"){
    $sondakika=true;
}
if(get("gece")){
    $gece=get("gece");
}

if(get("ay")!=""){
    $ay = get("ay");
}
$takvimKurali = "0";
if (get("takvimkurali")=="true")
    $takvimKurali="1";

$esnekTarih = "0";
if (get("esnekTarih")=="true")
    $esnekTarih="1";

if($ay>0){
    $kisaSureli=true;
    $date1 = date("Y-m-d",strtotime(date("Y")."-".$ay."-01"));
    $date2 =  date("Y-m-t",strtotime(date("Y")."-".$ay."-01"));
}



if($gece==""){
    $gece = "2,3,4,5,6";
}
$geceSql = $gece;


if(get("yil")!=""){
    $yearVillas = get("yil");
}

if(get("CurrentRoutingTypeId")=="ProductSearch"){
    if (get("tip")=="29"){
        $yearVillas="2024";
    }else if(get("EntityId")=="5"){
        $sondakika=1;
    }
}else if (get("CurrentRoutingTypeId")=="ProductCategory"){
    if (get("EntityId")=="29")
        $yearVillas="2025";
}
if($date1 && $date2){

    if($date1<date("Y-m-d") && $kisaSureli!=true){
        $date1 = date("Y-m-d",strtotime($date1." +1 years"));
        $date2 = date("Y-m-d",strtotime($date2." +1 years"));
    }
    $tarihli=true;
    $dsql2x = " cast(fiyatlar.sqfiyat*RD.Buy as decimal(12,0)) as sqfiyat, cast(fiyatlar.IndirimTutari * RD.Buy as decimal(12, 0))                                     as IndirimTutari,";

    if($esnekTarih=="1"){
        $musaitliksql=" ,dbo.musaitlikkontrol('".$date1."','".$date2."',h.id,1) as musaitlik ";
        $dsql2x=$dsql2x." fiyatlar.musaitlik as musaitlik, ";
    }
    if (get("nettarih")=="0"){
        $dsql2 ="select nettarih.fiyat as sqfiyat $musaitliksql ";
    }else{
        $dsql2="select ToplamTutar as sqfiyat,IndirimTutari $musaitliksql from dbo.Fn_yenifiyathesapla_tablo('".$date1."','".$date2."',h.id,".PRICE_SITE.") as sqfiyat ";
    }



    if ($kisaSureli){
        //$dsql2="select dbo.Fn_yenifiyathesapla(kisasureli.tarih,kisasureli.tarih2,h.id,".$site.") as sqfiyat ";
        $dsql2="select ToplamTutar as sqfiyat,IndirimTutari from dbo.Fn_yenifiyathesapla_tablo(kisasureli.tarih,kisasureli.tarih2,h.id,".PRICE_SITE.") ";
        $ksqlx= " convert(varchar,kisasureli.tarih,104) as tarih1,convert(varchar,kisasureli.tarih2,104) as tarih2,FORMAT (convert(date,kisasureli.tarih,104), 'dd MMM') as gun1,FORMAT (convert(date,kisasureli.tarih2,104), 'dd MMM') as gun2, ";
        $ksql= " cross apply(select top 1 tarih2 as tarih,dateadd(DAY,+(DATEDIFF(DAY,tarih2,(select min(d2.tarih) from dolu d2 where d2.emlak=dolu.emlak and (d2.durum=3) and d2.tarih>=dolu.tarih2))),tarih2) as tarih2 from dolu where emlak=h.id and (durum=3) and DATEDIFF(DAY,tarih2,(select min(d2.tarih) from dolu d2 where d2.emlak=dolu.emlak and (d2.durum=3 ) and d2.tarih>=dolu.tarih2)) in (".$geceSql.")  and convert(date,tarih2,103)>=convert(date,getdate(),103) ) as kisasureli ";
    }else{
        $ksqlx= " FORMAT(CAST('$date1' AS date), 'dd.MM.yyyy') as tarih1,FORMAT(CAST('$date2' AS date), 'dd.MM.yyyy') as tarih2, ";
    }

    $gecefarksql = round((strtotime($date2) - strtotime($date1))/(60 * 60 * 24))." as geceFark,";
    if ($kisaSureli)
        $gecefarksql = "DATEDIFF(day,convert(date,kisasureli.tarih,104),convert(date,kisasureli.tarih2,104)) as geceFark,";

    $dsqlx= $gecefarksql."  indirimler.oran as indirim,indirimler.tarih1 as it1,indirimler.tarih2 as it2, convert(varchar,indirimler.tarih1,104)+' ile '+convert(varchar,indirimler.tarih2,104)+' tarihleri arasına şimdi rezervasyon yaparsanız %'+convert(varchar,indirimler.oran)+' indirimden yararlanabilirsiniz.' as indirimMsg, ";
    $dsql=" cross apply (select top 1 max(i.oran) as oran,max(i.tarih1) as tarih1,max(i.tarih2) tarih2 from indirimler i where i.emlak=h.id and convert(date,getdate(),104)<=convert(date,i.tarih1,104) and (('".$date2."' between i.tarih1 and i.tarih2) or ('".$date1."' between i.tarih1 and i.tarih2) or (i.tarih1 between '".$date1."' and '".$date2."') or (i.tarih2 between '".$date1."' and '".$date2."'))) as indirimler ";
}else{
    $dsqlx=" indirimler.oran as indirim,indirimler.tarih1 as it1,indirimler.tarih2 as it2, convert(varchar,indirimler.tarih1,104)+' ile '+convert(varchar,indirimler.tarih2,104)+' tarihleri arasına şimdi rezervasyon yaparsanız %'+convert(varchar,indirimler.oran)+' indirimden yararlanabilirsiniz.' as indirimMsg, ";
    $dsql=" LEFT JOIN 
        Indirimlerx indirimler ON indirimler.id = h.id ";

    $WithBase.=", Indirimlerx AS (
    SELECT 
        h.id,
        MAX(i.oran) AS oran,
        MAX(i.tarih1) AS tarih1,
        MAX(i.tarih2) AS tarih2
    FROM 
        homes h
    INNER JOIN 
        indirimler i ON i.emlak = h.id
    WHERE 
        CONVERT(DATE, GETDATE(), 104) <= CONVERT(DATE, i.tarih1, 104)
    GROUP BY 
        h.id
)
";

    if($qsql["gavelExplodeOption"]=="0"){
        $explodeWhere="and (
                isnull(ka.belgeSuresiTipi, 1) in (0,1) 
                    or 
                (
                    isnull(ka.belgeSuresiTipi, 1) = 2 and convert(date, tarih1, 104) <= '".$qsql["gavelExplodeDate"]."' 
                )
        )";
    }
    //dsql2 de and convert(date,tarih2,104)>=convert(date,getdate(),105) silindi.
    $dsql2=" select (min(fiyat/7) * RD.Buy) as minfiyat,(max(fiyat/7) * RD.Buy) as maxfiyat
                                          from sezonlar
                                          where site = 1
                                            and islem = 'emlak'
                                            and islem_id = h.id
                                            and not fiyat = 0
                                            ".$explodeWhere."
                                            and convert(date, tarih2,
                                                              104) >= convert(date, getdate(),
                                                                                    105) ";
    $dsql2x=" cast(fiyatlar.minfiyat as decimal(12,0)) as minfiyat,cast(fiyatlar.maxfiyat as decimal(12,0)) as maxfiyat, ";

}

if ($sondakika){
    $sSql=" cross apply(select top 1 id,replace(tarih1,'/','.') as tarih1,replace(tarih2,'/','.') as tarih2,fiyat,                            FORMAT (convert(date,tarih1,104), 'dd MMM') as gun1,
                            FORMAT (convert(date,tarih2,104), 'dd MMM') as gun2,datediff(day,convert(date,tarih1,104),convert(date,tarih2,104)) as gece,convert(varchar,day(convert(date,tarih1,104))) as gun,convert(varchar,datename(month,convert(date,tarih1,104))) as ay from sonDakika where islem_id=h.id and convert(date,getdate(),104)<=convert(date,tarih1,104)  and site=".PRICE_SITE." order by convert(date,tarih1,104) ) as sonDakika ";
    $sSqlx=" sonDakika.fiyat,sonDakika.gece,sonDakika.gun,sonDakika.ay,sondakika.tarih1,sondakika.tarih2,sondakika.id as sid,1 as isSonDakika,sondakika.gun1,sondakika.gun2,  ";
    if($tarihli!=true){
        $dsql2="select sondakika.fiyat as minfiyat,sondakika.fiyat as maxfiyat ";
        $dsql2x=" fiyatlar.minfiyat as minfiyat,fiyatlar.maxfiyat as maxfiyat, ";
    }
}

$takvimCross = "";
$takvimWhere = "";
$takvimSelect = "";

if ($takvimKurali == "1" && $tarihli == true) {
    $takvimCross = "cross apply (SELECT TOP 1 
        (CASE WHEN ISNULL(gece, '') = '' THEN 0 ELSE gece END) AS gece,
        ISNULL((SELECT DATEDIFF(DAY, MAX(CONVERT(DATE, dd.tarih2, 104)), '".$date1."') 
            FROM dolu dd 
            WHERE dd.emlak = h.id AND dd.durum = 3 AND CONVERT(DATE, dd.tarih2, 104) <= '".$date1."'
            AND CONVERT(DATE, dd.tarih2, 104) > CONVERT(DATE, GETDATE(), 104)), gece) AS girisara,
        ISNULL((SELECT DATEDIFF(DAY, '".$date2."', MIN(CONVERT(DATE, dd.tarih, 104))) 
            FROM dolu dd 
            WHERE dd.emlak = h.id AND dd.durum = 3 AND CONVERT(DATE, dd.tarih, 104) >= '".$date2."' 
            AND CONVERT(DATE, dd.tarih2, 104) > CONVERT(DATE, GETDATE(), 104)), gece) AS cikisara
        FROM sezonlar 
        WHERE site = 1 AND islem = 'emlak' AND islem_id = h.id AND LEN(tarih2) = 10 
        AND '".$date1."' >= CONVERT(DATE, sezonlar.tarih1, 104) 
        AND '".$date1."' <= CONVERT(DATE, sezonlar.tarih2, 104)) as sezon ";
    $takvimWhere = " AND (sezon.girisara >= sezon.gece OR sezon.girisara = 0) 
        AND (sezon.cikisara >= sezon.gece OR sezon.cikisara = 0) 
        AND (DATEDIFF(DAY, '".$date1."', '".$date2."') >= sezon.gece 
        OR (sezon.girisara IN (sezon.gece, 0) AND sezon.cikisara IN (0)))";
    $takvimSelect = "0 AS gecemax, 0 AS sezongece,";
}

if ( $tarihli == true && $takvimKurali != "1") {
    $takvimCross = "cross apply (SELECT TOP 1
        (CASE WHEN ISNULL(gece, '') = '' THEN 0 ELSE gece END) AS gece
        FROM sezonlar
        WHERE site = 1 AND islem = 'emlak' AND islem_id = h.id AND LEN(tarih2) = 10
        AND '".$date1."' >= CONVERT(DATE, sezonlar.tarih1, 104)
        AND '".$date1."' <= CONVERT(DATE, sezonlar.tarih2, 104)) as sezon";
    $takvimSelect = "(CASE WHEN DATEDIFF(DAY, '".$date1."', '".$date2."') >= sezon.gece THEN 0 ELSE 1 END) AS gecemax,
        sezon.gece AS sezongece,";
}

$bolge = get("bolge");
$ozellik=get("specy");
$kodara = get("kodara");
$yetiskin = get("yetiskin");
$cocuk = get("cocuk");
$yatak_odasi = get("yatak_odasi");
$searchguest=get("searchguest");
$kisi=0;
$kisi2=get("kisi2");

if ($yetiskin)
    $kisi+=$yetiskin;
if ($cocuk)
    $kisi+=$cocuk;



if(get("customList")!="0" && get("customList")!=""){

    $CustomListJoin="inner join CustomList on CustomList.PageId=:PageId ";
    $CustomListJoin.="inner join CustomListHomes on CustomListHomes.HomeId=h.id and CustomListHomes.ListId=CustomList.Id ";
    $ExecuteArray["PageId"]=get("customList");

}

$urlSelect="concat('/',h.url".UZANTI.") as url";
if($sondakika){
    $urlSelect="concat('/',h.url".UZANTI.",'?start=',convert(date,sonDakika.tarih1,104),'&end=',convert(date,sonDakika.tarih2,104),'&q=1') as url";
}else if($kisaSureli){
    $urlSelect="concat('/',h.url".UZANTI.",'?start=',convert(date,kisasureli.tarih,104),'&end=',convert(date,kisasureli.tarih2,104),'&q=1') as url";
}else if($date1 && $date2){
    $urlSelect="concat('/',h.url".UZANTI.",'?start=','".$date1."','&end=','".$date2."','&q=1') as url";
}

$orderSqlEkle="";
if (get("maxfiyat")=="1"){
    $orderEk=" order by fiyatlar.sqfiyat desc";
}else{
    if (get("order_by")=="1"){
        $orderEk=" order by ".$orderSqlEkle." h.id asc";
    }else if (get("order_by")=="2"){
        $orderEk=" order by ".$orderSqlEkle." h.id desc";
    }else if (get("order_by")=="3"){
        if ($tarihli){
            $sirala=" order by ".$orderSqlEkle." fiyatlar.sqfiyat*RD.Buy asc";
        }else{
            $sirala=" order by ".$orderSqlEkle." fiyatlar.minfiyat asc";
        }
        $orderEk=$sirala;
    }else if (get("order_by")=="4"){
        if ($tarihli){
            $sirala=" order by ".$orderSqlEkle." fiyatlar.sqfiyat*RD.Buy desc";
        }else{
            $sirala=" order by ".$orderSqlEkle." fiyatlar.minfiyat desc";
        }

        $orderEk=$sirala;
    }else if (get("order_by")=="5"){
        $orderEk=" order by h.kisi asc";
    }else if (get("order_by")=="6"){
        $orderEk=" order by h.kisi desc";
    }else if (get("order_by")=="7"){
        $orderEk=" order by yorumlar.rating asc";
    }else if (get("order_by")=="8"){
        $orderEk=" order by yorumlar.rating desc";
    }else{
        if($esnekTarih=="1" && $tarihli){
            $orderEk=" order by CHARINDEX('".$date1."',fiyatlar.musaitlik) desc  ";
        }else
            $orderEk=  " order by ".$orderSqlEkle." h.siralama asc,h.id asc ";
    }
}

if($qsql["gavelListingOption"]=="0" && get("ids")==""){
    $gavelek = " and (isnull(ka.gavel,0)=0) ";
}else{
    $gavelek="";
}

$countSelect = $WithBase." select count(1) as totalCount ";
$normalSelect = "SET LANGUAGE Turkish; ".$WithBase." select * from (select  ROW_NUMBER() OVER (".$orderEk.") AS row_num, h.id,ToC.Symbol,
h.ribbon".UZANTI." as ribbon,
h.ribbon2".UZANTI." as ribbon2,
h.doviz as doviz,
h.depozito,
CH.BookableDirectly,
fr.RoutingId,
fr.EntityId, 
yorumlar.*, 
last30dayrez=isnull((select count(dd2.id) from dolu dd2 where dd2.emlak=h.id and dd2.durum=3 and convert(date,dd2.createdOn,103) between dateadd(day,-30,convert(date,getdate(),103)) and convert(date,getdate(),103) ),0),
TemizlikFiyatlar=stuff((SELECT top 1 ';/;/'+convert(varchar,sezonlar.gece)+'##'+convert(varchar,isnull(sezonlar.temizlikgece,0))+'##'+convert(varchar,isnull(sezonlar.temizlikfiyat,0)) FROM   sezonlar WHERE  site = ".PRICE_SITE." AND islem = 'emlak' AND islem_id = h.id AND Len(tarih2) = 10 and '".$date1."'>=CONVERT(DATE, sezonlar.tarih1, 104) and '".$date1."'<=CONVERT(DATE, sezonlar.tarih2, 104) ORDER  BY CONVERT(DATE, sezonlar.tarih1, 104) FOR xml path('')),1,4,''),
OnecikanOzellikler_old=stuff((select concat(';/;/', oo.id, '##', oo.baslik".DILUZANTI.")
                                 from oneCikanOzellikler oo
                                 where replace(concat(',', h.onecikan, ','), ' ', '') like concat('%,', oo.id, ',%')
                                 for xml path ('')), 1, 4, ''),
OnecikanOzellikler=stuff((select concat(';/;/', oo.id, '##', oo.baslik".UZANTI.")
from ozellikler oo
where replace(concat(',', h.ozellikler, ','), ' ', '') like concat('%#', oo.id, '#%') and oo.cat = 96
for xml path ('')), 1, 4, ''),
h.kisi,h.yatak_odasi,h.banyo,h.title".UZANTI." as title ,h.kisa_icerik".UZANTI." as icerik,
{$urlSelect},dbo.FnRandomSplit(h.resim,',') as image,
".$sSqlx."
".$dsql2x."
".$dsqlx."
".$ksqlx."
h.baslik".UZANTI." as name,d2.baslik as destination ";



$sql = " from homes h
inner join Finance.Currency FromC on FromC.CurrencyName=h.doviz
left join kanun7464 ka on ka.homeId=h.id
left join KiralamaTakvimi.CalendarHomes CH on CH.homesId=h.id
inner join Finance.Currency ToC on ToC.CurrencyId=:DefaultCurrencyId
inner join Finance.RateDetail RD on RD.ToCurrencyId=ToC.CurrencyId and RD.FromCurrencyId=FromC.CurrencyId and RD.RateId=:RateId
".$sSql."
".$dsql."
".$ksql."
".$CustomListJoin."
".$takvimCross."
cross apply(".$dsql2.") as fiyatlar
INNER JOIN tip t on t.id=h.emlak_tipi
inner join destinations d2 on d2.id = h.emlak_bolgesi
inner join destinations d1 on d1.id = d2.cat
inner join destinations d0 on d0.id = d1.cat
left join destinations dbase on dbase.id=d0.cat 
LEFT JOIN 
    FilteredRouting fr ON fr.EntityId = h.id
outer apply (
    select cast(avg(cast(puan as decimal(10,5))) as decimal(10,1)) as rating,count(id) as reviews from dbo.defter where islm='emlak' and islm_id=h.id and onay=1
) as yorumlar    
where h.aktif".UZANTI."=1 and d2.aktif=1 and d1.aktif=1 and t.aktif=1 ".$takvimWhere." ".$gavelek." ";

$ExecuteArray["DefaultCurrencyId"]=DefaultCurrencyId;
$ExecuteArray["RateId"]=Rate::GetLastRate();


if($tarihli){
    $sql.=" and not fiyatlar.sqfiyat=0 ";
}else{
    $sql.=" and not fiyatlar.minfiyat=0 ";
}

if($yearVillas!=""){
    $sql=$sql." and isnull((select max(year(convert(date,tarih1,103))) from sezonlar where islem='emlak' and site=".$site." and islem_id=h.id and convert(date,tarih2,103)>=convert(date,getdate(),103) and year(convert(date,tarih1,103)) in (".$yearVillas.")),'')!='' ";
}


// Tipe Göre

if(get("CurrentRoutingTypeId")=="ProductCategory"){
    if (get("EntityId")!="29")
        $tip = get("EntityId");
}else{
    if (get("tip")!="29")
        $tip = get("tip");
}

if($tip){

    $arr = explode(",",$tip);
    if (count($arr)>1){
        $orderSql="1=1";
        $sqlekle=" and (1=1 ";
        foreach ($arr as $item){
            $sqlekle.="and ( not isnull((select tt2.cat from tip tt2 where tt2.id=".$item."),0)=0 and (','+replace(h.kategori,' ','')+',' like '%,".trim($item).",%' or h.emlak_tipi=".$item.")  ) ";
            $orderSql.=" and (','+replace(h.kategori,' ','')+',' like '%,".$item.",%' or h.emlak_tipi=".$item.")";
        }
        $sqlekle.=")";
        $sql.=$sqlekle;
        $orderSqlEkle=" (case when (".$orderSql.") then 1 else 0 end) desc,";
    }else{
        $sql.=" and (','+replace(h.kategori,' ','')+',' like '%,".$tip.",%' or h.emlak_tipi=".$tip.") ";
    }
}
// Tipe Göre

if($ozellik){

    $arr = explode(",",$ozellik);
    if (count($arr)>1){
        $orderSql="1=1";
        $sqlekle=" and (1=1 ";
        foreach ($arr as $item){
            $sqlekle.="and ('#'+replace(h.ozellikler,' ','')+'#' like '%#".trim($item)."#%') ";
            $orderSql.=" and ('#'+replace(h.ozellikler,' ','')+',' like '%#".$item."#%' )";
        }
        $sqlekle.=")";
        $sql.=$sqlekle;
    }else{
        $sql.=" and ('#'+replace(h.ozellikler,' ','')+'#' like '%#".$ozellik."#%' ) ";
    }
}

if(get("CurrentRoutingTypeId")=="ProductDestination"){
    //if(get("EntityId")=="24")
    //    $sql.=" and ((21 in (d2.id,d1.id,d0.id,dbase.id)) or ".get("EntityId")." in (d2.id,d1.id,d0.id,dbase.id)) ";
    //else
        $sql.=" and ".get("EntityId")." in (d2.id,d1.id,d0.id,dbase.id) ";
}else {
    $bolge = get("bolge");
}
if ($bolge){
    $arr = explode(",",$bolge);
    if (count($arr)>1) {
        $sql.=" and (d1.id in (".implode(",",$arr).") or d2.id in (".implode(",",$arr).") or d0.id in (".implode(",",$arr).") or dbase.id in (".implode(",",$arr).") )";
    }else{
        $sql.=" and ".$bolge." in (d2.id,d1.id,d0.id,dbase.id) ";
    }
}

if($date1 && $date2){
    $date=date_create($date1);
    $date2=date_create($date2);

    $date->modify('+1 day');
    $date2->modify('-1 day');


    $startDate = date_format($date,"Y-m-d");
    $endDate = date_format($date2,"Y-m-d");




    if($esnekTarih=="1")
        $sql.=" and len(fiyatlar.musaitlik)>0  ";
    else
        if ($kisaSureli!=true){
            $sql.=" and (select top 1 count(dolu.id) from dolu where dolu.emlak=h.id and dolu.durum=3 and (('".$endDate."' between dolu.tarih and dolu.tarih2) or ('".$startDate."' between dolu.tarih and dolu.tarih2) or (dolu.tarih between '".$startDate."' and '".$endDate."') or (dolu.tarih2 between '".$startDate."' and '".$endDate."')))=0 ";
            if ($sondakika){
                $sql.=" and  sondakika.tarih1='".$startDate."'  and  sondakika.tarih2='".$endDate."' ";
            }
            $sql.=" and (select top 1 count(dolu.id) from dolu where dolu.emlak=h.id and dolu.durum=3 and (('".$endDate."' between dolu.tarih and dolu.tarih2) or ('".$startDate."' between dolu.tarih and dolu.tarih2) or (dolu.tarih between '".$startDate."' and '".$endDate."') or (dolu.tarih2 between '".$startDate."' and '".$endDate."')))=0";
        }else{
            $sql.=" and  (('".$endDate."' between kisasureli.tarih and kisasureli.tarih2) or ('".$startDate."' between kisasureli.tarih and kisasureli.tarih2) or (kisasureli.tarih between '".$startDate."' and '".$endDate."') or (kisasureli.tarih2 between '".$startDate."' and '".$endDate."')) ";
        }
}


//Kişi Sayısına Göre

//if not kisi="" and not kisi="0" then
//    if isnumeric(kisi)=true then
//
//        if kisi="1" then
//            sql=sql&" and (h.kisi=1 or h.kisi=2) "
//        'elseif kisi="2" then
//        '    sql=sql&" and (','+replace(h.kategori,' ','')+',' like '%,1,%' or h.emlak_tipi=1)"
//        else
//            if kisi mod 2 = 0 then 'çift sayı ise
//                sql=sql&" and h.kisi between "&kisi&" and "&kisi+2
//            else
//                sql=sql&" and h.kisi between "&kisi-1&" and "&kisi+1
//            end if
//        end if
//
//
//    end if
//end if

if($kisi){
    $sql.=" and (h.kisi>=:kisi) ";
    $ExecuteArray["kisi"]=$kisi;
    //if($kisi=="1"){
    //    $sql.=" and (h.kisi=1 or h.kisi=2) ";
    //}else{
    //    if($kisi % 2==0){
    //        $sql.=" and h.kisi between :kisi and :kisi2 ";
    //        $ExecuteArray["kisi"]=$kisi;
    //        $ExecuteArray["kisi2"]=$kisi+2;
    //    }else{
    //        $sql.=" and h.kisi between :kisi and :kisi2 ";
    //        $ExecuteArray["kisi"]=$kisi-1;
    //        $ExecuteArray["kisi2"]=$kisi+1;
    //    }
    //}
}
if($kisi2) {
    $sql .= " and (h.kisi<=:kisi2) ";
    $ExecuteArray["kisi2"] = $kisi2;
}



//Yatak Sayısına Göre
if ($yatak_odasi){
    $ExecuteArray["yatak_odasi"]=$yatak_odasi;
    $sql.=" and h.yatak_odasi=:yatak_odasi";
}

//Fiyat Aralığına Göre
if ($min && $max ){
    if ($max>0){
        if ($tarihli){
            $sql.=" and fiyatlar.sqfiyat between ".$min." and ".$max;
        }else{
            $sql.=" and (fiyatlar.minfiyat) between ".$min." and ".$max;
        }
    }
}

if (get("indirimliVillalar")=="1" || $indirimli==true){
    $sql.=" and indirimler.oran>0  ";
}

if (get("ids")){
    $sql.=" and h.id in (".str_replace("%2C","",get("ids")).")";
}


if (get("CurrentRoutingTypeId")=="ProductSearch" && get("EntityId")=="367"){
    //Popüler Villalar


    $sql.=" and h.vitrin=1 ";

}
if (get("CurrentRoutingTypeId")=="ProductSearch" && get("EntityId")=="346"){
    //Popüler Villalar


    $sql.=" and h.favori=1 ";

}

if(get("search")){
    $ExecuteArray["se"]="%".(get("search"))."%";
    $sql.=" and (h.baslik) like :se COLLATE Turkish_CI_AS ";
}

try {
    if(get("sqlx")){
        //echo $countSelect.$sql;
        echo $normalSelect.$sql." ) subquery
    WHERE row_num BETWEEN $start AND ".(($start -1)+$perPage);
        print_r($ExecuteArray);
        exit;
    }
    $totalRecord =$db->prepare($countSelect.$sql);
    $totalRecord->execute($ExecuteArray);
    $totalRecord=$totalRecord->fetch(PDO::FETCH_ASSOC)["totalCount"];
    //$totalRecord=100;
    $arr = $db->prepare($normalSelect.$sql." ) subquery
    WHERE row_num BETWEEN $start AND ".(($start -1)+$perPage));

    if ($addDays!=1 && $addDays!=-1){
        $arr->execute($ExecuteArray);
        $json["result"] = $arr->fetchAll(PDO::FETCH_ASSOC);
        $json["CurrentPage"]=$page;
        $json["TotalPage"]=(($totalRecord - ($totalRecord % $perPage)) / $perPage) + ($totalRecord % $perPage>0 ? 1 :0);
    }
    $json["TotalRecord"]=$totalRecord;
}catch (Exception $e) {
    $json["error"]=$e->getMessage();
}


echo json_encode($json);

