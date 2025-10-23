<?php

class BlogList
{
    public static function Index($Routing){
        global $db;
        $query = $db->prepare("select id,title,concat('/',url) as url,baslik,description,resim,kapak from sayfalar".UZANTI." where id=:id");
        $query->execute(["id"=>$Routing["EntityId"]]);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        //Breadcrumb
        $Breadcrumb = new Breadcrumb($Routing,$result);
        $result["BreadCrumb"] = $Breadcrumb->Result();
        //Breadcrumb


        $perPage=10;
        $page = get("page") ?: 1;
        $start = ($perPage * $page) - $perPage;
        $ExecuteArray=[];


        $countSelect = "select count(s.id) as totalCount ";
        $normalSelect = SQLLANG."select s.id,bk.baslik".UZANTI." as kategori,concat('/',bk.url".UZANTI.") as kategoriurl,bk.title".UZANTI." as kategorititle,s.baslik,s.title,'/'+s.url as url,s.resim,s.kisa_icerik,FORMAT(s.tarih,'MMM dd yyyy') as tarih ";
        $sql = " from sayfalar".UZANTI." s left join blog_kategorileri bk on s.kategori=bk.id where s.blog=1 and s.aktif=1 ";
        // order by s.tarih desc

        if (get("search")){
            $ExecuteArray["text"]="%".get("search")."%";
            $sql.=" and s.baslik like :text COLLATE Turkish_CI_AS ";
        }

        $totalRecord =$db->prepare($countSelect.$sql);
        $totalRecord->execute($ExecuteArray);
        $totalRecord=$totalRecord->fetch(PDO::FETCH_ASSOC)["totalCount"];

        $arr = $db->prepare($normalSelect.$sql." order by s.tarih desc OFFSET $start ROWS FETCH NEXT $perPage ROWS ONLY");
        $arr->execute($ExecuteArray);

        $query = $db->prepare(SQLLANG."select  bk.baslik".UZANTI." as kategori,concat('/',bk.url".UZANTI.") as kategoriurl,bk.title".UZANTI." as kategorititle,s.baslik,s.title,'/'+s.url as url,s.resim,FORMAT(s.tarih,'MMM dd yyyy') as tarih from sayfalar".UZANTI." s left join blog_kategorileri bk on s.kategori=bk.id where s.aktif=1 and s.blog=1 and s.favori=1 order by s.siralama asc");
        $query->execute([]);
        $Popular = $query->fetchAll(PDO::FETCH_ASSOC);



        $result["BlogData"]=[
            "CurrentPage"=>$page,
            "TotalRecord"=>$totalRecord,
            "TotalPage"=>(($totalRecord - ($totalRecord % $perPage)) / $perPage) + ($totalRecord % $perPage>0 ? 1 :0),
            "result"=>$arr->fetchAll(PDO::FETCH_ASSOC),
            "Popular"=>$Popular,
            "Categories"=>BlogList::GetCategories(),
            "BlogPage"=>Page::GetById(10,"/"),
        ];


        return $result;
    }
    public static function Category($Routing){
        global $db;
        $query = $db->prepare("select id,title".UZANTI." as title,concat('/',url".UZANTI.") as url,baslik".UZANTI." as baslik,description".UZANTI." as description,resim,'' as kapak from blog_kategorileri where id=:id");
        $query->execute(["id"=>$Routing["EntityId"]]);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        //Breadcrumb
        $Breadcrumb = new Breadcrumb($Routing,$result);
        $result["BreadCrumb"] = $Breadcrumb->Result();
        //Breadcrumb


        $perPage=10;
        $page = get("page") ?: 1;
        $start = ($perPage * $page) - $perPage;
        $ExecuteArray=[];


        $countSelect = "select count(s.id) as totalCount ";
        $normalSelect = SQLLANG."select s.id,bk.baslik".UZANTI." as kategori,concat('/',bk.url".UZANTI.") as kategoriurl,bk.title".UZANTI." as kategorititle,s.baslik,s.title,'/'+s.url as url,s.resim,s.kisa_icerik,FORMAT(s.tarih,'MMM dd yyyy') as tarih ";
        $sql = " from sayfalar".UZANTI." s left join blog_kategorileri bk on s.kategori=bk.id where s.blog=1 and s.aktif=1 and s.kategori=:id ";
        // order by s.tarih desc

        $ExecuteArray["id"]=$Routing["EntityId"];

        if (get("search")){
            $ExecuteArray["text"]="%".get("search")."%";
            $sql.=" and s.baslik like :text COLLATE Turkish_CI_AS ";
        }

        $totalRecord =$db->prepare($countSelect.$sql);
        $totalRecord->execute($ExecuteArray);
        $totalRecord=$totalRecord->fetch(PDO::FETCH_ASSOC)["totalCount"];

        $arr = $db->prepare($normalSelect.$sql." order by s.tarih desc OFFSET $start ROWS FETCH NEXT $perPage ROWS ONLY");
        $arr->execute($ExecuteArray);


        $query = $db->prepare(SQLLANG."select  bk.baslik".UZANTI." as kategori,concat('/',bk.url".UZANTI.") as kategoriurl,bk.title".UZANTI." as kategorititle,s.baslik,s.title,'/'+s.url as url,s.resim,FORMAT(s.tarih,'MMM dd yyyy') as tarih from sayfalar".UZANTI." s left join blog_kategorileri bk on s.kategori=bk.id where s.aktif=1 and s.blog=1 and s.favori=1 order by s.siralama asc");
        $query->execute([]);
        $Popular = $query->fetchAll(PDO::FETCH_ASSOC);


        $result["BlogData"]=[
            "CurrentPage"=>$page,
            "TotalRecord"=>$totalRecord,
            "TotalPage"=>(($totalRecord - ($totalRecord % $perPage)) / $perPage) + ($totalRecord % $perPage>0 ? 1 :0),
            "result"=>$arr->fetchAll(PDO::FETCH_ASSOC),
            "Popular"=>$Popular,
            "Categories"=>BlogList::GetCategories(),
            "BlogPage"=>Page::GetById(10,"/"),
        ];


        return $result;
    }

    public static function GetCategories()
    {
        global $db;

        $query = $db->prepare(SQLLANG."select id,baslik".UZANTI." as baslik,title".UZANTI." as title ,'/'+url".UZANTI." as url,(select count(h.id) as ContentCount from sayfalar".UZANTI." h where h.aktif=1 and h.blog=1 and h.kategori=blog_kategorileri.id) as ContentCount from blog_kategorileri where aktif=1 order by siralama");
        $query->execute();
        $json = $query->fetchAll(PDO::FETCH_ASSOC);

        return $json;
    }


}
