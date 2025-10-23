<?php

class Settings
{

    public static function Get($key)
    {
        global $db;

        $query = $db->prepare("SELECT setting_value FROM Settings WHERE setting_key = :setting_key");
        $query->execute(["setting_key"=>$key]);
        return $query->fetch(PDO::FETCH_ASSOC)["setting_value"];

    }

}