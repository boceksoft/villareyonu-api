<?php

class ProductCategory
{

    //Tipe Göre Listeleme
    public static function Index($Routing){
        global $db;
        global $qsql;
        global $url;

        $query = $db->prepare("select id,title".UZANTI." as title ,baslik".UZANTI." as baslik,left(description".UZANTI.",250) as description,kisa_baslik".UZANTI." as kisa_baslik,kapak,kisa_icerik".UZANTI." as kisa_icerik ,concat('/',url".UZANTI.") as url,icerik".UZANTI." as icerik,case when len(canonical) > 0 then  concat('{$qsql['domain']}/',canonical) else '{$qsql['domain']}/".implode("/",$url)."' end  as canonical  from tip where id=:id");
        $query->execute(["id"=>$Routing["EntityId"]]);
        $json=$query->fetch(PDO::FETCH_ASSOC);

        //Structural - Yapısal Veriler
        $Structural = new Structural($Routing,$json);
        $json["Structural"] = $Structural->Result();
        //Structural - Yapısal Veriler

        //Breadcrumb
        $Breadcrumb = new Breadcrumb($Routing,$json);
        $json["BreadCrumb"] = $Breadcrumb->Result();
        //Breadcrumb

        $json["Banners"]=Page::GetByCat(1432,"/");

        //Haftanın Villaları
        $First = Page::GetById(7,"/");
        $First["key"]="firsat";
        $HomeContent = [];
        if ($First["aktif"])
            $HomeContent[] = $First;

        $json["HomeContent"]=$HomeContent;

        $json=array_merge($json,$Routing);

        return $json;
    }

    //Bölgeye göre Listeleme
    public static function Destination($Routing){
        global $db;
        global $qsql;
        global $url;

        $query = $db->prepare("select id,cat,title".UZANTI." as title,baslik".UZANTI." as baslik,left(description".UZANTI.",250) as description,kisa_icerik".UZANTI." as kisa_icerik,url".UZANTI." as url,icerik".UZANTI." as icerik,case when len(canonical) > 0 then  concat('{$qsql['domain']}/',canonical) else '{$qsql['domain']}/".implode("/",$url)."' end  as canonical  from destinations where id=:id");
        $query->execute(["id"=>$Routing["EntityId"]]);
        $json = $query->fetch(PDO::FETCH_ASSOC);

        //Structural - Yapısal Veriler
        $Structural = new Structural($Routing,$json);
        $json["Structural"] = $Structural->Result();
        //Structural - Yapısal Veriler

        $json["Banners"]=Page::GetByCat(1432,"/");

        //Haftanın Villaları
        $First = Page::GetById(7,"/");
        $First["key"]="firsat";
        $HomeContent = [];
        if ($First["aktif"])
            $HomeContent[] = $First;

        $json["HomeContent"]=$HomeContent;

        $json=array_merge($json,$Routing);

        return $json;
    }

    //Sayfaya göre Listeleme
    public static function Page($Routing)
    {
        global $db;
        global $qsql;
        global $url;


        $query = $db->prepare("select id,title,baslik,cat,left(description,250) as description,kisa_icerik,url,icerik,case when len(canonical) > 0 then  concat('{$qsql['domain']}/',canonical) else '{$qsql['domain']}/".implode("/",$url)."' end  as canonical,kapak  from sayfalar".UZANTI." where id=:id");
        $query->execute(["id" => $Routing["EntityId"]]);
        $json = $query->fetch(PDO::FETCH_ASSOC);


        //Kısa Süreli Kiralıklar
        if ($Routing["EntityId"]=="9" && get("ay")){
            if(SITE==2){
                //2 Day Rental Villas in July
                $month_name = iconv('latin5','utf-8',strftime('%B',strtotime('2020-'.get("ay").'-19 14:57:22')));
                $json["title"]=get("gece")." Day Rental Villas in ".$month_name.", ".$qsql["siteadi"];
                $json["baslik"]=get("gece")." Day Rental Villas in ".$month_name;
                $json["description"]=$month_name." Ayı ".get("gece")." günlük villa kiralayın! En uygun fiyat teklifi ile, istediğiniz zamana uygun villa seçeneklerine göz atın.";
            }else{
                $month_name = iconv('latin5','utf-8',strftime('%B',strtotime('2020-'.get("ay").'-19 14:57:22')));
                $json["title"]=$month_name." Ayı ".get("gece")." Günlük Kiralık Villalar, ".$qsql["siteadi"];
                $json["baslik"]=$month_name." Ayı ".get("gece")." Günlük Kiralık Villalar";
                $json["description"]=$month_name." Ayı ".get("gece")." günlük villa kiralayın! En uygun fiyat teklifi ile, istediğiniz zamana uygun villa seçeneklerine göz atın.";
            }

            $json["ShortTerms"]=1;
        }


        //Structural - Yapısal Veriler
        $Structural = new Structural($Routing,$json);
        $json["Structural"] = $Structural->Result();
        //Structural - Yapısal Veriler

        $json["Banners"]=Page::GetByCat(1432,"/");

        //Breadcrumb
        $Breadcrumb = new Breadcrumb($Routing,$json);
        $json["BreadCrumb"] = $Breadcrumb->Result();
        //Breadcrumb

        //Haftanın Villaları
        $First = Page::GetById(7,"/");
        $First["key"]="firsat";
        $HomeContent = [];
        if ($First["aktif"])
            $HomeContent[] = $First;

        $json["HomeContent"]=$HomeContent;

        $json=array_merge($json,$Routing);

        return $json;

    }

    public static function GetById ($id) {
        global $db;

        $query = $db->prepare("select id,title".UZANTI." as title,baslik".UZANTI." as baslik,url".UZANTI." as url,concat('tip_',id) as [key] from tip where id=:id");
        $query->execute(["id"=>$id]);
        return $query->fetch(PDO::FETCH_ASSOC);

    }

    public static function GetFaq($PageId,$top=100){
        global $db;
        $query = $db->prepare("select top $top id,baslik,icerik from faq where site=:site and islm='tip' and cat=:cat ");
        $query->execute(["site"=>1,"cat"=>$PageId]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

}
