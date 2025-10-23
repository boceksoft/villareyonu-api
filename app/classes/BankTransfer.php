<?php

class BankTransfer
{
    public static function BankTransferPage($Routing){
        global $db;
        global $qsql;
        global $url;
        $query = $db->prepare("select id,title,baslik,left(description,250) as description,url,icerik, case when len(canonical) > 0 then  concat('{$qsql['domain']}/',canonical) else '{$qsql['domain']}/".implode("/",$url)."' end  as canonical from sayfalar where id=:id");
        $query->execute(["id"=>$Routing["EntityId"]]);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        $query  = $db->prepare("select * from hesaplar where aktif=1 and kullanici=0 order by banka");
        $query->execute();
        $result["_banks"] = $query->fetchAll(PDO::FETCH_ASSOC);

        $query  = $db->prepare("select * from Finance.Currency  order by SortOrder");
        $query->execute();
        $result["_currencies"] = $query->fetchAll(PDO::FETCH_ASSOC);


        return $result;

    }
}