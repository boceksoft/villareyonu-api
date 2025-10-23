<?php

    $conversationId=get("_");

    $query = $db->prepare("SELECT * FROM Finance.VirtualPosResponses WHERE conversationId=:conversationId");
    $query->execute(["conversationId"=>$conversationId]);
    $VirtualPosResponse = $query->fetch(PDO::FETCH_ASSOC);
    if (!$VirtualPosResponse){
        $json["error"]="Bu siparişe ait herhangi bir ödeme bulunamadı.";
    }else{
        $result = json_decode($VirtualPosResponse["Response"],2);
        $HtmlContent=<<<HTML
<html>
	<head>
		<title>Get724 Mpi 3D-Secure ??lem Sayfas?</title>
	</head>
	<body>
	
		<form name="downloadForm" action="{$result['ACSUrl']}" method="POST">
<!--		<noscript>-->
		<br>
		<br>
		<center>
		<h1>3-D Secure İşleminiz yapılıyor</h1>
		<h2>
		Tarayıcınızda Javascript kullanımı engellenmiştir.
		<br></h2>
		<h3>
			3D-Secure işleminizin doğrulama aşamasına geçebilmek için Gönder butonuna basmanız gerekmektedir
		</h3>
		<input type="submit" value="Gönder">
		</center>
<!--</noscript>-->
		<input type="hidden" name="PaReq" value="{$result['PaReq']}">
		<input type="hidden" name="TermUrl" value="{$result['TermUrl']}">
		<input type="hidden" name="MD" value="{$result['MerchantData']}">
		</form>
	<SCRIPT LANGUAGE="Javascript" >
		document.downloadForm.submit();
	</SCRIPT>
	</body>
</html>
HTML;
        header("Content-Type: text/html; charset=utf-8");
        echo $HtmlContent;

    }