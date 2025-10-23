<?php
class Destination{


    public static function GetById ($id) {
        global $db;

        $query = $db->prepare("select id,title,baslik,url as url,aktif,cat,canonical from destinations where id=:id");
        $query->execute(["id"=>$id]);
        return $query->fetch(PDO::FETCH_ASSOC);

    }



}