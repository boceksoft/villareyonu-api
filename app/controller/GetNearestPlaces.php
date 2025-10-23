<?php
SetHeader(200);

CONST APIKEY ='edb9d29b095929621ee8b05ea7b0a6c6';

$token = get('token');

if (empty($token) || $token !== APIKEY){
    echo json_encode('Wrong API Key!');
    SetHeader(400);
    exit;
}

$latitude = get('latitude');
$longitude = get('longitude');
$categoryId = get('category') ?? "";
$q = get('q') ?? "";
$q = preg_replace('/\s+/', '', $q);
$where = "";

if (empty($longitude) || empty($latitude)){
    echo json_encode('Latitude or longitude cannot be empty!');
    SetHeader(400);
    exit;
}
if (!is_numeric($longitude) || !is_numeric($latitude)){
    echo json_encode('Latitude or longitude should be number!');
    SetHeader(400);
    exit;
}


if (!empty($categoryId))
    $where = " AND pc.id in (".$categoryId.") ";



if (!empty($q))
    $where.= " AND p.name LIKE N'%". $q. "%' ";



$sql = "
    SELECT
        p.place_id as id,
        p.name,
        pc.nameTr as category,
        pc.id as categoryId,
        p.lat as latitude,
        p.lng as longitude,
        p.address,
        p.aciklama,
        p.phone,
        pc.icon as icon,
        geography::Point(p.lat, p.lng, 4326).STDistance(geography::Point(?, ?, 4326)) / 1000 AS distance,
         (SELECT Filename
         FROM upload 
         WHERE upload.islm='viisky_konum'  
           AND upload.islm_id=p.id 
           AND upload.site=1
           ORDER BY upload.sira ASC
         FOR JSON PATH) AS images,
        isnull(p.photo_reference, '') as photo_referance
    FROM
        places p
            LEFT JOIN place_categories pc on pc.name = p.category
    WHERE
        geography::Point(p.lat, p.lng, 4326).STDistance(geography::Point(?, ?, 4326)) <= 1000
        ".$where."
    ORDER BY
        distance;
            ";

$stmt = $db->prepare($sql);
$stmt->execute([
    $latitude,
    $longitude,
    $latitude,
    $longitude,
    $latitude,
    $longitude
]);

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($data as &$response)
    {
        $response["images"] = json_decode($response["images"], true);
    }
    echo json_encode($data);

?>