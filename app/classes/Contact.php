<?php

class Contact
{
    public static function Index($Routing){
        global $db;
        global $qsql;
        $query = $db->prepare("select id,title,baslik,url,left(description,250) as description,resim from sayfalar where id=:id");
        $query->execute(["id"=>$Routing["EntityId"]]);
        $result=$query->fetch(PDO::FETCH_ASSOC);

        //Structural - Yapısal Veriler
        $Structural = new Structural($Routing,$result);
        $result["Structural"] = $Structural->Result();
        //Structural - Yapısal Veriler

        //Breadcrumb
        $Breadcrumb = new Breadcrumb($Routing,$result);
        $result["BreadCrumb"] = $Breadcrumb->Result();
        //Breadcrumb

        $result["Location"]["Latitude"] = $qsql["enlem"];
        $result["Location"]["Longitude"] = $qsql["boylam"];

        $result["static"]=[
            "telefon"=>$qsql["telefon"],
            "adres"=>$qsql["adres"],
            "email"=>$qsql["sitemail"],
            "rez_tel"=>$qsql["rez_tel"],
            "gsm"=>$qsql["gsm"],
            "faturaVergiDairesi"=>$qsql["faturaVergiDairesi"],
            "faturaTitle"=>$qsql["faturaTitle"],
            "faturaVkn"=>$qsql["faturaVkn"],
        ];


        return $result;
    }
}