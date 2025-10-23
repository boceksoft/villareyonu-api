<?php
    header("Content-Type : application/json");
    $mailkonu = "Test Mail!".date("Y-m-d H:i:s");
    $Mail = new SendMail();
    $Mail->setEmail(get("mail")?:"taslan39@gmail.com");
    $Mail->setContent(rqbot('sablon1.asp?islem=rezervasyon_talep'));
    $Mail->setReceiverName("Test");
    $Mail->setSubject($mailkonu);
    if($Mail->Send()){
        $json["success"]=get("mail")?:"taslan39@gmail.com"." adresine mail gönderildi.";
    }else{
        $json["error"]=$Mail->getErr();
    }
    echo json_encode($json);