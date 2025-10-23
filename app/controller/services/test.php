<?php

$query = $db->prepare("select * from smsSablon where id=3");
$query->execute();
$sms = $query->fetch(PDO::FETCH_ASSOC);
echo str_replace(["{musteri}","{villaadi}","{tarih1}","{tarih2}","{rez_kodu}","{yetiskin}","{cocuk}"],[],$sms["icerik"]);



$a = new SmsSend();
$data=array(
    'message'=>'test mesajı',
    'no'=>['5416649080'],
    'header'=>$qsql["smsorg"],
    'filter'=>0,
    'encoding'=>'tr',
    'startdate'=>'',
    'stopdate'=>'',
    'bayikodu'=>'',
    'appkey'=>''
);
$sms= new SmsSend;
//$cevap=$sms->smsGonder($data);
