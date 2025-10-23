<?php
	if(!isset($_GET["onizle"]) || $_GET["onizle"]!="1")
		header('Content-type: text/plain');
	if(isset($_GET["islm"]) && $_GET["islm"]!=""){
		$file=str_replace(["/","\\"],["",""],$_GET["islm"]);
		if(strpos($file,".txt")==true){
			$dosyaAc = fOpen($file , "r"); 
			$dosyaOku = fRead ($dosyaAc , fileSize ($file)); 
			echo $dosyaOku; 
			fClose($dosyaAc);
		}
	}
	else
		echo "";

?>