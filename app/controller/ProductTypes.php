<?php

SetHeader(200);

$query = $db->prepare("Select 
    url,baslik,resim,siralama,'1' as a
    from sayfalar where aktif=1 and menu2=1 and not resim=''
    union
    select url,baslik,icon,siralama,2 as a from tip where favori=1 and aktif=1 and not icon=''
    order by a,siralama

");
$query->execute();
$json["data"]=$query->fetchAll(PDO::FETCH_ASSOC);

 
echo json_encode($json);
