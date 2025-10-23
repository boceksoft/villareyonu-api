<?php
    $mailkonu = "Test Mail!";
    $Mail = new SendMail();
    $Mail->setEmail("tolga@boceksoft.com");
    $Mail->setContent("Denemee");
    $Mail->setReceiverName("Test");
    $Mail->setSubject($mailkonu);
    if($Mail->Send()){
        $json["success"]="200";
    }else{
        $json["error"]=$Mail->getErr();
    }
    echo json_encode($json);