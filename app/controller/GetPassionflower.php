<?php
SetHeader(200);


$query = $db->prepare("select [percent] = indirim, [count] = cast(adet as int), [background] = bg_renk, [color] = tx_renk from carkifelek");
$query->execute();
$data = $query->fetchAll(PDO::FETCH_ASSOC);
$data["data"]=$data;
//return false if today is friday
$data["isEnabled"]=false;
if(date("N") == 3 || date("N") == 5){
    $data["isEnabled"]=true;
}


echo json_encode($data,JSON_NUMERIC_CHECK);
?>