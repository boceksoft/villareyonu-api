<?php

namespace classes;
class Favourites
{


    public static function AddItem($ProductId, $UserGuid = "")
    {
        global $db;

        if (self::CheckExists($ProductId, $UserGuid)) {
            throw new Exception('Ürün zaten favorilerinizde');
        }

        $UserGuid = self::GenerateUserGuid($UserGuid);
        try {
            $query = $db->prepare("INSERT INTO dbo.Favourites (ProductId,UserGuid) VALUES (:ProductId,:UserGuid)");
            $query->execute([
                'ProductId' => $ProductId,
                'UserGuid' => $UserGuid
            ]);
            return $UserGuid;
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    public static function RemoveItem($ProductId, $UserGuid = null)
    {
        global $db;
        if (self::CheckExists($ProductId, $UserGuid)) {
            try {
                $query = $db->prepare("DELETE FROM dbo.Favourites WHERE ProductId = :ProductId AND UserGuid = :UserGuid");
                $query->execute([
                    'ProductId' => $ProductId,
                    'UserGuid' => $UserGuid
                ]);
                return true;
            } catch (Exception $e) {
                throw new Exception($e);
            }
        } else {
            throw new Exception('Ürün bulunamadı');
        }

    }

    public static function ListItems($UserGuid = null)
    {
        global $db;

        if ($UserGuid) {
            $query = $db->prepare("SELECT ProductId,'/'+h.url" . UZANTI . " as url,d2.baslik" . UZANTI . "+' / '+d.baslik" . UZANTI . " as destination,
       dbo.FnRandomSplit(h.resim,',') as image,h.baslik" . UZANTI . " as name  FROM dbo.Favourites inner join homes h on h.id=ProductId inner join destinations d on d.id = h.emlak_bolgesi
inner join destinations d2 on d2.id = d.cat  WHERE UserGuid = :UserGuid");
            $query->execute([
                'UserGuid' => $UserGuid
            ]);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return [];
        }


    }

    public static function GenerateUserGuid($UserGuid)
    {

        return $UserGuid ?: sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    public static function CheckExists($ProductId, $UserGuid = "")
    {
        global $db;

        if ($UserGuid == "")
            return false;

        try {

            $query = $db->prepare("SELECT COUNT(*) FROM dbo.Favourites WHERE ProductId = :ProductId AND UserGuid = :UserGuid");
            $query->execute([
                'ProductId' => $ProductId,
                'UserGuid' => $UserGuid
            ]);
            return $query->fetchColumn();
        } catch (Exception $e) {
            throw new Exception($e);
        }

    }

}