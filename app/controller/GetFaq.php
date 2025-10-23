<?php
SetHeader(200);

$query = $db->prepare("SELECT id, baslik AS question, icerik AS answer FROM faq WHERE islm = N'viisky_sorular' ORDER BY siralama asc");
$query->execute([]);

$data = $query->fetchAll(PDO::FETCH_ASSOC);

if (empty($data)){
    echo json_encode("Not Found");
    SetHeader(404);
    exit;
}
    foreach ($data as &$d)
    {
        $d['answer'] = strip_tags(str_replace("&nbsp;", " ", $d['answer']));
    }

echo json_encode($data);


?>