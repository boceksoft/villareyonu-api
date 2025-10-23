<?php

class Faq
{
    public static function GetByPageId($PageId,$top=100){
        global $db;
        $query = $db->prepare("select top $top id,baslik,icerik from faq where site=:site  and cat=:cat ");
        $query->execute(["site"=>SITE,"cat"=>$PageId]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function Index($Routing)
    {
        global $db;
        global $qsql;
        global $url;
        $query = $db->prepare("select id,title,baslik,left(description,250) as description,'/'+url as url,cat,replace(icerik,'../../../../..','https://cdn.villareyonu.com') as icerik, case when len(canonical) > 0 then  concat('{$qsql['domain']}/',canonical) else '{$qsql['domain']}/".implode("/",$url)."' end  as canonical from sayfalar where id=:id");
        $query->execute(["id"=>$Routing["EntityId"]]);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        $query = $db->prepare("select uploadID,filename from upload where islm='sayfa' and islm_id=:islm_id and site=".SITE." order by sira");
        $query->execute(["islm_id"=>$Routing["EntityId"]]);
        $result["_photos"]=$query->fetchAll(PDO::FETCH_ASSOC);

        $query = $db->prepare("select id,baslik,icerik from faq where cat=:islm_id and site=".SITE." order by siralama");
        $query->execute(["islm_id"=>$Routing["EntityId"]]);
        $result["_faq"]=$query->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
}