<?php

    $query = $db->prepare("Update Finance.Payments set PaymentStatus=1 where ExpiredOn<getdate() and PaymentStatus=0");
    $query->execute();
    $result = $query->fetchAll(PDO::FETCH_ASSOC);