<?php
SetHeader(200);
$json = [];


$start = $month = date('m');
$year = date("Y");
if ($start>=7)
    $end=12;
else
    $end=$start+5;

$page = Page::GetById(9,"/");

$query = $db->prepare("select * from dbo.kisasureli({$start},{$end},$year,'')");
$query->execute();
$json["data"] = array_map(function($i)use($page){
    $a = explode(";/",$i["deger"]);
    $i["deger"]=[];
    foreach ($a as $b) {
        $par = explode("#",$b);
        $i["deger"][]=[
            "gece"=>$par[0],
            "adet"=>$par[1],
            "title"=>$i["ay"]." Ayı ".$par[0]." gece müsait villalar",
            "url"=>$page["url"]."?gece=".$par[0]."&ay=".$i["ay2"]
        ];
    }
    return $i;
},$query->fetchAll(PDO::FETCH_ASSOC));

$json["page"]=$page;
echo json_encode($json);