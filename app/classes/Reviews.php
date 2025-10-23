<?php

class Reviews
{
    public static function GetReviews($islm, $islm_id)
    {
        global $db;

        $query = $db->prepare("select * from defter where islm=:islm and islm_id=:islm_id and onay=1 order by convert(date,tarih,104) desc");
        $query->execute([
            "islm" => $islm,
            "islm_id" => $islm_id
        ]);
        return $query->fetchAll(PDO::FETCH_ASSOC);


    }
    public static function GetHomeReviews ($All="") {
        global $db;
        if ($All=="")
            $top="top 5";
        $query = $db->prepare("select {$top} defter.id,defter.mesaj,defter.isim,defter.puan,defter.tarih,h.url,h.baslik,h.title,dbo.FnRandomSplit(h.resim,',') as resim ,
       d1.baslik+' / '+d0.baslik as bolge ,concat('/',h.url) as url
            from defter 
                inner join homes h on h.id=defter.islm_id
                inner join destinations d0 on d0.id=h.emlak_bolgesi
                inner join destinations d1 on d1.id=d0.cat
            where  defter.islm='emlak' and defter.onay=1 and site=".SITE." order by defter.id desc");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);


    }
    public static function GetAllReviews($page = 1)
    {
        global $db;

        $pageSize = 10;
        $offset = ($page - 1) * $pageSize;
        $baseWhere = "defter.islm = 'emlak' 
                  AND defter.onay = 1 
                  AND site = " . SITE;

        $sql = "
            SELECT defter.id,
                   defter.mesaj,
                   defter.isim,
                   defter.puan,
                   defter.tarih,
                   h.url,
                   h.baslik,
                   h.title,
                   dbo.FnRandomSplit(h.resim, ',') AS resim,
                   d1.baslik + ' / ' + d0.baslik AS bolge,
                   '/' + h.url AS url
            FROM defter
                 INNER JOIN homes h ON h.id = defter.islm_id
                 INNER JOIN destinations d0 ON d0.id = h.emlak_bolgesi
                 INNER JOIN destinations d1 ON d1.id = d0.cat
            WHERE $baseWhere
            ORDER BY defter.id DESC
            OFFSET CAST(:offset AS INT) ROWS FETCH NEXT CAST(:limit AS INT) ROWS ONLY";


        $query = $db->prepare($sql);
        $query->execute([
            "offset" => $offset,
            "limit" => $pageSize
        ]);
        $data = $query->fetchAll(PDO::FETCH_ASSOC);

        $countSql = "
        SELECT COUNT(*) AS total
        FROM defter
             INNER JOIN homes h ON h.id = defter.islm_id
             INNER JOIN destinations d0 ON d0.id = h.emlak_bolgesi
             INNER JOIN destinations d1 ON d1.id = d0.cat
        WHERE $baseWhere";

        $countQuery = $db->query($countSql);
        $total = $countQuery->fetch(PDO::FETCH_ASSOC)['total'];

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize,
            'totalPages' => ceil($total / $pageSize)
        ];
    }


    public static function GetTotal()
    {
        global $db;


    }

}