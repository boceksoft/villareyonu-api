<?php
class MetaData
{
    public static function GenerateMetaData($Routing)
    {
        global $db;
        $return = [];

        if ($Routing["RoutingTypeId"]=="ProductDetail"){
            $query = $db->prepare("select title".UZANTI." as title,left(description".UZANTI.",250) as description,dbo.FnRandomSplit(resim,',') as image from homes where id=:id");
            $query->execute(["id"=>$Routing["EntityId"]]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            $return["title"]= $result["title"];
            $return["description"]= $result["description"];
            $return["image"]= CDN."/uploads/".$result["image"];
        }elseif ($Routing["RoutingTypeId"]=="ProductCategory"){
            $query = $db->prepare("select title".UZANTI." as title,left(description".UZANTI.",250) as description from tip where id=:id");
            $query->execute(["id"=>$Routing["EntityId"]]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            $return["title"]= $result["title"];
            $return["description"]= $result["description"];
        }elseif ($Routing["RoutingTypeId"]=="ProductDestination"){
            $query = $db->prepare("select title".UZANTI." as title,left(description".UZANTI.",250) as description from destinations where id=:id");
            $query->execute(["id"=>$Routing["EntityId"]]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            $return["title"]= $result["title"];
            $return["description"]= $result["description"];
        }elseif ($Routing["RoutingTypeId"]=="BlogCategory"){
            $query = $db->prepare("select title".UZANTI." as title,left(description".UZANTI.",250) as description from blog_kategorileri where id=:id");
            $query->execute(["id"=>$Routing["EntityId"]]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            $return["title"]= $result["title"];
            $return["description"]= $result["description"];
        }else{
            $query = $db->prepare("select title,left(description,250) as description from sayfalar".UZANTI." where id=:id");
            $query->execute(["id"=>$Routing["EntityId"]]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            $return["title"]= $result["title"];
            $return["description"]= $result["description"];
        }


        return $return;
    }


}