<?php
SetHeader(200);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$response = [];

if (strlen(get('q')) < 3 || empty(get('q')))
    $response['error'] = 'Search query should be at least 3 characters long.';


if (empty($response['error']))
{
        $query = $db->prepare("SELECT TOP 3
        'Villa' as searchCategory,
        r.Slug AS slug,
        r.RoutingId AS RoutingId,
        r.EntityId AS EntityId,
        r.RoutingTypeId AS RoutingTypeId,
        h.id AS home_id,
        h.baslik AS home_title,
        d2.id AS destination_level_2_id,
        d2.baslik AS destination_level_2_title,
        d1.id AS destination_level_1_id,
        d1.baslik AS destination_level_1_title,
        d0.id AS destination_level_0_id,
        d0.baslik AS destination_level_0_title,
        null as bolge,
        null as type_id,
        null as type_title,
        null as location_category_id,
        null as location_category_title,
        null as location_title,
        null as location_info
    FROM homes h
             INNER JOIN destinations d2 on d2.id = h.emlak_bolgesi
             INNER JOIN destinations d1 on d1.id = d2.cat
             INNER JOIN destinations d0 on d0.id = d1.cat
             LEFT JOIN Routing r on r.EntityId = h.id and r.RoutingTypeId = 'ProductDetail' and r.site = 1
    WHERE h.aktif = 1
      and d2.aktif = 1
      and d1.aktif = 1
      and d0.aktif = 1
      and (h.baslik like :param1)
    
    UNION
    
    SELECT
        TOP 3
        'Tatil Bölgesi' as searchCategory,
        r.Slug AS slug,
        r.RoutingId AS RoutingId,
        r.EntityId AS EntityId,
        r.RoutingTypeId AS RoutingTypeId,
        '',
        '',
        d.id AS destination_level_2_id,
        d.baslik AS destination_level_2_title,
        d1.id AS destination_level_1_id,
        d1.baslik AS destination_level_1_title,
        d0.id AS destination_level_0_id,
        d0.baslik AS destination_level_0_title,
        concat(IIF(d0.id>0,d0.baslik+'/',''), IIF(d1.id>0,d1.baslik+'/',''), d.baslik) as bolge,
        null as type_id,
        null as type_title,
        null as location_category_id,
        null as location_category_title,
        null as location_title,
        null as location_info
    FROM destinations d
             LEFT JOIN destinations d1 on d1.id = d.cat
             LEFT JOIN destinations d0 on d0.id = d1.cat
             LEFT JOIN Routing r on (r.EntityId = d.id) and r.RoutingTypeId = 'ProductDestination' and r.site = 1
    WHERE d.aktif = 1
      and (d.baslik like :param2)
    
    UNION
    
    
    SELECT
        TOP 3
        'Emlak Tipi' as searchCategory,
        r.Slug AS slug,
        r.RoutingId AS RoutingId,
        r.EntityId AS EntityId,
        r.RoutingTypeId AS RoutingTypeId,
        null AS home_id,
        null AS home_title,
        null AS destination_level_2_id,
        null AS destination_level_2_title,
        null AS destination_level_1_id,
        null AS destination_level_1_title,
        null AS destination_level_0_id,
        null AS destination_level_0_title,
        null as bolge,
        t.id as tip_id,
        t.baslik as type_title,
        null as location_category_id,
        null as location_category_title,
        null as location_title,
        null as location_info
    FROM tip t
        LEFT JOIN Routing r on (r.EntityId = t.id) and r.RoutingTypeId = 'ProductCategory' and r.site = 1
    WHERE t.aktif = 1
        and (t.baslik like :param3)
    
    
    UNION
    
    
    SELECT
        TOP 3
        'Konum' as searchCategory,
        null AS slug,
        null AS RoutingId,
        null AS EntityId,
        null AS RoutingTypeId,
        null AS home_id,
        null AS home_title,
        null AS destination_level_2_id,
        null AS destination_level_2_title,
        null AS destination_level_1_id,
        null AS destination_level_1_title,
        null AS destination_level_0_id,
        null AS destination_level_0_title,
        null as bolge,
        null as tip_id,
        null as type_title,
        pc.id as location_category_id,
        pc.nameTr as location_category_title,
        p.name as location_title,
        loc_info.location_info as location_info
    FROM places p
        CROSS APPLY (
            SELECT 
                (
                    SELECT 
                        p_cross.place_id AS id, 
                        p_cross.name, 
                        pc.nameTr AS category, 
                        pc.id AS categoryId, 
                        p_cross.lat, 
                        p_cross.lng, 
                        p_cross.address, 
                        ISNULL(pc.icon, '') AS icon,
                        (
                            SELECT Filename
                            FROM upload 
                            WHERE upload.islm = 'viisky_konum'  
                              AND upload.islm_id = p_cross.id 
                              AND upload.site = 1
                            ORDER BY upload.sira ASC
                            FOR JSON PATH
                        ) AS images, 
                        p_cross.photo_reference
                    FROM places p_cross
                    LEFT JOIN place_categories pc ON pc.name = p_cross.category
                    WHERE p_cross.id = p.id
                    FOR JSON PATH
                ) AS location_info
        ) loc_info
        INNER JOIN place_categories pc on pc.name = p.category
    WHERE  (p.name like :param4 or pc.nameTr like :param5)
    
    UNION

    SELECT
        TOP 3
        'Konum Kategorisi' as searchCategory,
        null AS slug,
        null AS RoutingId,
        null AS EntityId,
        null AS RoutingTypeId,
        null AS home_id,
        null AS home_title,
        null AS destination_level_2_id,
        null AS destination_level_2_title,
        null AS destination_level_1_id,
        null AS destination_level_1_title,
        null AS destination_level_0_id,
        null AS destination_level_0_title,
        null as bolge,
        null as tip_id,
        null as type_title,
        pc.id as location_category_id,
        pc.nameTr as location_category_title,
        null as location_title,
        null as location_info
    FROM place_categories pc
    WHERE  (pc.nameTr like :param6)");

        try
        {
            $query->execute([
                'param1' => "%" . get('q') . "%",
                'param2' => "%" . get('q') . "%",
                'param3' => "%" . get('q') . "%",
                'param4' => "%" . get('q') . "%",
                'param5' => "%" . get('q') . "%",
                'param6' => "%" . get('q') . "%"
            ]);
            $response = $query->fetchAll(PDO::FETCH_ASSOC);
            if (empty($response)) {
                die("[]");
            } else {

                foreach ($response as &$data)
                {
                    $locationInfoArray = json_decode($data["location_info"], true);

                    if (!empty($locationInfoArray) && is_array($locationInfoArray)) {
                        $data["location_info"] = $locationInfoArray[0];
                    }
                }

                echo json_encode($response);
            }
        }
        catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }

}
else
    die(json_encode($response["error"]));

