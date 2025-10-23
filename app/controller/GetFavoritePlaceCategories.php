<?php
SetHeader(200);


$query = $db->prepare("SELECT top 6 id,nameTr as name, icon, color, favori FROM place_categories where favori = 1 order by id asc");
$query->execute([]);
$data = $query->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);

?>