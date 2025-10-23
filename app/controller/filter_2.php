<?php
SetHeader(200);
$json = [];
$perPage=9;
$page = get("page") ?: 1;
$start = ($perPage * $page) - $perPage;
$ExecuteArray=[];
$site=1;
$uzanti = "";


$min = get("min");
$max = get("max");

if(get("searchdate1")!=""){
    $date1=get("searchdate1");
}
if (get("searchdate1")!="" && get("searchdate2")!=""){
    $date1=get("searchdate1");
    $date2=get("searchdate2");
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

if($ay>0){
    $kisaSureli=true;
    $date1 = strtotime(date("Y")."-".$ay."-01");
    $date2 = date("Y-m-t",$date1);
}


if($gece==""){
    $gece = "2,3,4,5,6";
}
$geceSql = $gece;


if(get("yil")!=""){
    $yearVillas = get("yil");
}

if($date1 && $date2){

    if($date1<date("Y-m-d") && $kisaSureli!=true){
        $date1 = date("Y-m-d",strtotime($date1." +1 years"));
        $date2 = date("Y-m-d",strtotime($date2." +1 years"));
    }
    $tarihli=true;
    $dsql2x = " fiyatlar.sqfiyat as sqfiyat, ";

    if (get("nettarih")=="0"){
        $dsql2 ="select nettarih.fiyat as sqfiyat ";
    }else{
        $dsql2="select dbo.Fn_yenifiyathesapla('".$date1."','".$date2."',h.id,".$site.") as sqfiyat ";
    }

    if ($kisaSureli){
        $dsql2="select dbo.Fn_yenifiyathesapla(kisasureli.tarih,kisasureli.tarih2,h.id,".$site.") as sqfiyat ";
        $ksqlx= " convert(varchar,kisasureli.tarih,104) as tarih1,convert(varchar,kisasureli.tarih2,104) as tarih2, ";
        $ksql= " cross apply(select top 1 tarih2 as tarih,dateadd(DAY,+(DATEDIFF(DAY,tarih2,(select min(d2.tarih) from dolu d2 where d2.emlak=dolu.emlak and (d2.durum=3) and d2.tarih>=dolu.tarih2))),tarih2) as tarih2 from dolu where emlak=h.id and (durum=3) and DATEDIFF(DAY,tarih2,(select min(d2.tarih) from dolu d2 where d2.emlak=dolu.emlak and (d2.durum=3 ) and d2.tarih>=dolu.tarih2)) in (".$geceSql.") and convert(date,tarih,103)>=convert(date,getdate(),103) and convert(date,tarih2,103)>=convert(date,getdate(),103) ) as kisasureli ";
    }

    $dsqlx=" indirimler.oran as indirim,indirimler.tarih1 as it1,indirimler.tarih2 as it2, convert(varchar,indirimler.tarih1,104)+' ile '+convert(varchar,indirimler.tarih2,104)+' tarihleri arasına şimdi rezervasyon yaparsanız %'+convert(varchar,indirimler.oran)+' indirimden yararlanabilirsiniz.' as indirimMsg, ";
    $dsql=" cross apply (select top 1 max(i.oran) as oran,max(i.tarih1) as tarih1,max(i.tarih2) tarih2 from indirimler i where i.emlak=h.id and convert(date,getdate(),104)<=convert(date,i.tarih1,104) and (('".$date2."' between i.tarih1 and i.tarih2) or ('".$date1."' between i.tarih1 and i.tarih2) or (i.tarih1 between '".$date1."' and '".$date2."') or (i.tarih2 between '".$date1."' and '".$date2."'))) as indirimler ";
}else{
    $dsqlx=" indirimler.oran as indirim,indirimler.tarih1 as it1,indirimler.tarih2 as it2, convert(varchar,indirimler.tarih1,104)+' ile '+convert(varchar,indirimler.tarih2,104)+' tarihleri arasına şimdi rezervasyon yaparsanız %'+convert(varchar,indirimler.oran)+' indirimden yararlanabilirsiniz.' as indirimMsg, ";
    $dsql=" cross apply (select top 1 max(i.oran) as oran,max(i.tarih1) as tarih1,max(i.tarih2) tarih2 from indirimler i where i.emlak=h.id and convert(date,getdate(),104)<=convert(date,i.tarih1,104) and i.id=isnull((select top 1 ix.id from indirimler ix where ix.emlak=h.id and convert(date,getdate(),104)<=convert(date,ix.tarih1,104) order by tarih1 asc),0) ) as indirimler ";

    $dsql2="select isnull((select min(fiyat) from sezonlar where site=".$site." and islem='emlak' and islem_id=h.id and not fiyat=0),0) as minfiyat,isnull((select max(fiyat) from sezonlar where site=".$site." and islem='emlak' and islem_id=h.id and not fiyat=0),0) as maxfiyat ";
    $dsql2x=" fiyatlar.minfiyat as minfiyat,fiyatlar.maxfiyat as maxfiyat, ";

}

if ($sondakika){
    $sSql=" cross apply(select top 1 id,replace(tarih1,'/','.') as tarih1,replace(tarih2,'/','.') as tarih2,fiyat,datediff(day,convert(date,tarih1,104),convert(date,tarih2,104)) as gece,convert(varchar,day(convert(date,tarih1,104))) as gun,convert(varchar,datename(month,convert(date,tarih1,104))) as ay from sonDakika where islem_id=h.id and convert(date,getdate(),104)<=convert(date,tarih1,104)  and site=".$site." order by convert(date,tarih1,104) ) as sonDakika ";
    $sSqlx=" sonDakika.fiyat,h.id,sonDakika.gece,sonDakika.gun,sonDakika.ay,sondakika.tarih1,sondakika.tarih2,sondakika.id as sid,  ";
    if($tarihli!=true){
        $dsql2="select sondakika.fiyat as minfiyat,sondakika.fiyat as maxfiyat ";
        $dsql2x=" fiyatlar.minfiyat as minfiyat,fiyatlar.maxfiyat as maxfiyat, ";
    }
}

$bolge = get("bolge");
$ozellik=get("ozellik");
$kodara = get("kodara");
$yetiskin = get("yetiskin");
$cocuk = get("cocuk");
$yatak_odasi = get("yatak_odasi");
$searchguest=get("searchguest");
$kisi=0;

if ($yetiskin)
    $kisi+=$yetiskin;
if ($cocuk)
    $kisi+=$cocuk;



$countSelect = "select count(h.id) as totalCount ";
$normalSelect = "select h.id,
h.ribbon".$uzanti." as ribbon,
h.doviz".$uzanti." as doviz,
h.kisi,h.yatak_odasi,h.banyo,h.title".$uzanti." as title ,
h.url".$uzanti." as url,dbo.FnRandomSplit(h.resim,',') as image,
".$sSqlx."
".$dsql2x."
".$dsqlx."
".$ksqlx."
h.baslik".$uzanti." as name,d1.baslik+' / '+d2.baslik as destination ";



$sql = " from homes h
INNER JOIN rate ON rate.CurrencyName=h.doviz".$uzanti."
".$sSql."
".$dsql."
".$ksql."
cross apply(".$dsql2.") as fiyatlar
INNER JOIN tip t on t.id=h.emlak_tipi
inner join destinations d2 on d2.id = h.emlak_bolgesi
inner join destinations d1 on d1.id = d2.cat
inner join destinations d0 on d0.id = d1.cat
where h.aktif".$uzanti."=1 and d2.aktif=1 and d1.aktif=1 and t.aktif=1 ";

if($tarihli){
    $sql.=" and not fiyatlar.sqfiyat=0 ";
}else{
    $sql.=" and not fiyatlar.minfiyat=0 ";
}

if($yearVillas!=""){
    $sql=$sql&" and isnull((select max(year(convert(date,tarih1,103))) from sezonlar where islem='emlak' and site=".$site." and islem_id=h.id and convert(date,tarih2,103)>=convert(date,getdate(),103) and year(convert(date,tarih1,103)) in (".$yearVillas.")),'')!='' ";
}


// Tipe Göre
if(get("CurrentRoutingTypeId")=="ProductCategory"){
    $tip = get("EntityId");
}else{
    $tip = get("tip");
}

if($tip){

    $arr = explode(",",$tip);
    if (count($arr)>1){
        $sqlekle=" and (1=2 ";
        foreach ($arr as $item){
            $sqlekle.="or ( not isnull((select tt2.cat from tip tt2 where tt2.id=".$item."),0)=0 and (','+replace(h.kategori,' ','')+',' like '%,".trim($item).",%' or h.emlak_tipi=".$item.")  ) ";
        }
        $sqlekle.=")";
        $sql.=$sqlekle;
    }else{
        $sql.=" and (','+replace(h.kategori,' ','')+',' like '%,".$tip.",%' or h.emlak_tipi=".$tip.") ";
    }
}
// Tipe Göre

if(get("CurrentRoutingTypeId")=="ProductDestination"){
    $sql.=" and ".get("EntityId")." in (d2.id,d1.id) ";
}else {
    $bolge = get("bolge");
}
if ($bolge){
    $arr = explode(",",$bolge);
    if (count($arr)>1) {
        $sql.=" and (d1.id in (".implode(",",$arr).") or d2.id in (".implode(",",$arr)."))";
    }else{
        $sql.=" and ".$bolge." in (d2.id,d1.id,d0.id) ";
    }
}







if(get("start") && get("end")){
    $date=date_create(get("start"));
    $date2=date_create(get("end"));
    $date->modify('+1 day');
    $date2->modify('-1 day');
    $startDate = date_format($date,"Y-m-d");
    $endDate = date_format($date2,"Y-m-d");

    if ($kisaSureli!=true){
        $sql=" and (select top 1 count(dolu.id) from dolu where dolu.emlak=h.id and dolu.durum=3 and (('".$endDate."' between dolu.tarih and dolu.tarih2) or ('".$startDate."' between dolu.tarih and dolu.tarih2) or (dolu.tarih between '".$startDate."' and '".$endDate."') or (dolu.tarih2 between '".$startDate."' and '".$endDate."')))=0 ";
        if ($sondakika){
            $sql.=" and  sondakika.tarih1='".$startDate."'  and  sondakika.tarih2='".$endDate."' ";
        }
    }else{
        $sql.=" and  (('".$endDate."' between kisasureli.tarih and kisasureli.tarih2) or ('".$startDate."' between kisasureli.tarih and kisasureli.tarih2) or (kisasureli.tarih between '".$startDate."' and '".$endDate."') or (kisasureli.tarih2 between '".$startDate."' and '".$endDate."')) ";
    }
    $sql.=" and (select top 1 count(dolu.id) from dolu where dolu.emlak=h.id and dolu.durum=3 and (('".$endDate."' between dolu.tarih and dolu.tarih2) or ('".$startDate."' between dolu.tarih and dolu.tarih2) or (dolu.tarih between '".$startDate."' and '".$endDate."') or (dolu.tarih2 between '".$startDate."' and '".$endDate."')))=0";
}


//Kişi Sayısına Göre
if($kisi){
    $ExecuteArray["kisi"]=$kisi;
    $sql.=" and (h.kisi>=:kisi)";
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
            $sql.=" and fiyatlar.sqfiyat*rate.rate between ".$min." and ".$max;
        }else{
            $sql.=" and (fiyatlar.minfiyat)*rate.rate between ".$min." and ".$max;
        }
    }
}

if (get("indirimliVillalar")=="1" || $indirimli==true){
    $sql.=" and indirimler.oran>0  ";
}

if (get("ids")){
    $sql.=" and h.id in ("&str_replace("%2C","",get("ids")).")";
}


if(get("sql")=="1"){
    echo $countSelect.$sql;
    print_r($ExecuteArray);
    exit;
}

$totalRecord =$db->prepare($countSelect.$sql);
$totalRecord->execute($ExecuteArray);
$totalRecord=$totalRecord->fetch(PDO::FETCH_ASSOC)["totalCount"];

$arr = $db->prepare($normalSelect.$sql." order by h.siralama asc ,h.id desc OFFSET $start ROWS FETCH NEXT $perPage ROWS ONLY");
$arr->execute($ExecuteArray);
$json["result"] = $arr->fetchAll(PDO::FETCH_ASSOC);
$json["CurrentPage"]=$page;
$json["TotalRecord"]=$totalRecord;
$json["TotalPage"]=(($totalRecord - ($totalRecord % $perPage)) / $perPage) + ($totalRecord % $perPage>0 ? 1 :0);


echo json_encode($json);

