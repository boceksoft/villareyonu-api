<?php

class Rate
{
    public static function GetLastRate(){
        global $db;
        $query = $db->prepare("select max(RateId) as RateId from Finance.Rate");
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC)["RateId"];
    }


    public static function Exchange($Val=1,$FromCurrencyId,$ToCurrencyId){
        global $db;

        $query = $db->prepare("Select :Val * RD.Buy as Result from Finance.RateDetail RD 
            WHERE RateId=:RateId and FromCurrencyId=:FromCurrencyId and ToCurrencyId=:ToCurrencyId");
        $query->execute([
            "RateId"=>Rate::GetLastRate(),
            "Val"=>$Val,
            "FromCurrencyId"=>$FromCurrencyId,
            "ToCurrencyId"=>$ToCurrencyId

        ]);
        return DefaultFormatPrice($query->fetch(PDO::FETCH_ASSOC)["Result"]);



    }


}