<?php

class Category
{
    public static function Index($Routing)
    {
        global $db;
        global $qsql;
        global $url;

        $query = $db->prepare("select id,title,baslik,url,left(description,250) as description,icerik,case when len(canonical) > 0 then  concat('{$qsql['domain']}/',canonical) else '{$qsql['domain']}/".implode("/",$url)."' end  as canonical from sayfalar".UZANTI." where id=:id");
        $query->execute(["id" => $Routing["EntityId"]]);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        $query = $db->prepare("select id,baslik".UZANTI." as baslik,'/'+url".UZANTI." as url,resim,title".UZANTI." as title from tip where aktif=1  order by siralama");
        $query->execute();
        $result["_category"]=array_map(function($item){
            $row = new Query();
            $row->setQuery("count");
            $row->addParam("and (','+replace(h.kategori,' ','')+',' like '%,".$item["id"].",%' or h.emlak_tipi=".$item["id"].")");
            $a = $row->Run()[0];
            $item["total"]=$a["total"];
            return $item;
        },$query->fetchAll(PDO::FETCH_ASSOC));

        //Haftanın Villaları
        $First = Page::GetById(7,"/");
        $First["key"]="firsat";
        $HomeContent = [];
        if ($First["aktif"])
            $HomeContent[] = $First;

        $result["HomeContent"]=$HomeContent;

        //Structural - Yapısal Veriler
        $Structural = new Structural($Routing,$result);
        $result["Structural"] = $Structural->Result();
        //Structural - Yapısal Veriler

        //Breadcrumb
        $Breadcrumb = new Breadcrumb($Routing,$result);
        $result["BreadCrumb"] = $Breadcrumb->Result();
        //Breadcrumb


        return $result;
    }
    public static function Destination($Routing)
    {
        global $db;
        global $qsql;
        global $url;
        global $config;


        $query = $db->prepare("select id,title,baslik,url,left(description,250) as description,icerik,case when len(canonical) > 0 then  concat('{$qsql['domain']}/',canonical) else '{$qsql['domain']}/".implode("/",$url)."' end  as canonical from sayfalar".UZANTI." where id=:id");
        $query->execute(["id" => $Routing["EntityId"]]);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        $row = new Query();
        $row->setQuery("count");
        $row->addParam("and dust.id in (d1.id,d2.id,d3.id)");

        $query = $db->prepare("select dust.id,dust.baslik".UZANTI." as baslik,'/'+dust.url".UZANTI." as url,dust.icerik,dust.kisa_icerik, dust.resim,dust.title".UZANTI." as title from destinations as dust where dust.aktif=1 and dust.favori=1   order by dust.siralama");
        //$query->execute([
        //    "RateId"=>Rate::GetLastRate(),
        //    "DefaultCurrencyId"=>$config["DefaultCurrencyId"]
        //]);

        $query->execute();

        //$result["_category"]=$query->fetchAll(PDO::FETCH_ASSOC);
       $result["_category"]=array_map(function($item){
           $row = new Query();
           $row->setQuery("count");
           $row->addParam("and ".$item["id"]." in (d1.id,d2.id,d3.id)");
           $a = $row->Run()[0];
           $item["total"]=$a["total"];
           return $item;
       },$query->fetchAll(PDO::FETCH_ASSOC));

        //Structural - Yapısal Veriler
        $Structural = new Structural($Routing,$result);
        $result["Structural"] = $Structural->Result();
        //Structural - Yapısal Veriler

        //Breadcrumb
        $Breadcrumb = new Breadcrumb($Routing,$result);
        $result["BreadCrumb"] = $Breadcrumb->Result();
        //Breadcrumb


        if(!is_dir(PATH."/app/cache/Blank"))
            mkdir(PATH."/app/cache/Blank");


        $myfile = fopen(realpath(".")."/app/cache/Blank/".$Routing["Slug"].".json", "w");
        $Routing["result"]=$result;
        fwrite($myfile, json_encode($Routing));
        fclose($myfile);



        return $result;
    }
}