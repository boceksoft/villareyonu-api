<?php
SetHeader(200);

$id = get("id") ?? "";


if (empty($id)){
    $query = $db->prepare("select * from stories where aktif = 1 and favori = 1 order by siralama asc");
    $query->execute();
    $data = $query->fetchAll(PDO::FETCH_ASSOC);
}
else{
    if (!is_numeric($id)){
        echo json_encode('Id parameter must be a integer!');
        SetHeader(400);
        exit;
    }

    $query = $db->prepare("select * from stories where aktif = 1 and favori = 1 and id = :id order by siralama asc");
    $query->execute([
        'id' => $id
    ]);
    $data = $query->fetch(PDO::FETCH_ASSOC);

    if (empty($data)){
        echo json_encode('Not Found');
        SetHeader(404);
        exit;
    }
}

    echo json_encode($data);
?>