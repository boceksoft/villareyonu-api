<?php

class Menu
{

    public static function GetItemsByMenuId($MenuId,$Cat=0)
    {
        global $db;

        $query = $db->prepare("select * from dbo.MenuItems where MenuId = :MenuId and Cat=:Cat and IsActive=1 and Site=".SITE." order by Sort");
        $query->execute([
            "MenuId"=>$MenuId,
            "Cat"=>$Cat
        ]);

        $Data = array_map(function($item){
            $item["Items"] = Menu::GetItemsByMenuId($item["MenuId"],$item["Id"]);
            return $item;
        }, $query->fetchAll(PDO::FETCH_ASSOC));

        return $Data;

    }

}