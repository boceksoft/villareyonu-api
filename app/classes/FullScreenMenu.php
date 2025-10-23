<?php

class FullScreenMenu
{
    public static function GetAll(){
        global $db;
        //Fullscreen Footer Menü
        $sql = "select id,baslik as name,title,url,0 as nofollow from sayfalar".UZANTI." where aktif=1 and cat=2 order by siralama ";
        $query = $db->prepare($sql);
        $query->execute();
        $json["footerMenu"] = $query->fetchAll(PDO::FETCH_ASSOC);
//Fullscreen Footer Menü

//Fullscreen Butonlar
        $sql = "select id,baslik as name,title,url, case when id = 319 then 1 else 0 end as [primary] from sayfalar".UZANTI." where id in(12,319) order by siralama ";
        $query = $db->prepare($sql);
        $query->execute();
        $json["links"] = $query->fetchAll(PDO::FETCH_ASSOC);
//Fullscreen Butonlar

        $sql = "select id,baslik as name,title,url from sayfalar".UZANTI." where id in(10,317,14) order by baslik asc ";
        $query = $db->prepare($sql);
        $query->execute();
        $json["mainContent"] = $query->fetchAll(PDO::FETCH_ASSOC);

        $sql = "select id,baslik as name,url from sayfalar".UZANTI." where id in(15,8,2) order by siralama ";
        $query = $db->prepare($sql);
        $query->execute();
        $rows = $query->fetchAll(PDO::FETCH_ASSOC);

        $rows = array_map(function ($item) use ($db) {
            if ($item["id"] == "15") {
                $sql = "select top 10 id,baslik".UZANTI." as name,url".UZANTI." as url from tip where aktif=1 and favori".UZANTI."=1 order by siralama ";
                $query = $db->prepare($sql);
                $query->execute();
                $Temp = $item;
                $item["items"] = $query->fetchAll(PDO::FETCH_ASSOC);

                $Temp["name"]="Tümünü Göster";
                $item["items"][]=$Temp;


            } elseif ($item["id"] == "8") {
                $sql = "select id,baslik".UZANTI." as name,url".UZANTI." as url from destinations where aktif=1 and favori".UZANTI."=1 order by siralama ";
                $query = $db->prepare($sql);
                $query->execute();
                $Temp = $item;
                $item["items"] = $query->fetchAll(PDO::FETCH_ASSOC);
                $Temp["name"]="Tümünü Göster";
                $item["items"][]=$Temp;
            } else {
                $sql = "select id,baslik as name,url,0 as nofollow from sayfalar".UZANTI." where aktif=1 and cat={$item["id"]} order by siralama ";
                $query = $db->prepare($sql);
                $query->execute();
                $item["items"] = $query->fetchAll(PDO::FETCH_ASSOC);
            }
            return $item;
        }, $rows);


        $json["menu"] = $rows;

        $sql = "select id,kisa_baslik as baslik,'/'+url as url,title from sayfalar".UZANTI." where id in(8)
            union 
            select id,baslik".UZANTI.",'/'+url".UZANTI.",title".UZANTI." from tip where id in (29,1) order by id";
        $query = $db->prepare($sql);
        $query->execute();
        $rows = $query->fetchAll(PDO::FETCH_ASSOC);
        $json["header_left"] = $rows;


        $json["social"]["facebook"] = $qsql["facebook"];
        $json["social"]["youtube"] = $qsql["youtube"];
        $json["social"]["instagram"] = $qsql["instagram"];


        return $json;
    }
}