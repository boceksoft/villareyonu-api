<?php

class BlogDetail
{
    public static function Index($Routing){

        global $db;
        global $qsql;
        $query = $db->prepare("SET LANGUAGE Turkish;select id,title,baslik,description,url,icerik,resim,FORMAT(tarih,'MMM dd yyyy') as tarih,resim as kapak  from sayfalar".UZANTI." where id=:id");
        $query->execute(["id"=>$Routing["EntityId"]]);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        $result["icerik"]=str_replace("../../../../..",$qsql["domain"],$result["icerik"]);

        //Breadcrumb
        $Breadcrumb = new Breadcrumb($Routing,$result);
        $result["BreadCrumb"] = $Breadcrumb->Result();
        //Breadcrumb

        $blog = Page::GetById(10);

        $query = $db->prepare("SET LANGUAGE Turkish;select top 5 s.baslik,s.title,'/".$blog["url"]."/'+s.url as url,s.resim,FORMAT(s.tarih,'MMM dd yyyy') as tarih from sayfalar".UZANTI." s where s.aktif=1 and s.blog=1 order by s.tarih desc");
        $query->execute([]);
        $result["BlogData"]["LastShared"] = $query->fetchAll(PDO::FETCH_ASSOC);

        $query = $db->prepare("SET LANGUAGE Turkish;select top 5 s.baslik,s.title,'/".$blog["url"]."/'+s.url as url,s.resim,FORMAT(s.tarih,'MMM dd yyyy') as tarih from sayfalar".UZANTI." s where s.aktif=1 and s.blog=1 order by s.tarih desc");
        $query->execute([]);
        $result["BlogData"]["Popular"] = $query->fetchAll(PDO::FETCH_ASSOC);

        $query = $db->prepare("select uploadID,filename from upload where islm='blog' and islm_id=:islm_id and site=".SITE." order by sira");
        $query->execute(["islm_id"=>$Routing["EntityId"]]);
        $result["_photos"]=$query->fetchAll(PDO::FETCH_ASSOC);

        $result["BlogData"]["Categories"]=BlogList::GetCategories();



        return $result;
    }
}