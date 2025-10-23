<?php
    SetHeader(200);
    $data=[];

    $name = post("name") ?? "";
    $phone = post("phone") ?? "";
    $verificationCode = post("verificationCode") ?? "";
    $phone = str_replace([" ",".","+"],"",$phone);

    if (empty($phone) && empty($name)){
        $data["error"]="Lütfen telefon ve mail adresinizi doğru girdiğinizden emin olun.";
    }
    else if (!is_numeric($phone)){
        $data["error"]="Telefon numaranız geçersiz.";
    }
    else{

        $query = $db->prepare("SELECT * FROM carkifelek_promosyon where '0-1'='$phone' or telefon='$phone' and ( (son_dogrulanma_tarihi>=getdate() and dogrulandi=0) or (dogrulandi=1 and son_kullanma_tarihi>=getdate() ) )");
        $query->execute();
        $dataQ = $query->fetch(PDO::FETCH_ASSOC);
        if (empty($dataQ)){
            $verificationCode=rand(1000,9999);
            $query = $db->prepare("insert into carkifelek_promosyon (telefon,isim_soyisim,dogrulama_kodu) values (:telefon,:isim_soyisim,:dogrulama_kodu)");
            $r = $query->execute([
                "telefon"=>$phone,
                "isim_soyisim"=>$name,
                "dogrulama_kodu"=>$verificationCode
            ]);
            if (!$r)
                $data["error"]="Bir sorun oluştu. Lütfen bilgileri kontrol edip tekrar deneyin.";
            else{

                $a = new SmsSend();
                $dataSend=array(
                    'message'=>"Çarkıfelek için doğrulama kodunuz  ".$verificationCode.". KİMSEYLE PAYLAŞMAYINIZ.",
                    'no'=>[$phone],
                    'header'=>$qsql["smsorg"],
                    'filter'=>0,
                    'encoding'=>'tr',
                    'startdate'=>'',
                    'stopdate'=>'',
                    'bayikodu'=>'',
                    'appkey'=>''
                );
                $sms= new SmsSend;
                $sms->smsGonder($dataSend);

                $data["success"]="Telefonunuza doğrulama kodu gönderildi, lütfen gelen doğrulama kodunu girin.";

            }
        }
        else{
            if($verificationCode==""){
                if($dataQ["dogrulandi"]==true){
                    $data["error"]="Lütfen telefonunuza en son gelen doğrulama kodunu girin.";
                }
                else{
                    $data["error"]="Lütfen telefonunuza gönderilen doğrulama kodunu girin.";
                }
            }
            else{
                if($verificationCode!=$dataQ["dogrulama_kodu"]){
                    $data["error"]="Doğrulama kodu hatalı.";
                }
                else{
                    if($dataQ["dogrulandi"]==true ){
                        $data["error"]="24 saate 1 kez çarkıfeleği çevirebilirsiniz.";
                    }
                    else{
                        $queryCark="WITH numbers AS (
                                SELECT 1 AS n
                                UNION ALL
                                SELECT n + 1
                                FROM numbers
                                WHERE n < 100
                            )
                            SELECT top 1 t.id, t.indirim, t.gelme_orani, t.baslik
                            FROM carkifelek t
                            CROSS JOIN numbers n WHERE n.n <= t.gelme_orani
                            ORDER BY newID()
                            OPTION (MAXRECURSION 0)";

                        $query = $db->prepare($queryCark);
                        $query->execute();
                        $dataWin = $query->fetch(PDO::FETCH_ASSOC);
                        if (empty($dataWin)){
                            $data["error"]="Bir sorun oluştu. Lütfen tekrar deneyin.";
                        }
                        else{
                            $promotionCode="CARK-".$dataWin["indirim"]."-".$dataQ["promosyon_kodu"];
                            $dicount=$dataWin["indirim"];
                            $query = $db->prepare("update carkifelek_promosyon set promosyon_kodu=:promosyon_kodu,indirim=:indirim,indirim_index=:indirim_index,homes=:homes,dogrulandi=1 where id = :id");
                            $r = $query->execute([
                                "id"=>$dataQ["id"],
                                "promosyon_kodu"=>$promotionCode,
                                "indirim_index"=>$dicount."_1",
                                "indirim"=>$dicount,
                                "homes"=>$dataQ["homes"]
                            ]);
                            if (!$r )
                                $data["error"]="Bir sorun oluştu. Lütfen bilgileri kontrol edip tekrar deneyin.";
                            else{
                                if($dataWin["indirim"]=="0"){
                                    $data["discount"]="0";
                                    $data["success"]="Malesef indirim kazanamadınız. 24 Saat sonra tekrar deneyiniz.";
                                    $data["promotionCode"]="";
                                    $data["nextList"]=$qsql["domain"];
                                }
                                else{
                                    $query = $db->prepare("select * from smssablon where id = 12 and aktif=1");
                                    $query->execute();
                                    $dataSms = $query->fetch(PDO::FETCH_ASSOC);
                                    if (!empty($dataSms)){
                                        $smsicerik=str_replace(["{isim}","{tarih}","{indirim}","{indirim_kodu}","{uygun_emlaklar}"],
                                                               [$dataQ["isim_soyisim"],$dataQ["son_kullanma_tarihi"],"%".$dicount,$promotionCode,$qsql["domain"]],$dataSms["icerik"]);
                                        $a = new SmsSend();
                                        $dataSend=array(
                                            'message'=>$smsicerik,
                                            'no'=>[$phone],
                                            'header'=>$qsql["smsorg"],
                                            'filter'=>0,
                                            'encoding'=>'tr',
                                            'startdate'=>'',
                                            'stopdate'=>'',
                                            'bayikodu'=>'',
                                            'appkey'=>''
                                        );
                                        $sms= new SmsSend;
                                        $sms->smsGonder($dataSend);
                                    }
                                    $data["success"]="";
                                    $data["discount"]=$dicount;
                                    $data["lastDate"]=$dataQ["son_kullanma_tarihi"];
                                    $data["promotionCode"]=$promotionCode;
                                    $data["nextList"]=$qsql["domain"];

                                }
                            }
                        }

                    }
                }
            }

        }
    }

    echo json_encode($data);
?>