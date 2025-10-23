<?php
header("Content-Type: text/html; charset=utf-8");
$mailicerik = MailTemplate::index("pdfgonder.html",get("rez"));
$query = $db->prepare("select icerik".UZANTI." as icerik from MailSablon where id=8");
$query->execute();
$mailsablon1 = $query->fetch(PDO::FETCH_ASSOC);
$mailicerik = str_replace("{-mail-icerik-}",		$mailsablon1["icerik"],												$mailicerik);
echo $mailicerik;