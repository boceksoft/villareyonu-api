<?php

class Page
{

    public static function Mapping($id)
    {

        //Sayfalar tablosunda ki id lerin , sayfalar_s2 tablosunda ki karşılıkları
        if(SITE==2){
            $arr = [
                35=>28,
                39=>29,
                162=>30,
                173=>31,
                289=>32,
                294=>33,
                318=>34,
                321=>35,
                346=>36,
                352=>37,
                391=>38,
                1355=>39,
                1432=>40,
                1433=>41,
                1434=>42,
                1435=>43,
                1518=>114,
                1519=>115
            ];
            if(isset($arr[$id])){
                return $arr[$id];
            }
        }

        return $id;
    }

    public static function GetById ($id,$urlEk=null) {
        global $db;
        $id = self::Mapping($id);
        $query = $db->prepare("select s.id,s.title, isnull(s.resim, '') as resim, s.baslik,'$urlEk'+s.url as url,s.aktif,s.kisa_icerik,s.kisa_baslik, s.icerik, Routing.RoutingId from sayfalar".UZANTI." as s left join Routing on s.id=Routing.EntityId  and Routing.Site=".SITE." left join RoutingType on RoutingType.RoutingTypeId=Routing.RoutingTypeId and RoutingType.RoutingController='Page' where id=:id");
        $query->execute(["id"=>$id]);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        if (!$result){
            return null;
        }
        foreach ($result as $key => &$value) {
            $result[$key] = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return $result;


    }
    public static function GetByCat ($id,$urlEk=null) {
        global $db;
        $id = self::Mapping($id);
        $query = $db->prepare("select id,title,baslik,'$urlEk'+url as url,aktif,kisa_icerik,kisa_baslik from sayfalar".UZANTI." where cat=:id and aktif=1");
        $query->execute(["id"=>$id]);
        return $query->fetchAll(PDO::FETCH_ASSOC);

    }
    public static function Detail($Routing){
        global $db;
        global $qsql;
        global $url;



        $query = $db->prepare("select id,title,isnull(resim, '') as resim, kapak,baslik,left(description,250) as description,'/'+url as url,cat,replace(icerik,'../../../../..','https://cdn.villareyonu.com') as icerik, case when len(canonical) > 0 then  concat('{$qsql['domain']}/',canonical) else '{$qsql['domain']}/".implode("/",$url)."' end  as canonical from sayfalar".UZANTI." where id=:id");
        $query->execute(["id"=>$Routing["EntityId"]]);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        $query = $db->prepare("select uploadID,filename from upload where islm='sayfa' and islm_id=:islm_id and site=1 order by sira");
        $query->execute(["islm_id"=>$Routing["EntityId"]]);
        $result["_photos"]=$query->fetchAll(PDO::FETCH_ASSOC);

        $query = $db->prepare("select id,baslik,icerik from faq where islm='sayfa' and cat=:islm_id and site=".SITE." order by siralama");
        $query->execute(["islm_id"=>$Routing["EntityId"]]);
        $result["_faq"]=$query->fetchAll(PDO::FETCH_ASSOC);

        $query = $db->prepare("select id,title,resim,baslik,left(description,250) as description,url,replace(icerik,'../../../../..','https://cdn.villareyonu.com') as icerik, case when len(canonical) > 0 then  concat('{$qsql['domain']}/',canonical) else '{$qsql['domain']}/".implode("/",$url)."' end  as canonical from sayfalar".UZANTI." where not blog=1 and aktif=1 and cat=:cat");
        $query->execute(["cat"=>$result["cat"]=="0" ? $Routing["EntityId"] : $result["cat"] ]);
        $result["_pages"]=$query->fetchAll(PDO::FETCH_ASSOC);

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
    public static function About($Routing){
        global $db;
        global $qsql;
        global $url;
        $query = $db->prepare("select id,title,kisa_icerik,baslik,kapak,resim,left(description,250) as description,'/'+url as url,cat,replace(icerik,'../../../../..','https://cdn.villareyonu.com') as icerik, case when len(canonical) > 0 then  concat('{$qsql['domain']}/',canonical) else '{$qsql['domain']}/".implode("/",$url)."' end  as canonical from sayfalar where id=:id");
        $query->execute(["id"=>$Routing["EntityId"]]);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        $query = $db->prepare("select uploadID,filename from upload where islm='sayfa' and islm_id=:islm_id and site=1 order by sira");
        $query->execute(["islm_id"=>$Routing["EntityId"]]);
        $result["_photos"]=$query->fetchAll(PDO::FETCH_ASSOC);

        $query = $db->prepare("select id,baslik,icerik from faq where islm='sayfa' and cat=:islm_id and site=1 order by siralama");
        $query->execute(["islm_id"=>$Routing["EntityId"]]);
        $result["_faq"]=$query->fetchAll(PDO::FETCH_ASSOC);

        $query = $db->prepare("select id,title,resim,baslik,left(description,250) as description,url,replace(icerik,'../../../../..','https://cdn.villareyonu.com') as icerik, case when len(canonical) > 0 then  concat('{$qsql['domain']}/',canonical) else '{$qsql['domain']}/".implode("/",$url)."' end  as canonical from sayfalar where not blog=1 and aktif=1 and cat=:cat");
        $query->execute(["cat"=>$result["cat"]=="0" ? $Routing["EntityId"] : $result["cat"] ]);
        $result["_pages"]=$query->fetchAll(PDO::FETCH_ASSOC);

        $query = $db->prepare("select id,resim,isim,gorev".DILUZANTI." as gorev,mail from team");
        $query->execute();
        $result["_team"]=$query->fetchAll(PDO::FETCH_ASSOC);
        if(!$result["_team"]){
            $result["_team"] = [
                [
                    "id"=>1,
                    "resim"=>'deneme.jpg',
                    "isim"=>'Test İsim',
                    "gorev"=>'Görev Test',
                ],
                [
                    "id"=>2,
                    "resim"=>'deneme.jpg',
                    "isim"=>'Test İsim',
                    "gorev"=>'Görev Test',
                ],
                [
                    "id"=>3,
                    "resim"=>'deneme.jpg',
                    "isim"=>'Test İsim',
                    "gorev"=>'Görev Test',
                ]
            ];
        }

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
    public static function BankAccounts($Routing){
        global $db;
        global $url;

        $query = $db->prepare("select id,title,baslik,url,cat,left(description,250) as description,'' as icerik,case when len(canonical) > 0 then  concat('{$qsql['domain']}/',canonical) else '{$qsql['domain']}/".implode("/",$url)."' end  as canonical from sayfalar where id=:id");
        $query->execute(["id"=>$Routing["EntityId"]]);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        $query = $db->prepare("select * from hesaplar where kullanici=0 and aktif=1 and not banka='Banka [Bilinmiyor]' order by siralama");
        $query->execute();
        $result["_bankAccounts"]=$query->fetchAll(PDO::FETCH_ASSOC);

        $query = $db->prepare("select id,title,resim,baslik,left(description,250) as description,url,replace(icerik,'../../../../..','https://cdn.villareyonu.com') as icerik, case when len(canonical) > 0 then  concat('{$qsql['domain']}/',canonical) else '{$qsql['domain']}/".implode("/",$url)."' end  as canonical from sayfalar where not blog=1 and aktif=1 and cat=:cat");
        $query->execute(["cat"=>$result["cat"]=="0" ? $Routing["EntityId"] : $result["cat"] ]);
        $result["_pages"]=$query->fetchAll(PDO::FETCH_ASSOC);

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
    public static function OurTeam($Routing){
        global $db;
        global $url;

        $query = $db->prepare("select id,title,baslik,url,cat,left(description,250) as description,icerik,case when len(canonical) > 0 then  concat('{$qsql['domain']}/',canonical) else '{$qsql['domain']}/".implode("/",$url)."' end  as canonical from sayfalar where id=:id");
        $query->execute(["id"=>$Routing["EntityId"]]);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        //Structural - Yapısal Veriler
        $Structural = new Structural($Routing,$result);
        $result["Structural"] = $Structural->Result();
        //Structural - Yapısal Veriler

        $query = $db->prepare("select id,resim,isim,gorev".DILUZANTI." as gorev,mail from team ");
        $query->execute();
        $result["_team"]=$query->fetchAll(PDO::FETCH_ASSOC);
        if(!$result["_team"]){
            $result["_team"] = [
                [
                    "id"=>1,
                    "resim"=>'deneme.jpg',
                    "isim"=>'Test İsim',
                    "gorev"=>'Görev Test',
                ],
                [
                    "id"=>2,
                    "resim"=>'deneme.jpg',
                    "isim"=>'Test İsim',
                    "gorev"=>'Görev Test',
                ],
                [
                    "id"=>3,
                    "resim"=>'deneme.jpg',
                    "isim"=>'Test İsim',
                    "gorev"=>'Görev Test',
                ]
            ];
        }

        //Breadcrumb
        $Breadcrumb = new Breadcrumb($Routing,$result);
        $result["BreadCrumb"] = $Breadcrumb->Result();
        //Breadcrumb

        return $result;
    }
    public static function Reviews($Routing){
        global $url;
        global $db;
        $query = $db->prepare("select id,title,baslik,url,left(description,250) as description,icerik,case when len(canonical) > 0 then  concat('{$qsql['domain']}/',canonical) else '{$qsql['domain']}/".implode("/",$url)."' end  as canonical from sayfalar where id=:id");
        $query->execute(["id"=>$Routing["EntityId"]]);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        $page = get("page") ?? 1;
        $result["_reviews"]=Reviews::GetAllReviews($page);

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
    public static function StaticPage($Routing){
        global $db;
        global $url;
        $query = $db->prepare("select id,title,baslik,url,left(description,250) as description,icerik,case when len(canonical) > 0 then  concat('{$qsql['domain']}/',canonical) else '{$qsql['domain']}/".implode("/",$url)."' end  as canonical from sayfalar where id=:id");
        $query->execute(["id"=>$Routing["EntityId"]]);
        $result = $query->fetch(PDO::FETCH_ASSOC);

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
    public static function DiscountDays($Routing){
        global $db;
        global $url;
        $query = $db->prepare("select id,title,baslik,url,left(description,250) as description,icerik,case when len(canonical) > 0 then  concat('{$qsql['domain']}/',canonical) else '{$qsql['domain']}/".implode("/",$url)."' end  as canonical from sayfalar where id=:id");
        $query->execute(["id"=>$Routing["EntityId"]]);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        //Structural - Yapısal Veriler
        $Structural = new Structural($Routing,$result);
        $result["Structural"] = $Structural->Result();
        //Structural - Yapısal Veriler

        $result["allVillas"]=ProductCategory::GetById(2);

        //Breadcrumb
        $Breadcrumb = new Breadcrumb($Routing,$result);
        $result["BreadCrumb"] = $Breadcrumb->Result();
        //Breadcrumb

        return $result;
    }
    public static function GetOffer($Routing){
        global $db;
        global $url;

        $query = $db->prepare("select id,title,baslik,url,left(description,250) as description,icerik,case when len(canonical) > 0 then  concat('{$qsql['domain']}/',canonical) else '{$qsql['domain']}/".implode("/",$url)."' end  as canonical from sayfalar where id=:id");
        $query->execute(["id"=>$Routing["EntityId"]]);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        //Structural - Yapısal Veriler
        $Structural = new Structural($Routing,$result);
        $result["Structural"] = $Structural->Result();
        //Structural - Yapısal Veriler

        //Breadcrumb
        $Breadcrumb = new Breadcrumb($Routing,$result);
        $result["BreadCrumb"] = $Breadcrumb->Result();
        //Breadcrumb

        $query = $db->prepare("select id,baslik,title,icon,t.ozelGun,t.aile, t.resim from tip t where t.aktif=1 order by t.siralama");
        $query->execute();
        $result["Categories"]=$query->fetchAll(PDO::FETCH_ASSOC);

        $query = $db->prepare("select d.id, d.baslik, d.resim from destinations d where d.aktif = 1 and d.cat = 0 ");
        $query->execute();
        $result["Regions"]=$query->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    public static function Advertise($Routing){
        global $db;
        global $url;

        $query = $db->prepare("select id,title,baslik,url,left(description,250) as description,icerik,case when len(canonical) > 0 then  concat('{$qsql['domain']}/',canonical) else '{$qsql['domain']}/".implode("/",$url)."' end  as canonical from sayfalar where id=:id");
        $query->execute(["id"=>$Routing["EntityId"]]);
        $result = $query->fetch(PDO::FETCH_ASSOC);

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
    public static function SinglePayment($Routing)
    {
        global $db;
        global $url;

        $query = $db->prepare("select id,title,baslik,url,left(description,250) as description,icerik,0 as header,case when len(canonical) > 0 then  concat('{$qsql['domain']}/',canonical) else '{$qsql['domain']}/".implode("/",$url)."' end  as canonical from sayfalar where id=456");
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);

        $query = $db->prepare("select Payments.*,C.Symbol,k.musteri as KayitlarMusteri,k.email as KayitlarEmail,k.telefon as KayitlarTelefon,GETDATE() as CurrentDate,case WHEN Payments.ExpiredOn>GETDATE() THEN 0 ELSE 1 END as IsExpired from Finance.Payments inner join Finance.Currency C on C.CurrencyId=Payments.CurrencyId left join kayitlar k on k.id=Payments.ReservationId where PaymentId=:PaymentId");
        $query->execute(["PaymentId"=>$Routing["EntityId"]]);
        $result["Payment"] = $query->fetch(PDO::FETCH_ASSOC);

        //Structural - Yapısal Veriler
        $Structural = new Structural($Routing,$result);
        $result["Structural"] = $Structural->Result();
        //Structural - Yapısal Veriler

        //Breadcrumb
        $Breadcrumb = new Breadcrumb($Routing,$result);
        $result["BreadCrumb"] = $Breadcrumb->Result();
        $result["BreadCrumbx"] = $Routing;
        //Breadcrumb

        return $result;
    }

    public static function VillaForm($Routing){
        global $db;
        global $qsql;
        global $url;



        $query = $db->prepare("select id,title,isnull(resim, '') as resim, kapak,baslik,left(description,250) as description,'/'+url as url,cat,replace(icerik,'../../../../..','https://cdn.villareyonu.com') as icerik, case when len(canonical) > 0 then  concat('{$qsql['domain']}/',canonical) else '{$qsql['domain']}/".implode("/",$url)."' end  as canonical from sayfalar where id=:id");
        $query->execute(["id"=>$Routing["EntityId"]]);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        $query = $db->prepare("select uploadID,filename from upload where islm='sayfa' and islm_id=:islm_id and site=1 order by sira");
        $query->execute(["islm_id"=>$Routing["EntityId"]]);
        $result["_photos"]=$query->fetchAll(PDO::FETCH_ASSOC);

        $query = $db->prepare("select id,baslik,icerik from faq where islm='sayfa' and cat=:islm_id and site=1 order by siralama");
        $query->execute(["islm_id"=>$Routing["EntityId"]]);
        $result["_faq"]=$query->fetchAll(PDO::FETCH_ASSOC);

        $query = $db->prepare("select id,title,resim,baslik,left(description,250) as description,url,replace(icerik,'../../../../..','https://cdn.villareyonu.com') as icerik, case when len(canonical) > 0 then  concat('{$qsql['domain']}/',canonical) else '{$qsql['domain']}/".implode("/",$url)."' end  as canonical from sayfalar where not blog=1 and aktif=1 and cat=:cat");
        $query->execute(["cat"=>$result["cat"]=="0" ? $Routing["EntityId"] : $result["cat"] ]);
        $result["_pages"]=$query->fetchAll(PDO::FETCH_ASSOC);

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


}