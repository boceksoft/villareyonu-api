<?php

 class DailyVillaPromotion
{


    public static function GetByDate($EntityId){

        $PromotionDate = date("Y-m-d");

        global $db;
        $query = $db->prepare("select * from daily_villa_promotions where promotion_date=:promotion_date and villa_id=:villa_id");
        $query->execute(["villa_id"=>$EntityId,"promotion_date"=>$PromotionDate]);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result;


    }
}