<?php

class Home
{
    public static function Index($Routing){
        global $db;
        global $qsql;

        $query = $db->prepare("select id,baslik,title,icerik,left(description,250) as description,video,video_kapak from sayfalar".UZANTI." where id=:id");
        $query->execute(["id"=>$Routing["EntityId"]]);
        $result = $query->fetch(PDO::FETCH_ASSOC);


        $query = $db->prepare("
            select t.baslik".UZANTI." as baslik,t.id,
            t.icon as icon, t.resim as resim,
            '/'+t.url".UZANTI." as url, 
            t.title".UZANTI." as title,r.* from tip t 
            inner join Routing r on r.EntityId=t.id and r.RoutingTypeId='ProductCategory' and r.site=1 
            where t.aktif=1 and t.favori=1 order by t.siralama asc
            ");
        $query->execute();

        $result["ProductCategories"]=array_map(function($item){
            //$row = new Query();
            //if($item["id"]=="29"){
            //    $row->setQuery("count");
            //    $row->addParam("and isnull((select max(year(convert(date,tarih1,103))) from sezonlar where islem='emlak' and site=".PRICE_SITE." and islem_id=h.id and convert(date,tarih2,103)>=convert(date,getdate(),103) and year(convert(date,tarih1,103)) in (2024)),'')!=''");
            //}else{
            //    $row->setQuery("count");
            //    $row->addParam("and (','+replace(h.kategori,' ','')+',' like '%,".$item["id"].",%' or h.emlak_tipi=".$item["id"].")");
            //}
            //$a = $row->Run()[0];
            //$item["total"]=$a["total"];
            return $item;
        },$query->fetchAll(PDO::FETCH_ASSOC));

        //Structural - Yapısal Veriler
        $Structural = new Structural($Routing,$result);
        $result["Structural"] = $Structural->Result();
        //Structural - Yapısal Veriler

        $result["canonical"]=$qsql["domain"];

        if (SITE == 2){
            $result["faq"]["data"] = Faq::GetByPageId(289,5);
            $result["faq"]["page"] = Page::GetById(289);
        }else{
            $result["faq"]["data"] = Faq::GetByPageId(328,5);
            $result["faq"]["page"] = Page::GetById(328);
        }

        $sql = "select filename,aciklama,aciklama2,aciklama3 from upload where islm='slider' and site=".SITE." order by sira";
        $query = $db->prepare($sql);
        $query->execute();
        $result["slider"] = $query->fetchAll(PDO::FETCH_ASSOC);

        $result["blog"]["page"] = Page::GetById(10);

        $query = $db->prepare("SET LANGUAGE Turkish;select top 3 id,title,baslik,FORMAT(tarih,'dd MMM yy') as tarih,resim,'/".$result["blog"]["page"]["url"]."/'+url as url,aktif,kisa_icerik,kisa_baslik from sayfalar".UZANTI." where (cat=10 or blog=1) and aktif=1 order by id desc");
        $query->execute();

        $result["blog"]["data"] = $query->fetchAll(PDO::FETCH_ASSOC);


        $result["modal"] = ProductCategory::GetById(14);
        $result["discounted_villa"] = Product::get_todays_discounted_product();


        //Haftanın Villaları
        if (SITE == 2){
            $First = Page::GetById(591,"/");
        }else{
            $First = Page::GetById(498,"/");
        }

        $First["key"]="vitrin";

        //Sizin İçin Seçtiklerimiz
        if (SITE == 2){
            $Second = Page::GetById(36,"/");
        }else{
            $Second = Page::GetById(346,"/");
        }
        $Second["key"]="favori";


        //Hemen kiralanabilir Villalar
        //$Third = Page::GetById(17,"/");
        //$Third["key"]="BookableDirectly";

        $HomeContent = [];



        if ($First["aktif"])
            $HomeContent[] = $First;

        if ($Second["aktif"])
            $HomeContent[] = $Second;

        $query = $db->prepare("select id,baslik".UZANTI." as baslik,title".UZANTI." as title,url,concat('tip_',id) as [key],kisa_icerik".UZANTI." as kisa_icerik from tip where aktif=1 and page_tab_bit".UZANTI."=1 order by siralama");
        $query->execute();
        $ProductTypes = $query->fetchAll(PDO::FETCH_ASSOC);

        foreach ($ProductTypes as $p){
            $HomeContent[] = $p;
        }


        $query = $db->prepare("select s.id,s.title,s.baslik,'/'+s.url as url,s.aktif,s.kisa_icerik,s.kisa_baslik from dbo.CustomList C inner join sayfalar".UZANTI." s on s.id = C.PageId where s.cat=:id and s.aktif=1");
        $query->execute(["id"=>391]);
        foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $item){
            $item["key"]="CustomList_".$item["id"];
            $HomeContent[] = $item;
        }




        $result["HomeContent"]=$HomeContent;



        $result["All"]=ProductCategory::GetById(2,"/");

        $result["static"]["GetOffer"] = Page::GetById("13","/");
        $result["static"]["Reviews"] = Page::GetById("11","/");
        $result["static"]["Reviews"]["data"] = Reviews::GetHomeReviews();
        $result["static"]["Destinations"] = Page::GetById("8","/");
        $popup = Page::GetById("1355", '/');

        if ($popup["aktif"])
        {
            $popup['icerik'] = str_replace('../../../../../', 'https://web.villareyonu.com/', $popup['icerik']);
            $result["static"]["PopUp"] = $popup;
        }



        $result["Banners"]=Page::GetByCat(496,"/");

        $result["rez_tel"] = $qsql["rez_tel"];


        $result["social"]["facebook"]=$qsql["facebook"];
        $result["social"]["youtube"]=$qsql["youtube"];
        $result["social"]["instagram"]=$qsql["instagram"];

        $query = $db->prepare("
        select top 10 d.baslik".UZANTI." as baslik,d.id,
         (select count(h.id) as total
        from homes h
                 inner join destinations d3 on d3.id = h.emlak_bolgesi
                 inner join destinations d2 on d2.id = d3.cat
                 inner join destinations d1 on d1.id = d2.cat
                 inner join rate r on r.CurrencyName = h.doviz
                 left join kanun7464 kanun on kanun.homeId = h.id
                 inner join tip t on t.id = h.emlak_tipi
        where h.aktif = 1
          and d3.aktif = 1
          and isnull(kanun.gavel, 0) = 0
          and d2.aktif = 1
          and t.aktif = 1
          and d1.aktif = 1
          and d.id in (d1.id, d2.id, d3.id))                              as emlakCount,
        replace(replace(replace(isnull(d.resim".UZANTI.",''),' ','-'),'ı','i'),N'ş','s') as resim,
        r.EntityId,
        case when d1.baslik is not null then d1.baslik else d2.baslik end as ust_baslik,
        r.RoutingTypeId,
        concat('/',d.url".UZANTI.") as url,
        d.title".UZANTI." as title from destinations d
                INNER JOIN Routing r on r.EntityId= d.id and r.RoutingTypeId = 'ProductDestination' and site = ".SITE."
                LEFT JOIN destinations d2 ON d2.id = d.cat
                LEFT JOIN destinations d1 ON d1.id = d2.cat
         where d.favori".UZANTI."=1 and  d.aktif=1  order by d.siralama asc
        ");
        $query->execute();

        $destinations = $query->fetchAll(PDO::FETCH_ASSOC);


        $result["ProductDestinations"]=array_map(function($item){
            $row = new Query();
            $row->setTop(1);
            $row->setQuery("Product");
            $row->addParam("and ".$item["id"]." in (d3.id,d2.id,d1.id)");
            $row->addOrderBy("fiyatlar.minfiyat*RD.Buy asc");
            $a = $row->Run()[0];
            $item["Product"]=$a;

            return $item;
        },$destinations);



        return $result;
    }
}