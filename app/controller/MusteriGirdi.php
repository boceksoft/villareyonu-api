<?php


SetHeader(200);
$json = [];

$ReservationNumber=get("ReservationNumber");
$query = $db->prepare("select * from safeStatus ss where ss.kayitId=:ReservationNumber");

//remove last 4 characters
$ReservationNumber2 = substr($ReservationNumber, 0, -4);
$query->execute([
    "ReservationNumber"=>$ReservationNumber2
]);

$safeStatus = $query->fetch(PDO::FETCH_ASSOC);
if ($safeStatus) {
    $json["success"] = 200;
}else{
   $query = $db->prepare("insert into safeStatus (kayitId,status) values (:ReservationNumber,2)");
   $insert = $query->execute([
       "ReservationNumber"=>$ReservationNumber2
   ]);
   if ($insert) {
       $json["success"] = "İşlem Başarılı";

       $sql = "select (select top 1 'Ad-Soyad: '+bakimciad+' Telefon: '+bakimcitel from homes as h where id=homes.id) as bakimci,kayitlar.id as kayitid,".
       "kayitlar.*,".
       "dateadd(day,1,kayitlar.rez_tarihi) as ta1,".
       "dateadd(day,-1,kayitlar.gelecek_tarih) as ta2,".
       "convert(varchar,kayitlar.rez_tarihi,104) as rez_tarihi,".
       "convert(varchar,kayitlar.gelecek_tarih,104) as gelecek_tarih,".
       "DATEDIFF(DAY,rez_tarihi,gelecek_tarih) as gece,".
       "destinations.baslik as bolge_baslik,".
       "d2.baslik as bolge_ust_baslik,".
       "dbo.FnRandomSplit(homes.resim,',') as resim, ".
       "homes.baslik as hbaslik, ".
       "kayitlar.musteri as musteri, ".
       "kayitlar.telefon as telefon, ".
       "homes.evsahibi ".
       "from homes ".
       "inner join kayitlar on kayitlar.evid=homes.id ".
       "inner join destinations on destinations.id=homes.emlak_bolgesi ".
       "inner join destinations as d2 on d2.id=destinations.cat ".
       "where :id=concat(kayitlar.id,(case when datepart(hour,kayitlar.islem_tarihi)<10 then '0' else '' end),datepart(hour,kayitlar.islem_tarihi),(case when datepart(minute,kayitlar.islem_tarihi)<10 then '0' else '' end),datepart(minute,kayitlar.islem_tarihi))";
       $query = $db->prepare($sql);
       $query->execute([
           "id"=>$ReservationNumber
       ]);
       $Reservation = $query->fetch(PDO::FETCH_ASSOC);
       $json["Reservation"] = $Reservation;

       $smsQuery = $db->prepare("select * from smssablon where id = 4");
       $smsQuery->execute();
       $sms = $smsQuery->fetch(PDO::FETCH_ASSOC);
       $CustomerInformationPage = Page::GetById(181, "/");
       if ($sms) {
           $mesaj = str_replace(
               ["{musteri}", "{bakimci}", "{giris-bilgilendirme}"], [
               $Reservation["musteri"],
               $Reservation["bakimci"],
               $qsql["domain"].$CustomerInformationPage["url"]."/".$ReservationNumber
           ], $sms["icerik"]);
           $json["sms"]=$mesaj;

           $a = new SmsSend();
           $data=array(
               'message'=>$mesaj,
               'no'=>[$Reservation["telefon"]],
               'header'=>$qsql["smsorg"],
               'filter'=>0,
               'encoding'=>'tr',
               'startdate'=>'',
               'stopdate'=>'',
               'bayikodu'=>'',
               'appkey'=>''
           );
           //$sms= new SmsSend;
           //$sms->smsGonder($data);



       }
   }else
       $json["error"] = "Bir sorun oluştu.";
}
echo json_encode($json);
